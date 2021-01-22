<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class UserInvitationController extends Controller
{
    public function __invoke(Team $team, string $token)
    {
        /* @var $member \App\Models\Member */
        $member = $team->members()->where('token', $token)->firstOrFail();

        $member->activate();

        return redirect()->to('/');
    }
}
