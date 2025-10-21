<?php

declare(strict_types=1);

namespace Cel\Tests;

use PHPUnit\Framework\TestCase;
use RuntimeException;

use function file_get_contents;
use function scandir;

/**
 * @require-extends TestCase
 */
trait ResourceProviderTrait
{
    /**
     * @return iterable<string, list{string}>
     *
     * @mago-expect analysis:possibly-false-iterator
     */
    public static function provideCelResources(): iterable
    {
        $resources = __DIR__ . '/__resources__/';
        foreach (scandir($resources) as $file) {
            if ('.' === $file || '..' === $file) {
                continue;
            }

            $content = file_get_contents($resources . $file);
            if (false === $content) {
                throw new RuntimeException("Failed to read file: {$file}");
            }

            yield $file => [$content];
        }
    }
}
