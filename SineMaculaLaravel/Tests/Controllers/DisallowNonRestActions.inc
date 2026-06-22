<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;

class UserController extends Controller
{
    public function __construct()
    {
    }

    public function index(): void
    {
        $callback = function (): int {
            return 1;
        };
    }

    public function create(): void
    {
    }

    public function store(): void
    {
    }

    public function show(): void
    {
    }

    public function edit(): void
    {
    }

    public function update(): void
    {
    }

    public function destroy(): void
    {
    }

    public function middleware(): void
    {
    }

    public function authorize(): void
    {
    }

    public function callAction(): void
    {
    }

    public static function make(): self
    {
        return new self();
    }

    protected function helper(): void
    {
    }

    public function processPayment(): void
    {
    }

    /**
     * @utility
     */
    public function buildQuery(): void
    {
    }

    /**
     * @non-rest-action
     */
    public function stream(): void
    {
    }
}

class InvoiceController extends Controller
{
    public function __invoke(): void
    {
    }
}

/**
 * @utility
 */
class HealthController extends Controller
{
    public function ping(): void
    {
    }
}

abstract class BaseController extends Controller
{
    public function sharedHelper(): void
    {
    }
}

class PaymentService
{
    public function processPayment(): void
    {
    }
}

function globalHelper(): void
{
}
