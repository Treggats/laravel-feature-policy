<?php

declare(strict_types=1);

namespace Mazedlx\FeaturePolicy\Console;

use Illuminate\Console\Command;

final class UpgradeConfigCommand extends Command
{
    protected $signature = 'permissions-policy:upgrade-config {--force : Overwrite permissions-policy.php if it already exists}';

    protected $description = 'Migrate config/feature-policy.php to config/permissions-policy.php';

    public function handle(): int
    {
        $legacyPath = config_path('feature-policy.php');

        if (! file_exists($legacyPath)) {
            $this->info('No config/feature-policy.php found, nothing to migrate.');

            return self::SUCCESS;
        }

        $newPath = config_path('permissions-policy.php');

        if (file_exists($newPath) && ! $this->option('force')) {
            $this->error('config/permissions-policy.php already exists. Re-run with --force to overwrite it with config/feature-policy.php.');

            return self::FAILURE;
        }

        if (! copy($legacyPath, $newPath)) {
            $this->error('Could not write config/permissions-policy.php.');

            return self::FAILURE;
        }

        $this->info('Published config/permissions-policy.php from your existing config/feature-policy.php.');

        if ($this->confirm('Remove config/feature-policy.php now that it has been migrated?')) {
            unlink($legacyPath);

            $this->info('Removed config/feature-policy.php.');
        }

        return self::SUCCESS;
    }
}
