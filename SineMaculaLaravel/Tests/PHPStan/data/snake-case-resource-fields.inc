<?php

namespace SineMaculaLaravel\Tests\PHPStan\Data;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'first_name' => $this->first_name,
            'createdAt'  => $this->created_at,
            'address'    => [
                'line_1'   => $this->line_1,
                'postCode' => $this->post_code,
            ],
            'tags'       => ['php', 'laravel'],
            ...$this->extra(),
        ];
    }
}

class PlainResource extends JsonResource
{
    public function toArray($request): array
    {
        return $this->resource;
    }
}

class EmptyResource extends JsonResource
{
}

class NotAResource
{
    public function toArray($request): array
    {
        return ['badKey' => 1];
    }
}
