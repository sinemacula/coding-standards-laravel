<?php

namespace App\Support;

use Vendor\Package\Widget;

class Dashboard extends Widget
{
    /**
     * A vendor base class declares this untyped, so PHP forbids typing it here.
     *
     * @untypeable
     */
    protected $view;

    /**
     * The tag is matched case-insensitively.
     *
     * @UNTYPEABLE
     */
    protected static $registry;

    /** @untypeable */
    protected $inline;

    /**
     * A docblock without the tag does not exempt the property.
     *
     * @var string
     */
    protected $label;

    /**
     * Prose mentioning @untypeable is not a tag and does not exempt anything.
     */
    protected $prose;

    protected $orphan;
}
