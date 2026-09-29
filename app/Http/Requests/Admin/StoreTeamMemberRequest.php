<?php

namespace App\Http\Requests\Admin;

use App\Models\TeamMember;

class StoreTeamMemberRequest extends TeamMemberFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', TeamMember::class) ?? false;
    }
}
