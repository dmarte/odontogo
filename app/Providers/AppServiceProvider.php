<?php

namespace App\Providers;

use App\Models\Team;
use App\Models\User;
use App\Observers\TeamObserver;
use App\Observers\TeamUserObserver;
use App\Observers\UserObserver;
use App\Pivots\TeamUser;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Team::observe(TeamObserver::class);
        TeamUser::observe(TeamUserObserver::class);
        User::observe(UserObserver::class);
    }
}
