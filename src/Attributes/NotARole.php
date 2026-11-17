<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\Attributes;

/**
 * Mark a class as playing no Laravel role.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class NotARole {}
