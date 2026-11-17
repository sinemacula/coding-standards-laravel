<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\DependencyInjection\NeonAdapter;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use SineMacula\CodingStandardsLaravel\PHPStan\Rules\DisallowModelBehaviourRule;

/**
 * Tests the analysis config this package ships to consuming projects.
 *
 * The reports below are verbatim captures from a Laravel project.
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
     * Ignores ordinary query code reports on both Eloquent and query builders.
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
     * Ignores attribute-scope and generically bound builder reports.
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
     * Still reports dynamic calls outside the framework builders.
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
     * Pins the migration path to the analysed project.
     *
     * The booted application's database path can point away from the project.
     *
     * @return void
     */
    public function testPinsTheMigrationPathToTheProject(): void
    {
        $paths = self::readConfig()['parameters']['databaseMigrationsPath'] ?? null;

        self::assertIsArray($paths, 'The shipped config declares no databaseMigrationsPath.');
        self::assertContains('%currentWorkingDirectory%/database/migrations', $paths);
    }

    /**
     * The shipped hook set matches the rule's own default.
     *
     * Rule tests use the default, so only this catches the two drifting apart.
     *
     * @return void
     */
    public function testShipsTheHookSetTheRuleDefaultsTo(): void
    {
        $shipped = self::readConfig()['parameters']['sineMaculaLaravel']['modelHooks'] ?? null;
        $default = (new \ReflectionParameter([DisallowModelBehaviourRule::class, '__construct'], 'hooks'))
            ->getDefaultValue();

        self::assertIsArray($shipped, 'The shipped config declares no modelHooks.');
        self::assertSame($default, $shipped);
    }

    /**
     * Declares in its own schema every parameter it sets for other extensions.
     *
     * PHPStan rejects an undeclared parameter, failing runs without its owner.
     *
     * @return void
     */
    public function testDeclaresTheParametersItSetsForOtherExtensions(): void
    {
        $config     = self::readConfig();
        $schema     = $config['parametersSchema'] ?? [];
        $parameters = $config['parameters']       ?? [];

        self::assertIsArray($schema);
        self::assertIsArray($parameters);

        foreach (['databaseMigrationsPath', 'parseModelCastsMethod'] as $parameter) {
            self::assertArrayHasKey(
                $parameter,
                $schema,
                "The config sets {$parameter} without declaring it, which refuses to start without the "
                . 'extension that owns it.',
            );
            self::assertArrayHasKey($parameter, $parameters);
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
     * Load the analysis config this package ships.
     *
     * The loader is internal API, accepted over carrying a second neon parser.
     *
     * @return array<string, mixed>
     */
    private static function readConfig(): array
    {
        $file = dirname(__DIR__, 3) . '/php/phpstan-laravel.neon';

        // @phpstan-ignore phpstanApi.constructor, phpstanApi.method
        return (new NeonAdapter([]))->load($file);
    }

    /**
     * Read the dynamic-call pattern out of the shipped config.
     *
     * @return string
     */
    private static function readBuilderPattern(): string
    {
        $ignored = self::readConfig()['parameters']['ignoreErrors'] ?? [];

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
