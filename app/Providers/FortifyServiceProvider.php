<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap application services.
     */
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Custom Authentication
        |--------------------------------------------------------------------------
        */
        Fortify::loginView(function () {
            return view('auth.login');
        });

        Fortify::authenticateUsing(function (Request $request) {

            $user = User::where(
                'username',
                $request->input('username')
            )->first();

            if (
                $user &&
                $user->is_active &&
                Hash::check($request->input('password'), $user->password)
            ) {
                return $user;
            }

            return null;
        });

        /*
        |--------------------------------------------------------------------------
        | Login Rate Limiting
        |--------------------------------------------------------------------------
        */

        RateLimiter::for('login', function (Request $request) {

            $throttleKey = Str::transliterate(
                Str::lower($request->input('username'))
                    . '|'
                    . $request->ip()
            );

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
