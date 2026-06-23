<?php

namespace App\Jobs;

class ProcessThing
{
    public function handle($payload, int $tries): void
    {
    }

    #[\Override]
    public function via($notifiable): array
    {
        return [];
    }

    #[Scheduled('daily')]
    public function run($context): void
    {
    }

    public function typed(string $name): void
    {
    }
}

trait ThrottleRequestsTrait
{
    protected function resolveRequestSignature($request): string
    {
        return '';
    }

    private function buildKey($value): string
    {
        return '';
    }
}
