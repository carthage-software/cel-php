<?php

declare(strict_types=1);

use Cel\Input\Input;
use Cel\Lexer\Lexer;
use Cel\Token\Token;

require_once __DIR__ . '/../vendor/autoload.php';

$source = <<<CEL
// Simple expression
(request.size - 10) > 0 && 'admin' in request.auth.claims
CEL;

printf("Tokenizing source:\n---\n%s\n---\n", $source);

$input = new Input($source);
$lexer = new Lexer($input);

/** @var list<Token> $tokens */
$tokens = [];
while ($token = $lexer->advance()) {
    $tokens[] = $token;
}

// Print the token stream
foreach ($tokens as $token) {
    // For readability, we replace whitespace characters with their names
    $value = $token->value;
    if ($token->kind->isWhitespace() || $token->kind->isComment()) {
        $value = str_replace(["\n", "\r", "\t", " "], ["\\n", "\\r", "\\t", "·"], $value);
    }

    printf(
        "[%-16s][%-3d...%-3d]: '%s'\n",
        $token->kind->name,
        $token->span->start,
        $token->span->end,
        $value,
    );
}

// Verify that the tokenization was lossless
$reconstructed = implode('', array_map(fn (Token $t) => $t->value, $tokens));

if ($reconstructed === $source) {
    echo "\nTokenization successful: resulting string matches original input.\n";
} else {
    echo "\nTokenization failed: resulting string does not match original input.\n";
}
