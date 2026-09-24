<?php

namespace App\Repositories;

use App\Models\Credential;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use SineMacula\Repositories\ApiRepository;
use SineMacula\Repositories\Attributes\Model;

#[Model(Credential::class)]
class CredentialRepository extends ApiRepository
{
}

class StrayAccount extends EloquentModel
{
}
