<?php

namespace Khofo;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

use Khofo\vendor\Commands\KhofoCommand;
use Khofo\vendor\Commands\KhofoInstall;
use Khofo\vendor\Commands\KhofoDelete;
use Khofo\vendor\Commands\KhofoCores;
use Khofo\vendor\Commands\KhofoMakeCore;
use Khofo\vendor\Commands\KhofoMakeController;
use Khofo\vendor\Commands\KhofoMakeModel;
use Khofo\vendor\Commands\KhofoMakeCommand;
use Khofo\vendor\Commands\KhofoMakeMigration;
use Khofo\vendor\Commands\KhofoMigrate;

class KhofoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadCommands();
    }

    public function loadCommands() {

        if ($this->app->runningInConsole()) {
            $this->commands([
                KhofoInstall::class,
                KhofoCommand::class,
                KhofoDelete::class,
                KhofoCores::class,
                KhofoMakeCore::class,
                KhofoMakeController::class,
                KhofoMakeModel::class,
                KhofoMakeCommand::class,
                KhofoMakeMigration::class,
                KhofoMigrate::class
            ]);
        }
    }
}
