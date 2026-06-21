<?php

namespace App\Models;

class User
{
    public function check(): void
    {
        abort(403);

        throw new NotFoundHttpException();
    }
}
