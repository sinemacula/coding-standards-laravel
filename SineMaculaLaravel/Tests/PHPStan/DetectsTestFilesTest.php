<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\PHPStan;

use PHPStan\Analyser\Scope;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;
use SineMacula\CodingStandardsLaravel\PHPStan\Concerns\DetectsTestFiles;

/**
 * Tests for the tests-directory detection concern.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversTrait(DetectsTestFiles::class)]
final class DetectsTestFilesTest extends TestCase
{
    /**
     * A POSIX path under tests/ is detected.
     *
     * @return void
     */
    public function testMatchesAPosixTestsPath(): void
    {
        self::assertTrue($this->isTestPath('/app/tests/Feature/StoreUserRequestTest.php'));
    }

    /**
     * A Windows path under tests\ is detected through separator normalisation.
     *
     * @return void
     */
    public function testMatchesAWindowsTestsPath(): void
    {
        self::assertTrue($this->isTestPath('C:\app\tests\Feature\StoreUserRequestTest.php'));
    }

    /**
     * A production path is not detected.
     *
     * @return void
     */
    public function testIgnoresAProductionPath(): void
    {
        self::assertFalse($this->isTestPath('/app/src/Http/Requests/StoreUserRequest.php'));
    }

    /**
     * Run the concern against a scope reporting the given file path.
     *
     * @param  string  $path
     * @return bool
     */
    private function isTestPath(string $path): bool
    {
        $scope = self::createStub(Scope::class);
        $scope->method('getFile')->willReturn($path);

        $detector = new class {
            use DetectsTestFiles;

            /**
             * Expose the concern's detection to the test.
             *
             * @param  \PHPStan\Analyser\Scope  $scope
             * @return bool
             */
            public function isUnderTests(Scope $scope): bool
            {
                return $this->isTestFile($scope);
            }
        };

        return $detector->isUnderTests($scope);
    }
}
