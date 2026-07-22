<?php

namespace App\Services;

class EdgeCaseService
{
    public function edges($account): void
    {
        ABORT(418);
        $this->abort(403);

        throw new \Symfony\Component\HttpKernel\Exception\ConflictHttpException();
        throw new/* factory */NotFoundHttpException();
    }

    private function abort(int $code): void
    {
        //
    }
}
