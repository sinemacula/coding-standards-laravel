<?php

namespace App\Services {
    class BracedService
    {
        public function cancel(): void
        {
            abort(404);
        }
    }
}
