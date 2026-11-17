<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use SineMacula\CodingStandardsLaravel\PHPStan\Concerns\DetectsLaravelVersion;
use SineMacula\CodingStandardsLaravel\PHPStan\Rules\PreferQueueAttributesRule;

/**
 * Tests for the queue attribute preference rule.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @extends \PHPStan\Testing\RuleTestCase<\SineMacula\CodingStandardsLaravel\PHPStan\Rules\PreferQueueAttributesRule>
 *
 * @internal
 */
#[CoversClass(PreferQueueAttributesRule::class)]
#[CoversTrait(DetectsLaravelVersion::class)]
final class PreferQueueAttributesRuleTest extends RuleTestCase
{
    /** @var string The replacement message. */
    private const string ERROR = 'Use the #[%s] attribute instead of the $%s property.';

    /** @var string The lagging floor message. */
    private const string LAGGING = '#[%s] is available in the Laravel %s this project resolves to, but its declared '
        . 'floor still allows older versions; raise the floor to %s to replace the $%s property.';

    /** @var string The fixture carrying every queued kind. */
    private const string FIXTURE = __DIR__ . '/data/queue-attributes/queued.inc';

    /** @var string Explicit Laravel floor for the rule under test. */
    private string $minLaravelVersion = '';

    /**
     * Holds each kind to the settings a 13.4 floor reads from attributes.
     *
     * @return void
     */
    public function testHoldsEachKindToTheSettingsItsFloorSupports(): void
    {
        $this->minLaravelVersion = '13.4';

        $this->analyse([self::FIXTURE], [
            [sprintf(self::ERROR, 'Tries', 'tries'), 20],
            [sprintf(self::ERROR, 'Timeout', 'timeout'), 22],
            [sprintf(self::ERROR, 'Backoff', 'backoff'), 24],
            [sprintf(self::ERROR, 'MaxExceptions', 'maxExceptions'), 26],
            [sprintf(self::ERROR, 'FailOnTimeout', 'failOnTimeout'), 28],
            [sprintf(self::ERROR, 'DeleteWhenMissingModels', 'deleteWhenMissingModels'), 30],
            [sprintf(self::ERROR, 'UniqueFor', 'uniqueFor'), 32],
            [sprintf(self::ERROR, 'Queue', 'queue'), 34],
            [sprintf(self::ERROR, 'Connection', 'connection'), 36],
            [sprintf(self::ERROR, 'Tries', 'tries'), 96],
            [sprintf(self::ERROR, 'Tries', 'tries'), 101],
            [sprintf(self::ERROR, 'Timeout', 'timeout'), 110],
            [sprintf(self::ERROR, 'Tries', 'tries'), 119],
        ]);
    }

    /**
     * Enforces every later setting once the floor reaches it.
     *
     * @return void
     */
    public function testEnforcesTheLaterSettingsOnceTheFloorReachesThem(): void
    {
        $this->minLaravelVersion = '13.36';

        $this->analyse([self::FIXTURE], [
            [sprintf(self::ERROR, 'Tries', 'tries'), 20],
            [sprintf(self::ERROR, 'Timeout', 'timeout'), 22],
            [sprintf(self::ERROR, 'Backoff', 'backoff'), 24],
            [sprintf(self::ERROR, 'MaxExceptions', 'maxExceptions'), 26],
            [sprintf(self::ERROR, 'FailOnTimeout', 'failOnTimeout'), 28],
            [sprintf(self::ERROR, 'DeleteWhenMissingModels', 'deleteWhenMissingModels'), 30],
            [sprintf(self::ERROR, 'UniqueFor', 'uniqueFor'), 32],
            [sprintf(self::ERROR, 'Queue', 'queue'), 34],
            [sprintf(self::ERROR, 'Connection', 'connection'), 36],
            [sprintf(self::ERROR, 'Delay', 'delay'), 38],
            [sprintf(self::ERROR, 'DebounceFor', 'debounceFor'), 40],
            [sprintf(self::ERROR, 'Tries', 'tries'), 96],
            [sprintf(self::ERROR, 'Tries', 'tries'), 101],
            [sprintf(self::ERROR, 'FailOnTimeout', 'failOnTimeout'), 103],
            [sprintf(self::ERROR, 'Timeout', 'timeout'), 110],
            [sprintf(self::ERROR, 'FailOnTimeout', 'failOnTimeout'), 112],
            [sprintf(self::ERROR, 'Tries', 'tries'), 119],
            [sprintf(self::ERROR, 'UniqueFor', 'uniqueFor'), 123],
        ]);
    }

    /**
     * A hooked property computes its setting, so it is left alone.
     *
     * @return void
     */
    public function testLeavesAHookedPropertyAlone(): void
    {
        $this->minLaravelVersion = '13.4';

        $this->analyse([__DIR__ . '/data/queue-attributes-hooked/queued.inc'], [
            [sprintf(self::ERROR, 'Timeout', 'timeout'), 15],
        ]);
    }

    /**
     * Enforces nothing below 13.4, where a runtime value lost to the attribute.
     *
     * @return void
     */
    public function testEnforcesNothingBelowLaravel134(): void
    {
        $this->analyse([self::FIXTURE], []);
        $this->analyse([__DIR__ . '/data/version/unsupported/queued.inc'], []);
        $this->analyse([__DIR__ . '/data/version/supported/app/queued.inc'], []);
    }

    /**
     * Reads the floor from the queue-side components as well.
     *
     * @return void
     */
    public function testDetectsTheFloorFromTheQueueSideComponents(): void
    {
        $this->analyse([__DIR__ . '/data/version/queue-package/queued.inc'], [
            [sprintf(self::ERROR, 'Tries', 'tries'), 10],
            [sprintf(self::ERROR, 'Delay', 'delay'), 12],
            [sprintf(self::ERROR, 'Timeout', 'timeout'), 17],
        ]);
    }

    /**
     * Takes the lowest floor the declared queue-side components share.
     *
     * @return void
     */
    public function testTakesTheLowestFloorTheComponentsDeclare(): void
    {
        $this->analyse([__DIR__ . '/data/version/queue-mixed/queued.inc'], [
            [sprintf(self::ERROR, 'Tries', 'tries'), 10],
            [sprintf(self::ERROR, 'Timeout', 'timeout'), 17],
        ]);
    }

    /**
     * Reports a lagging floor naming the floor each setting needs.
     *
     * @return void
     */
    public function testReportsALaggingFloorAtTheSettingsOwnFloor(): void
    {
        $locked = '13.30.0';

        $this->analyse([__DIR__ . '/data/version/queue-drift/queued.inc'], [
            [sprintf(self::LAGGING, 'Tries', $locked, '13.4', 'tries'), 10],
            [sprintf(self::LAGGING, 'Delay', $locked, '13.24', 'delay'), 12],
            [sprintf(self::LAGGING, 'Timeout', $locked, '13.4', 'timeout'), 17],
        ]);

        $resolved = '13.23.0';

        $this->analyse([__DIR__ . '/data/version/drift-illuminate/queued.inc'], [
            [sprintf(self::LAGGING, 'Tries', $resolved, '13.4', 'tries'), 10],
            [sprintf(self::LAGGING, 'Timeout', $resolved, '13.4', 'timeout'), 17],
        ]);
    }

    /**
     * Stays silent when both the floor and the lock predate the attributes.
     *
     * @return void
     */
    public function testStaysSilentWhenTheLockAlsoPredatesTheAttributes(): void
    {
        $this->analyse([__DIR__ . '/data/version/drift-old/queued.inc'], []);
    }

    /**
     * Provide the rule under test.
     *
     * @return \PHPStan\Rules\Rule<\PHPStan\Node\InClassNode>
     */
    #[\Override]
    protected function getRule(): Rule
    {
        return new PreferQueueAttributesRule($this->minLaravelVersion);
    }
}
