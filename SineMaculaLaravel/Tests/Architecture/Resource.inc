<?php

namespace App\Http\Resources;

use App\Services\AvatarUrls;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use SineMacula\ApiToolkit\Http\Resources\ApiResource;
use SineMacula\ApiToolkit\Http\Resources\ReportResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return ['avatar' => app(AvatarUrls::class)->for($this->resource)];
    }
}

class UserCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return ['links' => resolve(AvatarUrls::class)->all()];
    }
}

class NoticeResource extends ApiResource
{
    public static function schema(): array
    {
        return [
            'avatar' => static fn ($notice): string => app(AvatarUrls::class)->for($notice),
        ];
    }
}

class UsageReport extends ReportResource
{
    public static function schema(): array
    {
        return [
            'avatar' => static fn ($row): string => app(AvatarUrls::class)->for($row),
        ];
    }
}
