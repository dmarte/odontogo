<?php

namespace App\Providers;

use App\Models\Budget;
use App\Models\Contact;
use App\Models\Doctor;
use App\Models\Document;
use App\Models\Member;
use App\Models\Patient;
use App\Models\Product;
use App\Models\Provider;
use App\Models\Receipt;
use App\Models\Team;
use App\Models\User;
use App\Observers\ContactObserver;
use App\Observers\DoctorObserver;
use App\Observers\MemberObserver;
use App\Observers\PatientObserver;
use App\Observers\ProductObserver;
use App\Observers\TeamObserver;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
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
        Provider::observe(ContactObserver::class);
        Patient::observe(ContactObserver::class);
        Patient::observe(PatientObserver::class);
        Doctor::observe(ContactObserver::class);
        Doctor::observe(DoctorObserver::class);

        Relation::morphMap([
            'receipt'  => Receipt::class,
            'budget'   => Budget::class,
            'document' => Document::class,
            'doctor'   => Doctor::class,
            'patient'  => Patient::class,
        ]);
    }
}
