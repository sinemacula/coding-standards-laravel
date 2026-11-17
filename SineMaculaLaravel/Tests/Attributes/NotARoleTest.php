<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Attributes;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SineMacula\CodingStandardsLaravel\Attributes\NotARole;

/**
 * Tests for the NotARole attribute.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversClass(NotARole::class)]
final class NotARoleTest extends TestCase
{
    /**
     * The attribute targets classes only.
     *
     * @return void
     */
    public function testTargetsClassesOnly(): void
    {
        $attributes = (new \ReflectionClass(NotARole::class))->getAttributes(\Attribute::class);

        self::assertCount(1, $attributes);
        self::assertSame(\Attribute::TARGET_CLASS, $attributes[0]->newInstance()->flags);
    }
}
