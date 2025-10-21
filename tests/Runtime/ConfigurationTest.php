<?php

declare(strict_types=1);

namespace Cel\Tests\Runtime;

use Cel\Exception\MisconfigurationException;
use Cel\Extension\ExtensionInterface;
use Cel\Message\MessageInterface;
use Cel\Runtime\Configuration;
use Cel\Value\Value;
use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{
    public function testDefaultConstructor(): void
    {
        $config = new Configuration();

        static::assertTrue($config->enableMacros);
        static::assertSame([], $config->allowedMessageClasses);
        static::assertSame([], $config->messageClassAliases);
        static::assertFalse($config->enforceMessageClassAliases);

        // Should have standard extensions loaded
        $extensions = $config->getExtensions();
        static::assertGreaterThan(0, $extensions);
    }

    public function testDefaultStaticConstructor(): void
    {
        $config = Configuration::default();

        static::assertInstanceOf(Configuration::class, $config);
        static::assertTrue($config->enableMacros);
    }

    public function testWithMacrosDisabled(): void
    {
        $config = new Configuration(enableMacros: false);

        static::assertFalse($config->enableMacros);
    }

    public function testWithAllowedMessageClasses(): void
    {
        $messageClass = self::createMockMessageClass();

        $config = new Configuration(allowedMessageClasses: [$messageClass]);

        static::assertSame([$messageClass], $config->allowedMessageClasses);
    }

    public function testWithMessageClassAliases(): void
    {
        $messageClass = self::createMockMessageClass();

        $config = new Configuration(
            allowedMessageClasses: [$messageClass],
            messageClassAliases: ['MyMessage' => $messageClass],
        );

        static::assertSame(['MyMessage' => $messageClass], $config->messageClassAliases);
        static::assertSame([$messageClass => ['MyMessage']], $config->messageClassesToAliases);
    }

    public function testWithMultipleAliasesForSameClass(): void
    {
        $messageClass = self::createMockMessageClass();

        $config = new Configuration(
            allowedMessageClasses: [$messageClass],
            messageClassAliases: [
                'MyMessage' => $messageClass,
                'Msg' => $messageClass,
            ],
        );

        static::assertContains('MyMessage', $config->messageClassesToAliases[$messageClass]);
        static::assertContains('Msg', $config->messageClassesToAliases[$messageClass]);
        static::assertCount(2, $config->messageClassesToAliases[$messageClass]);
    }

    public function testWithEnforceMessageClassAliases(): void
    {
        $messageClass = self::createMockMessageClass();

        $config = new Configuration(
            allowedMessageClasses: [$messageClass],
            messageClassAliases: ['MyMessage' => $messageClass],
            enforceMessageClassAliases: true,
        );

        static::assertTrue($config->enforceMessageClassAliases);
    }

    public function testWithoutStandardExtensions(): void
    {
        $config = new Configuration(enableStandardExtensions: false);

        static::assertSame([], $config->getExtensions());
    }

    public function testThrowsExceptionWhenAliasNotInAllowedClasses(): void
    {
        $messageClass = self::createMockMessageClass();

        $this->expectException(MisconfigurationException::class);
        $this->expectExceptionMessage('does not map to an allowed message class');

        new Configuration(
            allowedMessageClasses: [],
            messageClassAliases: ['MyMessage' => $messageClass],
        );
    }

    public function testAddExtension(): void
    {
        $config = new Configuration(enableStandardExtensions: false);

        $extension = $this->createMock(ExtensionInterface::class);

        $config->addExtension($extension);

        static::assertSame([$extension], $config->getExtensions());
    }

    public function testGetExtensions(): void
    {
        $config = new Configuration(enableStandardExtensions: false);

        $extension1 = $this->createMock(ExtensionInterface::class);
        $extension2 = $this->createMock(ExtensionInterface::class);

        $config->addExtension($extension1);
        $config->addExtension($extension2);

        $extensions = $config->getExtensions();

        static::assertCount(2, $extensions);
        static::assertSame([$extension1, $extension2], $extensions);
    }

    public function testGetValueResolvers(): void
    {
        $config = new Configuration(enableStandardExtensions: false);

        $resolver1 = $this->createMock(\Cel\Value\Resolver\ValueResolverInterface::class);
        $resolver2 = $this->createMock(\Cel\Value\Resolver\ValueResolverInterface::class);

        $extension1 = $this->createMock(ExtensionInterface::class);
        $extension1->method('getValueResolvers')->willReturn([$resolver1]);

        $extension2 = $this->createMock(ExtensionInterface::class);
        $extension2->method('getValueResolvers')->willReturn([$resolver2]);

        $config->addExtension($extension1);
        $config->addExtension($extension2);

        $resolvers = $config->getValueResolvers();

        static::assertCount(2, $resolvers);
        static::assertSame([$resolver1, $resolver2], $resolvers);
    }

    public function testGetValueResolversWithNoResolvers(): void
    {
        $config = new Configuration(enableStandardExtensions: false);

        $extension = $this->createMock(ExtensionInterface::class);
        $extension->method('getValueResolvers')->willReturn([]);

        $config->addExtension($extension);

        $resolvers = $config->getValueResolvers();

        static::assertSame([], $resolvers);
    }

    public function testForAllowedMessages(): void
    {
        $messageClass = self::createMockMessageClass();

        $config = Configuration::forAllowedMessages(allowedMessageClasses: [$messageClass]);

        static::assertSame([$messageClass], $config->allowedMessageClasses);
        static::assertTrue($config->enableMacros);
        // Should have standard extensions loaded
        static::assertGreaterThan(0, $config->getExtensions());
    }

    public function testForAllowedMessagesWithAliases(): void
    {
        $messageClass = self::createMockMessageClass();

        $config = Configuration::forAllowedMessages(
            allowedMessageClasses: [$messageClass],
            messageClassAliases: ['MyMessage' => $messageClass],
        );

        static::assertSame(['MyMessage' => $messageClass], $config->messageClassAliases);
    }

    public function testForAllowedMessagesWithEnforceAliases(): void
    {
        $messageClass = self::createMockMessageClass();

        $config = Configuration::forAllowedMessages(
            allowedMessageClasses: [$messageClass],
            messageClassAliases: ['MyMessage' => $messageClass],
            enforceMessageClassAliases: true,
        );

        static::assertTrue($config->enforceMessageClassAliases);
    }

    public function testForAllowedMessagesThrowsExceptionWhenAliasNotInAllowedClasses(): void
    {
        $messageClass = self::createMockMessageClass();

        $this->expectException(MisconfigurationException::class);

        Configuration::forAllowedMessages(
            allowedMessageClasses: [],
            messageClassAliases: ['MyMessage' => $messageClass],
        );
    }

    /**
     * @return class-string<MessageInterface>
     */
    private static function createMockMessageClass(): string
    {
        return new class implements MessageInterface {
            #[\Override]
            public function toCelValue(): Value
            {
                throw new \RuntimeException('Not implemented');
            }

            #[\Override]
            public static function fromCelFields(array $fields): static
            {
                return new self();
            }
        }::class;
    }
}
