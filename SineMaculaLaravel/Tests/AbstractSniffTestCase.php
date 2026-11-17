<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Ruleset;
use PHPUnit\Framework\TestCase;

/**
 * Base test case for Sine Macula Laravel PHP_CodeSniffer sniffs.
 *
 * The sniff under test is derived from the test class's name and directory.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
abstract class AbstractSniffTestCase extends TestCase
{
    /**
     * Assert that the sniff reports errors on exactly the given line numbers.
     *
     * @param  string  $fixture
     * @param  array<int, int>  $expectedLines
     * @return void
     */
    protected function assertErrorsOnLines(string $fixture, array $expectedLines): void
    {
        $actualLines = array_keys($this->process($fixture)->getErrors());

        sort($actualLines);
        sort($expectedLines);

        static::assertSame($expectedLines, $actualLines);
    }

    /**
     * Assert that the sniff reports exactly the given error messages, keyed by
     * line number.
     *
     * @param  string  $fixture
     * @param  array<int, list<string>>  $expectedMessages
     * @return void
     */
    protected function assertErrorMessagesOnLines(string $fixture, array $expectedMessages): void
    {
        $actualMessages = [];

        foreach ($this->process($fixture)->getErrors() as $line => $columns) {
            foreach ($columns as $errors) {
                foreach ($errors as $error) {
                    $actualMessages[$line][] = $error['message'];
                }
            }
        }

        ksort($actualMessages);
        ksort($expectedMessages);

        static::assertSame($expectedMessages, $actualMessages);
    }

    /**
     * Property overrides to apply to the sniff under test.
     *
     * @return array<string, mixed>
     */
    protected function sniffProperties(): array
    {
        return [];
    }

    /**
     * Resolve the absolute path of the sniff under test from this test class.
     *
     * Concern tests override this to name the sniff they drive.
     *
     * @return string
     */
    protected function sniffFile(): string
    {
        return str_replace(
            [DIRECTORY_SEPARATOR . 'Tests' . DIRECTORY_SEPARATOR, 'SniffTest.php'],
            [DIRECTORY_SEPARATOR . 'Sniffs' . DIRECTORY_SEPARATOR, 'Sniff.php'],
            $this->testFile(),
        );
    }

    /**
     * Run the sniff under test over a fixture in the test's own directory.
     *
     * @param  string  $fixture
     * @return \PHP_CodeSniffer\Files\LocalFile
     */
    private function process(string $fixture): LocalFile
    {
        // An empty sniff set is rejected; load a standard, drop its sniffs.
        $config            = new Config(['--extensions=inc,php'], false);
        $config->standards = ['Generic'];

        $ruleset         = new Ruleset($config);
        $ruleset->sniffs = [];
        $ruleset->registerSniffs([$this->sniffFile()], [], []);
        $ruleset->populateTokenListeners();

        foreach ($ruleset->sniffs as $sniff) {
            foreach ($this->sniffProperties() as $property => $value) {
                $sniff->{$property} = $value;
            }
        }

        $file = new LocalFile($this->directory() . DIRECTORY_SEPARATOR . $fixture, $ruleset, $config);
        $file->process();

        return $file;
    }

    /**
     * The directory holding this test class and its fixtures.
     *
     * @return string
     */
    private function directory(): string
    {
        return dirname($this->testFile());
    }

    /**
     * Resolve the absolute path of the concrete test class.
     *
     * @return string
     *
     * @throws \LogicException
     */
    private function testFile(): string
    {
        $file = (new \ReflectionClass(static::class))->getFileName();

        if ($file === false) {
            throw new \LogicException(sprintf('Test class %s is not declared in a file.', static::class));
        }

        return $file;
    }
}
