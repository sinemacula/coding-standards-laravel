<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Controllers;

use PHPUnit\Framework\Attributes\CoversClass;
use SineMaculaLaravel\Sniffs\Controllers\DisallowInlineValidationSniff;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the inline controller validation sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversClass(DisallowInlineValidationSniff::class)]
final class DisallowInlineValidationSniffTest extends AbstractSniffTestCase
{
    /**
     * Flags validate() and Validator::make() in controllers only.
     *
     * @return void
     */
    public function testFlagsInlineValidationInControllers(): void
    {
        $this->assertErrorsOnLines('DisallowInlineValidation.inc', [11, 12, 13]);
    }

    /**
     * Flags only make() among static calls on the Validator facade.
     *
     * @return void
     */
    public function testFlagsOnlyMakeOnTheValidatorFacade(): void
    {
        $this->assertErrorsOnLines('DisallowInlineValidationStatics.inc', [12]);
    }
}
