<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [];
    }
}

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
}

abstract class ScopeRequest extends FormRequest
{
    public function rules(): array
    {
        return [];
    }
}

class FraudRequest extends ScopeRequest
{
}

abstract class BareRequest extends FormRequest
{
}

class OrphanRequest extends BareRequest
{
}

class DeleteUserRequest extends FormRequest
{
    public function Rules(): array
    {
        return [];
    }
}

abstract class HiddenRulesRequest extends FormRequest
{
    private function rules(): array
    {
        return [];
    }
}

class InheritsHiddenRulesRequest extends HiddenRulesRequest
{
}

class ProtectedRulesRequest extends FormRequest
{
    protected function rules(): array
    {
        return [];
    }
}
