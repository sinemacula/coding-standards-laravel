<?php

declare(strict_types = 1);

namespace SineMacula\CodingStandardsLaravel\Attributes;

/**
 * Mark a class as playing no Laravel role.
 *
 * The role-based structure sniffs skip a class carrying this attribute
 * entirely, exactly as the `@role-exempt` docblock tag does. The sniffs match
 * the attribute by short name, so any `NotARole` satisfies them; this class
 * ships so the idiomatic `#[NotARole]` reference also resolves under static
 * analysis.
 *
 * @author      Ben Carey <bdmc@sinemacula.co.uk>
 * @copyright   2026 Sine Macula Limited
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class NotARole {}
