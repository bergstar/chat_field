<?php

namespace Toolborg\ChatField;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Toolborg\ChatField\Livewire\ChatWindow;

class ChatFieldServiceProvider extends PackageServiceProvider
{
    public static string $name = 'chat-field';

    public static string $viewNamespace = 'chat-field';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews(static::$viewNamespace)
            ->hasMigrations([
                'create_chat_field_threads_table',
                'create_chat_field_messages_table',
            ])
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('bergstar/chat-field');
            });
    }

    public function packageBooted(): void
    {
        FilamentAsset::register(
            [
                Css::make('chat-field-styles', __DIR__ . '/../resources/css/chat-field.css')->loadedOnRequest(),
            ],
            'bergstar/chat-field',
        );

        Livewire::component('toolborg-chat-field-window', ChatWindow::class);
    }
}
