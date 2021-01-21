<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class UserInvitationController extends Controller
{
    public function __invoke(Team $team, string $token)
    {
        /* @var $user \App\Models\User */
        $user = $team->users()->wherePivot('token', $token)->firstOrFail();

        $user->membership->activate();

        return redirect()->to('/');
    }
}
