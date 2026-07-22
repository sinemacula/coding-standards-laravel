<?php

namespace Services\Billing;

class InvoiceService
{
    public function fail(): void
    {
        abort(500);
    }
}
