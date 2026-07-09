<?php

declare(strict_types=1);

namespace Mazedlx\FeaturePolicy\Tests\Console;

use Mazedlx\FeaturePolicy\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class UpgradeConfigCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        foreach (['feature-policy.php', 'permissions-policy.php'] as $file) {
            $path = config_path($file);

            if (file_exists($path)) {
                unlink($path);
            }
        }

        parent::tearDown();
    }

    #[Test]
    public function it_reports_when_there_is_nothing_to_migrate(): void
    {
        $this->artisan('permissions-policy:upgrade-config')
            ->assertExitCode(0);

        $this->assertFileDoesNotExist(config_path('permissions-policy.php'));
    }

    #[Test]
    public function it_copies_the_legacy_config_file_as_is(): void
    {
        $legacyContents = "<?php\n\nreturn ['policy' => 'legacy-policy-class'];\n";
        file_put_contents(config_path('feature-policy.php'), $legacyContents);

        $this->artisan('permissions-policy:upgrade-config')
            ->expectsConfirmation('Remove config/feature-policy.php now that it has been migrated?', 'no')
            ->assertExitCode(0);

        $this->assertSame($legacyContents, file_get_contents(config_path('permissions-policy.php')));
        $this->assertFileExists(config_path('feature-policy.php'));
    }

    #[Test]
    public function it_removes_the_legacy_file_when_the_user_confirms(): void
    {
        file_put_contents(config_path('feature-policy.php'), "<?php\n\nreturn ['policy' => 'legacy-policy-class'];\n");

        $this->artisan('permissions-policy:upgrade-config')
            ->expectsConfirmation('Remove config/feature-policy.php now that it has been migrated?', 'yes')
            ->assertExitCode(0);

        $this->assertFileDoesNotExist(config_path('feature-policy.php'));
    }

    #[Test]
    public function it_wont_overwrite_an_existing_new_config_without_force(): void
    {
        file_put_contents(config_path('feature-policy.php'), "<?php\n\nreturn ['policy' => 'legacy-policy-class'];\n");
        file_put_contents(config_path('permissions-policy.php'), "<?php\n\nreturn ['policy' => 'existing-policy-class'];\n");

        $this->artisan('permissions-policy:upgrade-config')
            ->assertExitCode(1);

        $this->assertSame(
            "<?php\n\nreturn ['policy' => 'existing-policy-class'];\n",
            file_get_contents(config_path('permissions-policy.php')),
        );
    }

    #[Test]
    public function it_overwrites_an_existing_new_config_with_force(): void
    {
        $legacyContents = "<?php\n\nreturn ['policy' => 'legacy-policy-class'];\n";
        file_put_contents(config_path('feature-policy.php'), $legacyContents);
        file_put_contents(config_path('permissions-policy.php'), "<?php\n\nreturn ['policy' => 'existing-policy-class'];\n");

        $this->artisan('permissions-policy:upgrade-config', ['--force' => true])
            ->expectsConfirmation('Remove config/feature-policy.php now that it has been migrated?', 'no')
            ->assertExitCode(0);

        $this->assertSame($legacyContents, file_get_contents(config_path('permissions-policy.php')));
    }
}
