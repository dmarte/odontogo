<?php


namespace App\Scopes;


use App\Models\Member;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TeamScope implements Scope
{
    public function __construct(private Member $member)
    {
    }

    public function apply(Builder $query, Model $model) : void
    {
        $query->where('team_id', $this->member->team_id);
    }
}
