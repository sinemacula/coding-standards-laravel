<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\DependencyInjection\NeonAdapter;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * Tests the analysis config this package ships to consuming projects.
 *
 * Loading the real file proves it parses and that the ignored-report patterns
 * match the reports they are written for - which are verbatim captures from a
 * Laravel project analysed with this standard, not paraphrases - while leaving
 * unrelated reports of the same kind untouched.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversNothing]
final class AnalysisConfigTest extends TestCase
{
    /** @var string The identifier the builder patterns are scoped to. */
    private const string DYNAMIC_CALL = 'staticMethod.dynamicCall';

    /** @var array<int, string> Reports produced by ordinary query code. */
    private const array BUILDER_REPORTS = [
        'Dynamic call to static method Illuminate\Database\Eloquent\Builder<App\Models\Credential>::whereNull().',
        'Dynamic call to static method Illuminate\Database\Eloquent\Builder<App\Models\Credential>::count().',
        'Dynamic call to static method Illuminate\Database\Eloquent\Builder<App\Models\Credential>::first().',
        'Dynamic call to static method Illuminate\Database\Eloquent\Builder<App\Models\Token>::where().',
        'Dynamic call to static method Illuminate\Database\Query\Builder::where().',
    ];

    /** @var array<int, string> Reports from a #[Scope] and a generic bound to static. */
    private const array SCOPE_REPORTS = [
        'Dynamic call to static method Illuminate\Database\Eloquent\Builder<App\Models\Credential>::active().',
        'Dynamic call to static method Illuminate\Database\Eloquent\Builder<static(App\Models\Credential)>::whereNull().',
        'Dynamic call to static method Illuminate\Database\Eloquent\Builder<Illuminate\Database\Eloquent\Model>::where().',
    ];

    /** @var array<int, string> Reports of the same kind that must still surface. */
    private const array UNRELATED_REPORTS = [
        'Dynamic call to static method App\Support\Registry::make().',
        'Dynamic call to static method Illuminate\Support\Facades\DB::table().',
        'Dynamic call to static method App\Builders\CredentialBuilder::whereNull().',
    ];

    /**
     * Ordinary query code - the query methods and the terminal calls - is
     * ignored on both the Eloquent and the query builder.
     *
     * @return void
     */
    public function testIgnoresTheReportsOrdinaryQueryCodeProduces(): void
    {
        foreach (self::BUILDER_REPORTS as $report) {
            self::assertMatchesPattern($report);
        }
    }

    /**
     * A scope declared with the attribute is forwarded onto the builder like
     * any query method, as is a builder generic bound to static or to the base
     * model, so those reports are ignored too.
     *
     * @return void
     */
    public function testIgnoresScopeAndGenericallyBoundBuilderReports(): void
    {
        foreach (self::SCOPE_REPORTS as $report) {
            self::assertMatchesPattern($report);
        }
    }

    /**
     * The pattern is scoped to the framework builders, so a genuine dynamic
     * call to a static method elsewhere still surfaces - including one on a
     * project's own builder, which the shipped pattern cannot name.
     *
     * @return void
     */
    public function testLeavesUnrelatedDynamicCallReports(): void
    {
        $pattern = self::readBuilderPattern();

        foreach (self::UNRELATED_REPORTS as $report) {
            self::assertSame(0, preg_match($pattern, $report), "Pattern must not ignore: {$report}");
        }
    }

    /**
     * Assert the shipped pattern ignores a report.
     *
     * @param  string  $report
     * @return void
     */
    private static function assertMatchesPattern(string $report): void
    {
        self::assertSame(
            1,
            preg_match(self::readBuilderPattern(), $report),
            "Pattern must ignore: {$report}",
        );
    }

    /**
     * Read the dynamic-call pattern out of the shipped config.
     *
     * The config loader is internal, so a minor upgrade may move it; that is
     * acceptable here, where the alternative is a second neon parser carried
     * only to read one shipped file, and any breakage surfaces as a failure of
     * this test rather than in anything the package ships.
     *
     * @return string
     */
    private static function readBuilderPattern(): string
    {
        $file = dirname(__DIR__, 3) . '/php/phpstan-laravel.neon';

        // @phpstan-ignore phpstanApi.constructor, phpstanApi.method
        $config = (new NeonAdapter([]))->load($file);

        $ignored = $config['parameters']['ignoreErrors'] ?? [];

        self::assertIsArray($ignored);

        foreach ($ignored as $entry) {
            if (is_array($entry) && ($entry['identifier'] ?? null) === self::DYNAMIC_CALL) {
                self::assertIsString($entry['message'] ?? null);

                return $entry['message'];
            }
        }

        self::fail('The shipped config declares no ' . self::DYNAMIC_CALL . ' pattern.');
    }
}
