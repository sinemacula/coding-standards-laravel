<?php

namespace Database\Factories;

use App\Credentials\Services\CredentialManager;

class CredentialFactory extends BaseFactory
{
    public function definition(): array
    {
        return [
            'digest' => app(CredentialManager::class)->digest('token'),
        ];
    }
}
