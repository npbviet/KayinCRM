<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Thêm 2 dòng này
use Webkul\User\Models\User;
use App\Observers\UserObserver;

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
        // ĐĂNG KÝ OBSERVER ĐỂ TỰ ĐỘNG GÁN ROLE CHO USER SOCIAL LOGIN
        User::observe(UserObserver::class);
    }
}