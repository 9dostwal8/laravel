<?php

namespace App\Providers;

use App\Models\ProjectVariant;
use App\Models\User;
use App\Models\Letter;
use App\Models\Command;
use App\Models\Document;
use App\Models\Progress;
use App\Models\Note;
use App\Models\Alert;
use App\Models\InvestorProject;
use App\Observers\ProjectVariantObserver;
use App\Observers\LetterObserver;
use App\Observers\CommandObserver;
use App\Observers\DocumentObserver;
use App\Observers\ProgressObserver;
use App\Observers\NoteObserver;
use App\Observers\AlertObserver;
use App\Observers\InvestorProjectObserver;
use App\Policies\BackupDestinationPolicy;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Filament\Resources\Resource;
use Filament\Support\Assets\Css;
use Filament\Forms\Components\FileUpload;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use ShuvroRoy\FilamentSpatieLaravelBackup\Models\BackupDestination;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        ini_set('upload_max_filesize', '60M');
        ini_set('post_max_size', '60M');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FileUpload::macro('safeDefaults', function () {
            return $this
                ->acceptedFileTypes([
                    'image/*',   // فقط عکس
                    'application/pdf', // مثال: PDF
                ])
                ->rules([
                    'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx'
                ]);
        });
        
        Gate::policy(BackupDestination::class, BackupDestinationPolicy::class);

        Model::shouldBeStrict(! $this->app->environment('production'));

        Resource::scopeToTenant(false);

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch->locales(['en', 'ckb', 'ar']);
        });

        Livewire::setScriptRoute(function ($handle) {
            return Route::get('/livewire/livewire-js', $handle);
        });

        FilamentAsset::register([
            Css::make('custom-css', __DIR__ . '/../../resources/css/fonts.css'),
        ]);

        Gate::define('use-translation-manager', function (?User $user) {
            return $user !== null && $user->hasRole('super_admin');
        });

        // Register observers
        ProjectVariant::observe(ProjectVariantObserver::class);
        Letter::observe(LetterObserver::class);
        Command::observe(CommandObserver::class);
        Document::observe(DocumentObserver::class);
        Progress::observe(ProgressObserver::class);
        Note::observe(NoteObserver::class);
        Alert::observe(AlertObserver::class);
        InvestorProject::observe(InvestorProjectObserver::class);
    }
}
