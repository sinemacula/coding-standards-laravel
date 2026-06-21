<?php

namespace SineMaculaLaravel\Tests\PHPStan\Data;

use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'      => 'x',
            'created_at' => 'now',
            'updated_at' => 'now',
        ];
    }
}

class CleanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => 'x',
        ];
    }
}

class CallFactory extends Factory
{
    public function definition(): array
    {
        return $this->state([]);
    }
}

class NoDefinitionFactory extends Factory
{
}

class NotAFactory
{
    public function definition(): array
    {
        return [
            'created_at' => 'now',
        ];
    }
}
