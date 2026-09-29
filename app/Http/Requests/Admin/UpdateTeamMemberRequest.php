<?php

namespace App\Http\Requests\Admin;

class UpdateTeamMemberRequest extends TeamMemberFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('teamMember')) ?? false;
    }
}
