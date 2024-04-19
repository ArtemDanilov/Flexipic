<?php

namespace Artemdanilov\Flexipic;

use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        \Artemdanilov\Flexipic\Tags\Flexipic::class
    ];

    public function bootAddon()
    {
        Statamic::afterInstalled(function ($command) {
            $command->call('vendor:publish', ['--tag' => 'flexipic']);
        });

        $this->bootAddonConfig();
    }

    protected function bootAddonConfig(): self
    {
        $this->mergeConfigFrom(__DIR__.'/../config/flexipic.php', 'statamic.flexipic');

        if (!file_exists(config_path('statamic/flexipic.php'))) {
            $this->publishes([
                __DIR__.'/../config/flexipic.php' => config_path('statamic/flexipic.php'),
            ], 'flexipic');
        }

        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/flexipic'),
        ], 'flexipic');

        return $this;
    }
}
