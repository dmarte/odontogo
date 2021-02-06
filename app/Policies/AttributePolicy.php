<?php

namespace App\Policies;

use App\Models\Attribute;
use App\Models\User;
use App\Models\Member;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttributePolicy
{
    use HandlesAuthorization;

    public function create(User $user) {

        if ($user->member->role->is_administrator) {
            return true;
        }

        return $user->member->role->scopes[Member::SCOPE_ATTRIBUTES_ADD] ?? false;
    }

    public function view(User $user, Attribute $attribute) {
        return false;
    }

    public function delete(User $user, Attribute $attribute) {
        return $user
            ->memberships
            ->where('team_id', $attribute->team_id)
            ->where('role.scopes.'. Member::SCOPE_ATTRIBUTES_DELETE, true)
            ->isNotEmpty()
             ||
            $user
            ->memberships
            ->where('team_id', $attribute->team_id)
            ->where('role.is_administrator', true)
            ->isNotEmpty()
            ;
    }

    public function update(User $user, Attribute $attribute)
    {
        if ($user->member->role->is_administrator) {
            return true;
        }

        return $user
            ->memberships
            ->where('team_id', $attribute->team_id)
            ->where('role.scopes.' . Member::SCOPE_ATTRIBUTES_MODIFY, true)
            ->isNotEmpty();
    }
}
