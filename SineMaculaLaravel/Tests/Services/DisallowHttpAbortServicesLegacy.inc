<?php

namespace App\ServicesLegacy;

class LegacyGateway
{
    public function fail(): void
    {
        abort(410);
    }
}
