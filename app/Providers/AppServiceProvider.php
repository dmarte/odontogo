<?php

namespace App\Providers;

use App\Models\Contact;
use App\Models\Product;
use App\Models\Team;
use App\Models\User;
use App\Observers\ContactObserver;
use App\Observers\ProductObserver;
use App\Observers\TeamObserver;
use App\Observers\MemberObserver;
use App\Observers\UserObserver;
use App\Models\Member;
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
        Member::observe(MemberObserver::class);
        User::observe(UserObserver::class);
        Product::observe(ProductObserver::class);
        Contact::observe(ContactObserver::class);
    }
}
