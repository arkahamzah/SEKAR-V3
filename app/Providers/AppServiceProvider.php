<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Services\SSOSessionHelper;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('sso.session', function () {
            return new SSOSessionHelper();
        });
    }

    public function boot(): void
    {
        // Register Blade directives
        Blade::directive('userNIK', function () {
            return "<?php echo App\Services\SSOSessionHelper::getUserNIK(); ?>";
        });

        Blade::directive('userName', function () {
            return "<?php echo App\Services\SSOSessionHelper::getUserName(); ?>";
        });

        Blade::directive('userPosition', function () {
            return "<?php echo App\Services\SSOSessionHelper::getUserPosition(); ?>";
        });

        Blade::directive('userUnit', function () {
            return "<?php echo App\Services\SSOSessionHelper::getUserUnit(); ?>";
        });

        Blade::directive('userDivisi', function () {
            return "<?php echo App\Services\SSOSessionHelper::getUserDivisi(); ?>";
        });

        Blade::directive('userLocation', function () {
            return "<?php echo App\Services\SSOSessionHelper::getUserLocation(); ?>";
        });

        // Register global view variables
        view()->composer('*', function ($view) {
            if (auth()->check()) {
                $view->with([
                    'userDetail' => SSOSessionHelper::getFormattedUserInfo(),
                    'currentUserNIK' => SSOSessionHelper::getUserNIK(),
                    'currentUserName' => SSOSessionHelper::getUserName(),
                ]);
            }
        });
        
        // Register class alias
        if (!class_exists('SSOSession')) {
            class_alias(SSOSessionHelper::class, 'SSOSession');
        }
    }
}