<?php

namespace App\Providers;

// use Illuminate\Auth\Access\Gate;
use App\Models\Company;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        View::composer('*', function ($view) {
            $view->with('companies', Company::all());
        });
        Gate::define('manager' , function($user){
            return $user->role === 'admin' ;
        }); 

        Gate::define('units-sell', function($user){
            return in_array($user->role, ['admin' , 'seller']) ;
        }); 

        Gate::define('dashboard-view' , function($user){
            return in_array($user->role , ['admin', 'seller' , 'accountant']) ; 
        }); 

        Gate::define('payments' , function($user){
            return in_array($user->role , ['admin' , 'accountant']);
        }); 
    }
}
