<?php

namespace App\Http\Resources;

use App\Services\AvatarUrls;

abstract class ApiResource
{
}

class ProfileResource extends ApiResource
{
    public function avatar(): string
    {
        return app(AvatarUrls::class)->current();
    }
}
