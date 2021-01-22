<?php

namespace App\Observers;

use App\Models\Member;
use Illuminate\Support\Str;

class MemberObserver
{
    public function creating(Member  $membership) {
        $membership->token = Str::uuid();
        $membership->invited_at = now()->format('Y-m-d H:i:s');
        $membership->author_user_id = $membership->author_user_id ?? $membership->user_id;
    }

}
