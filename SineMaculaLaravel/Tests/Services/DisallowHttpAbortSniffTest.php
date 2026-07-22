<?php

declare(strict_types = 1);

namespace SineMaculaLaravel\Tests\Services;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\DetectsFunctionCalls;
use SineMacula\CodingStandardsLaravel\Sniffs\Concerns\ResolvesNamespace;
use SineMaculaLaravel\Sniffs\Services\DisallowHttpAbortSniff;
use SineMaculaLaravel\Tests\AbstractSniffTestCase;

/**
 * Tests for the HTTP-abort-in-services sniff.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 *
 * @internal
 */
#[CoversClass(DisallowHttpAbortSniff::class)]
#[CoversTrait(DetectsFunctionCalls::class)]
#[CoversTrait(ResolvesNamespace::class)]
final class DisallowHttpAbortSniffTest extends AbstractSniffTestCase
{
    /**
     * abort() helpers and *HttpException instantiations are flagged inside a
     * Services namespace; domain exceptions are not.
     *
     * @return void
     */
    public function testFlagsHttpAbortsInServices(): void
    {
        $this->assertErrorsOnLines('DisallowHttpAbort.inc', [9, 10, 11, 13]);
    }

    /**
     * The error names the abort helper or the HTTP exception class.
     *
     * @return void
     */
    public function testReportsAbortAndExceptionMessages(): void
    {
        $this->assertErrorMessagesOnLines('DisallowHttpAbort.inc', [
            9  => ['Services must not abort the request ("abort()"); throw a domain exception instead.'],
            10 => ['Services must not abort the request ("abort_if()"); throw a domain exception instead.'],
            11 => ['Services must not abort the request ("abort_unless()"); throw a domain exception instead.'],
            13 => ['Services must not throw HTTP exceptions ("NotFoundHttpException"); throw a domain exception instead.'],
        ]);
    }

    /**
     * An uppercase abort call, a fully qualified HTTP exception and a `new`
     * separated from its class only by a comment are flagged; a same-named
     * method call is not.
     *
     * @return void
     */
    public function testFlagsAbortEdgeCases(): void
    {
        $this->assertErrorsOnLines('DisallowHttpAbortEdgeCases.inc', [9, 12, 13]);
    }

    /**
     * A namespace declared with the braced syntax is still resolved.
     *
     * @return void
     */
    public function testFlagsAbortsInBracedNamespaces(): void
    {
        $this->assertErrorsOnLines('DisallowHttpAbortBracedNamespace.inc', [8]);
    }

    /**
     * A namespace whose first segment is Services is a service namespace.
     *
     * @return void
     */
    public function testFlagsLeadingServicesNamespaceSegment(): void
    {
        $this->assertErrorsOnLines('DisallowHttpAbortLeadingServices.inc', [9]);
    }

    /**
     * A namespace declared directly after a compact declare statement is still
     * resolved.
     *
     * @return void
     */
    public function testFlagsServicesNamespaceAfterCompactDeclare(): void
    {
        $this->assertErrorsOnLines('DisallowHttpAbortCompactDeclare.inc', [7]);
    }

    /**
     * The same calls are ignored outside a Services namespace.
     *
     * @return void
     */
    public function testIgnoresNonServiceNamespaces(): void
    {
        $this->assertErrorsOnLines('DisallowHttpAbortNonService.inc', []);
    }

    /**
     * A namespace segment merely ending or starting with Services is not a
     * service namespace.
     *
     * @return void
     */
    public function testIgnoresNamespaceSegmentsContainingServices(): void
    {
        $this->assertErrorsOnLines('DisallowHttpAbortMicroServices.inc', []);
        $this->assertErrorsOnLines('DisallowHttpAbortServicesLegacy.inc', []);
    }

    /**
     * The same calls are ignored in a file with no namespace.
     *
     * @return void
     */
    public function testIgnoresFilesWithoutANamespace(): void
    {
        $this->assertErrorsOnLines('DisallowHttpAbortGlobal.inc', []);
    }

    /**
     * A namespace-less file importing a Services class is still not a service
     * namespace.
     *
     * @return void
     */
    public function testIgnoresServicesImportsWithoutANamespace(): void
    {
        $this->assertErrorsOnLines('DisallowHttpAbortGlobalUse.inc', []);
    }
}
