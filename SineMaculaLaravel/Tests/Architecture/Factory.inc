<?php

namespace Database\Factories;

use App\Credentials\Services\CredentialManager;
use App\Models\Credential;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\App;

class CredentialFactory extends Factory
{
    protected $model = Credential::class;

    public function definition(): array
    {
        return [
            'digest' => app(CredentialManager::class)->digest('token'),
        ];
    }

    public function configure(): static
    {
        $manager = App::make(CredentialManager::class);

        return $this->afterMaking(static function (Credential $credential) use ($manager): void {
            $credential->digest = $manager->digest('token');
        });
    }

    public function revoked(): static
    {
        $manager = resolve(CredentialManager::class);

        return $this->state(fn (): array => ['digest' => $manager->digest('revoked')]);
    }
}
