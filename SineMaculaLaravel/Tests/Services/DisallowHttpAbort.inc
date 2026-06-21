<?php

namespace App\Services;

class AccountService
{
    public function close($account): void
    {
        abort(403);
        abort_if($account === null, 404);
        abort_unless($account, 401);

        throw new NotFoundHttpException();
        throw new \DomainException();

        $this->log('done');
    }

    private function log(string $message): void
    {
        //
    }
}
