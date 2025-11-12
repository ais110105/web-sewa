<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('edit-roles');
    }

    public function rules(): array
    {
        $roleId = $this->route('role');

        return [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $roleId],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Role Name',
            'permissions' => 'Permissions',
        ];
    }
}
