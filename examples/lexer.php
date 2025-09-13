<?php

declare(strict_types=1);

use Cel\Input\Input;
use Cel\Lexer\Lexer;
use Cel\Token\Token;
use Psl\IO;
use Psl\Str;
use Psl\Str\Byte;
use Psl\Vec;

require_once __DIR__ . '/../vendor/autoload.php';

$source = <<<CEL
// Simple expression
(request.size - 10) > 0 && 'admin' in request.auth.claims
CEL;

IO\write_error_line("Tokenizing source:\n---\n%s\n---", $source);

$input = new Input($source);
$lexer = new Lexer($input);

/** @var list<Token> $tokens */
$tokens = [];
while (true) {
    $token = $lexer->advance();
    if (null === $token) {
        break;
    }

    $tokens[] = $token;
}

// Print the token stream
foreach ($tokens as $token) {
    // For readability, we replace whitespace characters with their names
    $value = $token->value;
    if ($token->kind->isWhitespace() || $token->kind->isComment()) {
        $value = Byte\replace_every($value, ["\n" => "\\n", "\r" => "\\r", "\t" => "\\t", ' ' => '·']);
    }

    IO\write_line("[%-16s][%-3d...%-3d]: '%s'", $token->kind->name, $token->span->start, $token->span->end, $value);
}

// Verify that the tokenization was lossless
$reconstructed = Str\join(Vec\map($tokens, fn(Token $t): string => $t->value), '');

if ($reconstructed === $source) {
    IO\write_line("\nTokenization successful: resulting string matches original input.");
} else {
    IO\write_line("\nTokenization failed: resulting string does not match original input.");
}
