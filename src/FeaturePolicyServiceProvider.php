<?php

namespace Mazedlx\FeaturePolicy;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Mazedlx\FeaturePolicy\Console\UpgradeConfigCommand;

final class FeaturePolicyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        if (! function_exists('config_path')) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/permissions-policy.php' => config_path('permissions-policy.php'),
        ], 'config');

        $this->commands([
            UpgradeConfigCommand::class,
        ]);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/permissions-policy.php', 'permissions-policy');

        if (! function_exists('config_path') || file_exists(config_path('permissions-policy.php'))) {
            return;
        }

        if (! file_exists(config_path('feature-policy.php'))) {
            return;
        }

        $legacyConfig = config('feature-policy');

        if (is_array($legacyConfig) && $legacyConfig !== []) {
            config(['permissions-policy' => array_merge(config('permissions-policy', []), $legacyConfig)]);
        }

        Log::warning(
            'config/feature-policy.php is deprecated and will stop being read in a future major version. '
            . 'Run `php artisan permissions-policy:upgrade-config` to migrate to config/permissions-policy.php.'
        );
    }
}
