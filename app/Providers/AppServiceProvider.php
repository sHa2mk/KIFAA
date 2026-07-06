<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * This method customizes the post-authentication redirect behavior.
     * Users without extracted skills are sent to the CV upload page, while
     * users who already have a career profile are sent to the dashboard.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponse::class, function () {
            return new class implements LoginResponse {
                /**
                 * Redirect the user after login.
                 *
                 * @param  \Illuminate\Http\Request  $request
                 * @return \Illuminate\Http\RedirectResponse
                 */
                public function toResponse($request)
                {
                    $user = $request->user();

                    if ($user->skills()->count() === 0) {
                        return redirect()->route('cv.upload.form');
                    }

                    return redirect()->route('dashboard');
                }
            };
        });

        $this->app->singleton(RegisterResponse::class, function () {
            return new class implements RegisterResponse {
                /**
                 * Redirect the user after registration.
                 *
                 * @param  \Illuminate\Http\Request  $request
                 * @return \Illuminate\Http\RedirectResponse
                 */
                public function toResponse($request)
                {
                    return redirect()->route('cv.upload.form');
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure application-wide defaults.
     *
     * This method sets immutable dates, protects destructive database commands
     * in production, and applies stronger password rules in production.
     *
     * @return void
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }
}