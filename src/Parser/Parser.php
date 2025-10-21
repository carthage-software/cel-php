<?php

declare(strict_types=1);

namespace Cel\Parser;

use Cel\Exception\InternalException;
use Cel\Lexer\LexerInterface;
use Cel\Parser\Exception\UnexpectedEndOfFileException;
use Cel\Parser\Exception\UnexpectedTokenException;
use Cel\Span\Span;
use Cel\Syntax\Aggregate\FieldInitializerNode;
use Cel\Syntax\Aggregate\ListExpression;
use Cel\Syntax\Aggregate\MapEntryNode;
use Cel\Syntax\Aggregate\MapExpression;
use Cel\Syntax\Aggregate\MessageExpression;
use Cel\Syntax\Binary\BinaryExpression;
use Cel\Syntax\Binary\BinaryOperator;
use Cel\Syntax\Binary\BinaryOperatorKind;
use Cel\Syntax\ConditionalExpression;
use Cel\Syntax\Expression;
use Cel\Syntax\IdentifierNode;
use Cel\Syntax\Literal\BoolLiteralExpression;
use Cel\Syntax\Literal\BytesLiteralExpression;
use Cel\Syntax\Literal\FloatLiteralExpression;
use Cel\Syntax\Literal\IntegerLiteralExpression;
use Cel\Syntax\Literal\NullLiteralExpression;
use Cel\Syntax\Literal\StringLiteralExpression;
use Cel\Syntax\Literal\UnsignedIntegerLiteralExpression;
use Cel\Syntax\Member\CallExpression;
use Cel\Syntax\Member\IdentifierExpression;
use Cel\Syntax\Member\IndexExpression;
use Cel\Syntax\Member\MemberAccessExpression;
use Cel\Syntax\Node;
use Cel\Syntax\ParenthesizedExpression;
use Cel\Syntax\PunctuatedSequence;
use Cel\Syntax\SelectorNode;
use Cel\Syntax\Unary\UnaryExpression;
use Cel\Syntax\Unary\UnaryOperator;
use Cel\Syntax\Unary\UnaryOperatorKind;
use Cel\Token\Token;
use Cel\Token\TokenKind;
use Closure;
use Error;
use Override;
use Psl;
use Psl\Exception\ExceptionInterface;
use Psl\Str;
use Psl\Str\Byte;

final class Parser implements ParserInterface
{
    use ParserConvenienceMethodsTrait;

    private TokenStream $stream;

    /**
     * @inheritDoc
     */
    #[Override]
    public static function default(): static
    {
        return new self();
    }

    /**
     * @param LexerInterface $lexer
     *
     * @throws UnexpectedEndOfFileException If the end of the file is reached unexpectedly.
     * @throws UnexpectedTokenException If an unexpected token is encountered.
     * @throws InternalException If internal parsing operations fail.
     */
    #[Override]
    public function construct(LexerInterface $lexer): Expression
    {
        $this->stream = new TokenStream($lexer);

        $expression = $this->parseExpression();

        if (!$this->stream->hasReachedEnd()) {
            throw new UnexpectedTokenException($this->stream->peek());
        }

        return $expression;
    }

    /**
     * @throws UnexpectedEndOfFileException
     * @throws UnexpectedTokenException
     * @throws InternalException If internal parsing operations fail.
     */
    private function parseExpression(): Expression
    {
        $expr = $this->parseConditionalOr();

        if (!$this->stream->hasReachedEnd() && $this->stream->isAt(TokenKind::Question)) {
            $question = $this->stream->eat(TokenKind::Question);
            $then = $this->parseConditionalOr();
            $colon = $this->stream->eat(TokenKind::Colon);
            $else = $this->parseExpression(); // Right-associative

            return new ConditionalExpression($expr, $question->span, $then, $colon->span, $else);
        }

        return $expr;
    }

    /**
     * @throws UnexpectedEndOfFileException
     * @throws UnexpectedTokenException
     * @throws InternalException If internal parsing operations fail.
     */
    private function parseConditionalOr(): Expression
    {
        $left = $this->parseConditionalAnd();

        while (!$this->stream->hasReachedEnd() && $this->stream->isAt(TokenKind::DoublePipe)) {
            $opToken = $this->stream->eat(TokenKind::DoublePipe);
            $operator = new BinaryOperator(BinaryOperatorKind::Or, $opToken->span);
            $right = $this->parseConditionalAnd();
            $left = new BinaryExpression($left, $operator, $right);
        }

        return $left;
    }

    /**
     * @throws UnexpectedEndOfFileException
     * @throws UnexpectedTokenException
     * @throws InternalException If internal parsing operations fail.
     */
    private function parseConditionalAnd(): Expression
    {
        $left = $this->parseRelation();

        while (!$this->stream->hasReachedEnd() && $this->stream->isAt(TokenKind::DoubleAmpersand)) {
            $opToken = $this->stream->eat(TokenKind::DoubleAmpersand);
            $operator = new BinaryOperator(BinaryOperatorKind::And, $opToken->span);
            $right = $this->parseRelation();
            $left = new BinaryExpression($left, $operator, $right);
        }

        return $left;
    }

    /**
     * @throws UnexpectedEndOfFileException
     * @throws UnexpectedTokenException
     * @throws InternalException If internal parsing operations fail.
     */
    private function parseRelation(): Expression
    {
        $left = $this->parseAddition();

        while (!$this->stream->hasReachedEnd() && $this->isAtRelationOperator()) {
            $opToken = $this->stream->consume();
            $opKind = $this->tokenToBinaryOperatorKind($opToken->kind);
            $operator = new BinaryOperator($opKind, $opToken->span);
            $right = $this->parseAddition();
            $left = new BinaryExpression($left, $operator, $right);
        }

        return $left;
    }

    /**
     * @throws UnexpectedEndOfFileException
     * @throws UnexpectedTokenException
     * @throws InternalException If internal parsing operations fail.
     */
    private function parseAddition(): Expression
    {
        $left = $this->parseMultiplication();

        while (
            !$this->stream->hasReachedEnd()
            && ($this->stream->isAt(TokenKind::Plus) || $this->stream->isAt(TokenKind::Minus))
        ) {
            $opToken = $this->stream->consume();
            $opKind = $this->tokenToBinaryOperatorKind($opToken->kind);
            $operator = new BinaryOperator($opKind, $opToken->span);
            $right = $this->parseMultiplication();
            $left = new BinaryExpression($left, $operator, $right);
        }

        return $left;
    }

    /**
     * @throws UnexpectedEndOfFileException
     * @throws UnexpectedTokenException
     * @throws InternalException If internal parsing operations fail.
     */
    private function parseMultiplication(): Expression
    {
        $left = $this->parseUnary();

        while (
            !$this->stream->hasReachedEnd()
            && (
                $this->stream->isAt(TokenKind::Asterisk)
                || $this->stream->isAt(TokenKind::Slash)
                || $this->stream->isAt(TokenKind::Percent)
            )
        ) {
            $opToken = $this->stream->consume();
            $opKind = $this->tokenToBinaryOperatorKind($opToken->kind);
            $operator = new BinaryOperator($opKind, $opToken->span);
            $right = $this->parseUnary();
            $left = new BinaryExpression($left, $operator, $right);
        }

        return $left;
    }

    /**
     * @throws UnexpectedEndOfFileException
     * @throws UnexpectedTokenException
     * @throws InternalException If internal parsing operations fail.
     */
    private function parseUnary(): Expression
    {
        if ($this->stream->isAt(TokenKind::Bang) || $this->stream->isAt(TokenKind::Minus)) {
            $opToken = $this->stream->consume();
            $opKind = match ($opToken->kind) {
                TokenKind::Bang => UnaryOperatorKind::Not,
                TokenKind::Minus => UnaryOperatorKind::Negate,
                default => throw new Error("Not a unary operator token: `{$opToken->kind->name}`"),
            };

            $operator = new UnaryOperator($opKind, $opToken->span);
            $operand = $this->parseUnary();

            return new UnaryExpression($operator, $operand);
        }

        return $this->parseMember();
    }

    /**
     * @throws UnexpectedEndOfFileException
     * @throws UnexpectedTokenException
     * @throws InternalException If internal parsing operations fail.
     */
    private function parseMember(): Expression
    {
        $expr = $this->parsePrimary();

        while (!$this->stream->hasReachedEnd()) {
            if ($this->stream->isAt(TokenKind::Dot)) {
                $dot = $this->stream->eat(TokenKind::Dot);
                $field = $this->stream->eat(TokenKind::Identifier);
                $selector = new SelectorNode($field->value, $field->span);

                if (!$this->stream->hasReachedEnd() && $this->stream->isAt(TokenKind::LeftParenthesis)) {
                    $openParen = $this->stream->eat(TokenKind::LeftParenthesis);
                    $args = $this->parsePunctuatedSequence(TokenKind::RightParenthesis, $this->parseExpression(...));
                    $closeParen = $this->stream->eat(TokenKind::RightParenthesis);

                    $expr = new CallExpression(
                        $expr,
                        $dot->span,
                        $selector,
                        $openParen->span,
                        $args,
                        $closeParen->span,
                    );
                } else {
                    $expr = new MemberAccessExpression($expr, $dot->span, $selector);
                }
            } elseif ($this->stream->isAt(TokenKind::LeftBracket)) {
                $openBracket = $this->stream->eat(TokenKind::LeftBracket);
                $index = $this->parseExpression();
                $closeBracket = $this->stream->eat(TokenKind::RightBracket);

                $expr = new IndexExpression($expr, $openBracket->span, $index, $closeBracket->span);
            } else {
                break;
            }
        }

        return $expr;
    }

    /**
     * @throws UnexpectedEndOfFileException
     * @throws UnexpectedTokenException
     * @throws InternalException If internal parsing operations fail.
     */
    private function parsePrimary(): Expression
    {
        if ($this->stream->hasReachedEnd()) {
            throw new UnexpectedEndOfFileException($this->stream->cursorPosition());
        }

        $token = $this->stream->peek();

        if ($token->kind === TokenKind::LeftParenthesis) {
            $left = $this->stream->eat(TokenKind::LeftParenthesis);
            $expr = $this->parseExpression();
            $right = $this->stream->eat(TokenKind::RightParenthesis);
            return new ParenthesizedExpression($left->span, $expr, $right->span);
        }

        if ($token->kind === TokenKind::LeftBracket) {
            return $this->parseListLiteral();
        }

        if ($token->kind === TokenKind::LeftBrace) {
            return $this->parseMapLiteral();
        }

        if ($token->kind->isLiteral()) {
            return $this->parseLiteral();
        }

        $leadingDot = null;
        if ($token->kind === TokenKind::Dot) {
            $leadingDot = $this->stream->eat(TokenKind::Dot);
            $token = $this->stream->peek();
        }

        if ($token->kind === TokenKind::Identifier) {
            if ($this->isAtMessageLiteral()) {
                return $this->parseMessageLiteral($leadingDot?->span);
            }

            $identToken = $this->stream->eat(TokenKind::Identifier);

            if (!$this->stream->hasReachedEnd() && $this->stream->isAt(TokenKind::LeftParenthesis)) {
                $selector = new SelectorNode($identToken->value, $identToken->span);
                $openParen = $this->stream->eat(TokenKind::LeftParenthesis);
                $args = $this->parsePunctuatedSequence(TokenKind::RightParenthesis, $this->parseExpression(...));
                $closeParen = $this->stream->eat(TokenKind::RightParenthesis);

                return new CallExpression(
                    null,
                    $leadingDot?->span,
                    $selector,
                    $openParen->span,
                    $args,
                    $closeParen->span,
                );
            }

            if (null !== $leadingDot) {
                // A leading dot must be followed by a message literal or a function call.
                // If it\'s just an identifier, it\'s a syntax error.
                throw new UnexpectedTokenException($identToken);
            }

            return new IdentifierExpression(new IdentifierNode($identToken->value, $identToken->span));
        }

        if (null !== $leadingDot) {
            throw new UnexpectedTokenException($this->stream->peek());
        }

        throw new UnexpectedTokenException($token);
    }

    /**
     * @throws UnexpectedEndOfFileException
     * @throws UnexpectedTokenException
     */
    private function parseListLiteral(): ListExpression
    {
        $open = $this->stream->eat(TokenKind::LeftBracket);
        $elements = $this->parsePunctuatedSequence(TokenKind::RightBracket, $this->parseExpression(...));
        $close = $this->stream->eat(TokenKind::RightBracket);

        return new ListExpression($open->span, $elements, $close->span);
    }

    /**
     * @throws UnexpectedEndOfFileException
     * @throws UnexpectedTokenException
     */
    private function parseMapLiteral(): MapExpression
    {
        $open = $this->stream->eat(TokenKind::LeftBrace);
        $entries = $this->parsePunctuatedSequence(TokenKind::RightBrace, $this->parseMapEntry(...));
        $close = $this->stream->eat(TokenKind::RightBrace);

        return new MapExpression($open->span, $entries, $close->span);
    }

    /**
     * @throws UnexpectedEndOfFileException
     * @throws UnexpectedTokenException
     * @throws InternalException If internal parsing operations fail.
     */
    private function parseMapEntry(): MapEntryNode
    {
        $key = $this->parseExpression();
        $colon = $this->stream->eat(TokenKind::Colon);
        $value = $this->parseExpression();

        return new MapEntryNode($key, $colon->span, $value);
    }

    /**
     * @throws UnexpectedEndOfFileException
     * @throws UnexpectedTokenException
     */
    private function parseMessageLiteral(null|Span $leadingDot): MessageExpression
    {
        $firstIdent = $this->stream->eat(TokenKind::Identifier);
        $selector = new SelectorNode($firstIdent->value, $firstIdent->span);

        $following = [];
        $dots = [];
        while (!$this->stream->hasReachedEnd() && $this->stream->isAt(TokenKind::Dot)) {
            if ($this->stream->lookahead(1)?->kind !== TokenKind::Identifier) {
                break;
            }

            $dots[] = $this->stream->eat(TokenKind::Dot)->span;
            $ident = $this->stream->eat(TokenKind::Identifier);
            $following[] = new SelectorNode($ident->value, $ident->span);
        }
        $followingSelectors = new PunctuatedSequence($following, $dots);

        $openBrace = $this->stream->eat(TokenKind::LeftBrace);
        $initializers = $this->parsePunctuatedSequence(TokenKind::RightBrace, $this->parseFieldInitializer(...));
        $closeBrace = $this->stream->eat(TokenKind::RightBrace);

        return new MessageExpression(
            $leadingDot,
            $selector,
            $followingSelectors,
            $openBrace->span,
            $initializers,
            $closeBrace->span,
        );
    }

    /**
     * @throws UnexpectedEndOfFileException
     * @throws UnexpectedTokenException
     * @throws InternalException If internal parsing operations fail.
     */
    private function parseFieldInitializer(): FieldInitializerNode
    {
        $fieldToken = $this->stream->eat(TokenKind::Identifier);
        $field = new SelectorNode($fieldToken->value, $fieldToken->span);
        $colon = $this->stream->eat(TokenKind::Colon);
        $value = $this->parseExpression();

        return new FieldInitializerNode($field, $colon->span, $value);
    }

    /**
     * @throws UnexpectedEndOfFileException
     * @throws UnexpectedTokenException
     * @throws InternalException If internal parsing operations fail.
     */
    private function parseLiteral(): Expression
    {
        $token = $this->stream->consume();
        return match ($token->kind) {
            TokenKind::LiteralInt => new IntegerLiteralExpression((int) $token->value, $token->value, $token->span),
            TokenKind::LiteralUInt => new UnsignedIntegerLiteralExpression(
                (int) Byte\trim_right($token->value, 'uU'),
                $token->value,
                $token->span,
            ),
            TokenKind::LiteralFloat => new FloatLiteralExpression((float) $token->value, $token->value, $token->span), // @mago-expect analysis:invalid-type-cast
            TokenKind::LiteralString => $this->parseStringLiteral($token),
            TokenKind::BytesSequence => $this->parseBytesLiteral($token),
            TokenKind::True => new BoolLiteralExpression(true, $token->value, $token->span),
            TokenKind::False => new BoolLiteralExpression(false, $token->value, $token->span),
            TokenKind::Null => new NullLiteralExpression($token->value, $token->span),
            default => throw new UnexpectedTokenException($token),
        };
    }

    /**
     * Parses a string literal, handling escape sequences.
     *
     * @throws InternalException If string unescaping fails.
     */
    private function parseStringLiteral(Token $token): StringLiteralExpression
    {
        try {
            // Check if it's a raw string (prefixed with r or R)
            $value = $token->value;
            $isRaw = Byte\lowercase(Byte\slice($value, 0, 1)) === 'r';

            // Determine quote style and extract content
            $quoteStart = $isRaw ? 1 : 0;
            $quote = Byte\slice($value, $quoteStart, 1);
            $isTriple = Byte\slice($value, $quoteStart, 3) === Psl\Str\repeat($quote, 3);
            $quoteLength = $isTriple ? 3 : 1;

            // Extract the content between quotes
            $start = $quoteStart + $quoteLength;
            $end = Byte\length($value) - $quoteLength;
            $length = $end - $start;
            $content = Byte\slice($value, $start, $length >= 0 ? $length : 0);

            // Unescape unless it's a raw string
            if (!$isRaw) {
                $content = StringUnescaper::unescapeString($content);
            }

            return new StringLiteralExpression($content, $token->value, $token->span);
        } catch (ExceptionInterface $e) {
            throw InternalException::forMessage('String literal parsing failed: ' . $e->getMessage(), $e);
        }
    }

    /**
     * Parses a bytes literal, handling escape sequences.
     *
     * @throws InternalException If bytes unescaping fails.
     */
    private function parseBytesLiteral(Token $token): BytesLiteralExpression
    {
        try {
            // Bytes can be: b"...", b'...', br"...", rb"...", etc.
            $value = $token->value;
            $lowerValue = Byte\lowercase($value);

            // Determine if raw by checking the prefix (first 2 chars)
            // Can be: br"..." or rb"..." (or with ')
            $prefix = Byte\slice($lowerValue, 0, 2);
            $isRaw = $prefix === 'br' || $prefix === 'rb';

            // Find where the quote starts (after b, r, br, or rb prefix)
            $prefixEnd = $isRaw ? 2 : 1;

            // Extract quote and content
            $quote = Byte\slice($value, $prefixEnd, 1);
            $isTriple = Byte\slice($value, $prefixEnd, 3) === Psl\Str\repeat($quote, 3);
            $quoteLength = $isTriple ? 3 : 1;

            // Extract content between quotes
            $start = $prefixEnd + $quoteLength;
            $end = Byte\length($value) - $quoteLength;
            $length = $end - $start;
            $content = Byte\slice($value, $start, $length >= 0 ? $length : 0);

            // Unescape unless it's a raw bytes literal
            if (!$isRaw) {
                $content = StringUnescaper::unescapeBytes($content);
            }

            return new BytesLiteralExpression($content, $token->value, $token->span);
        } catch (ExceptionInterface $e) {
            throw InternalException::forMessage('Bytes literal parsing failed: ' . $e->getMessage(), $e);
        }
    }

    /**
     * @template T of Node
     *
     * @param (Closure(): T) $parse
     *
     * @return PunctuatedSequence<T>
     *
     * @throws UnexpectedEndOfFileException
     * @throws UnexpectedTokenException
     */
    private function parsePunctuatedSequence(TokenKind $end, Closure $parse): PunctuatedSequence
    {
        $elements = [];
        $commas = [];

        if ($this->stream->isAt($end)) {
            /** @var PunctuatedSequence<T> */
            return new PunctuatedSequence([], []);
        }

        while (true) {
            $elements[] = $parse();

            if ($this->stream->hasReachedEnd() || $this->stream->isAt($end)) {
                break;
            }

            $commas[] = $this->stream->eat(TokenKind::Comma)->span;

            if ($this->stream->hasReachedEnd() || $this->stream->isAt($end)) {
                break;
            }
        }

        return new PunctuatedSequence($elements, $commas);
    }

    /**
     * @throws UnexpectedEndOfFileException If the end of the file is reached unexpectedly.
     */
    private function isAtRelationOperator(): bool
    {
        if ($this->stream->hasReachedEnd()) {
            return false;
        }

        $kind = $this->stream->peek()->kind;
        return (
            $kind === TokenKind::Equal
            || $kind === TokenKind::NotEqual
            || $kind === TokenKind::Less
            || $kind === TokenKind::LessOrEqual
            || $kind === TokenKind::Greater
            || $kind === TokenKind::GreaterOrEqual
            || $kind === TokenKind::In
        );
    }

    private function tokenToBinaryOperatorKind(TokenKind $kind): BinaryOperatorKind
    {
        return match ($kind) {
            TokenKind::DoublePipe => BinaryOperatorKind::Or,
            TokenKind::DoubleAmpersand => BinaryOperatorKind::And,
            TokenKind::Equal => BinaryOperatorKind::Equal,
            TokenKind::NotEqual => BinaryOperatorKind::NotEqual,
            TokenKind::Less => BinaryOperatorKind::LessThan,
            TokenKind::LessOrEqual => BinaryOperatorKind::LessThanOrEqual,
            TokenKind::Greater => BinaryOperatorKind::GreaterThan,
            TokenKind::GreaterOrEqual => BinaryOperatorKind::GreaterThanOrEqual,
            TokenKind::In => BinaryOperatorKind::In,
            TokenKind::Plus => BinaryOperatorKind::Plus,
            TokenKind::Minus => BinaryOperatorKind::Minus,
            TokenKind::Asterisk => BinaryOperatorKind::Multiply,
            TokenKind::Slash => BinaryOperatorKind::Divide,
            TokenKind::Percent => BinaryOperatorKind::Modulo,
            default => throw new Error("Not a binary operator token: {$kind->name}"),
        };
    }

    private function isAtMessageLiteral(): bool
    {
        $i = 0;
        if ($this->stream->lookahead($i)?->kind === TokenKind::Dot) {
            $i++;
        }

        if ($this->stream->lookahead($i)?->kind !== TokenKind::Identifier) {
            return false;
        }
        $i++;

        while ($this->stream->lookahead($i)?->kind === TokenKind::Dot) {
            $i++;
            if ($this->stream->lookahead($i)?->kind !== TokenKind::Identifier) {
                return false;
            }
            $i++;
        }

        return $this->stream->lookahead($i)?->kind === TokenKind::LeftBrace;
    }
}
