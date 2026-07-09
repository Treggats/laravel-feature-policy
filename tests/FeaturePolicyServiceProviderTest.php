<?php

declare(strict_types=1);

namespace Mazedlx\FeaturePolicy\Tests;

use Mazedlx\FeaturePolicy\FeaturePolicyServiceProvider;
use PHPUnit\Framework\Attributes\Test;

final class FeaturePolicyServiceProviderTest extends TestCase
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
    public function it_falls_back_to_a_published_legacy_config_file(): void
    {
        file_put_contents(config_path('feature-policy.php'), "<?php\n\nreturn ['policy' => 'legacy-policy-class'];\n");
        config()->set('feature-policy', ['policy' => 'legacy-policy-class']);

        $this->app->register(FeaturePolicyServiceProvider::class, force: true);

        $this->assertSame('legacy-policy-class', config('permissions-policy.policy'));
    }

    #[Test]
    public function it_ignores_the_legacy_config_file_once_the_new_config_has_been_published(): void
    {
        file_put_contents(config_path('feature-policy.php'), "<?php\n\nreturn ['policy' => 'legacy-policy-class'];\n");
        file_put_contents(config_path('permissions-policy.php'), "<?php\n\nreturn ['policy' => 'new-policy-class'];\n");
        config()->set('permissions-policy.policy', 'new-policy-class');

        $this->app->register(FeaturePolicyServiceProvider::class, force: true);

        $this->assertSame('new-policy-class', config('permissions-policy.policy'));
    }

    #[Test]
    public function it_does_nothing_when_no_legacy_config_file_exists(): void
    {
        config()->set('permissions-policy.policy', 'default-policy-class');

        $this->app->register(FeaturePolicyServiceProvider::class, force: true);

        $this->assertSame('default-policy-class', config('permissions-policy.policy'));
    }
}
