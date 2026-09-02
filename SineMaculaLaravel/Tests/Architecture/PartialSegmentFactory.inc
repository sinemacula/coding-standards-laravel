<?php

namespace App\Support\MyFactories;

use App\Rendering\Services\TemplateCompiler;

class Thing extends Factory
{
    public function build(): string
    {
        return app(TemplateCompiler::class)->compile('x');
    }
}
