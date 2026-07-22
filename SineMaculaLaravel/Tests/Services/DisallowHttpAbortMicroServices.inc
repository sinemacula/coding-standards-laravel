<?php

namespace App\MicroServices;

class GatewayClient
{
    public function fail(): void
    {
        abort(502);
    }
}
