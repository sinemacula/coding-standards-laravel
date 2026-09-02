<?php

namespace App\View;

use App\Rendering\Services\TemplateCompiler;
use Illuminate\Support\Facades\App;
use Illuminate\View\Factory;

class ThemeFactory extends Factory
{
    public function compile(string $template): string
    {
        $compiler = app(TemplateCompiler::class);

        return $compiler->compile($template);
    }

    public function render(string $template): string
    {
        return App::make(TemplateCompiler::class)->render($template);
    }
}
