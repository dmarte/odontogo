<?php

namespace App\Observers;

use App\Pivots\TeamUser;
use Illuminate\Support\Str;

class TeamUserObserver
{
    public function creating(TeamUser  $membership) {
        $membership->token = Str::uuid();
        $membership->invited_at = now()->format('Y-m-d H:i:s');
        $membership->author_user_id = $membership->author_user_id ?? $membership->user_id;
    }

}
