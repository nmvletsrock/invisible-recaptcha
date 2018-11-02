<?php

namespace AlbertCht\InvisibleReCaptcha;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;

class InvisibleReCaptchaServiceProvider extends ServiceProvider
{
    /**
     * Boot the services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootConfig();
        $this->app['validator']->extend('captchai', function ($attribute, $value) {
            return $this->app['captchai']->verifyResponse($value, $this->app['request']->getClientIp());
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('captchai', function ($app) {
            return new InvisibleReCaptcha(
                $app['config']['captcha.siteKey'],
                $app['config']['captcha.secretKey'],
                $app['config']['captcha.options']
            );
        });

        $this->app->afterResolving('blade.compiler', function () {
            $this->addBladeDirective($this->app['blade.compiler']);
        });
    }

    /**
     * Boot configure.
     *
     * @return void
     */
    protected function bootConfig()
    {
        $path = __DIR__.'/config/captcha.php';

        $this->mergeConfigFrom($path, 'captcha');

        if (function_exists('config_path')) {
            $this->publishes([$path => config_path('captcha.php')]);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['captchai'];
    }

    /**
     * @param BladeCompiler $blade
     * @return void
     */
    public function addBladeDirective(BladeCompiler $blade)
    {
        $blade->directive('captchai', function ($lang) {
            return "<?php echo app('captcha')->render({$lang}); ?>";
        });
    }
}
