<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use BezhanSalleh\FilamentLanguageSwitch\Enums\Placement;
use Filament\Actions\CreateAction;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
            // ->locales(['ar','en'])
            ->locales(['ar'])
            ->labels([
                    'ar' => 'العربية',
                    // 'en' => 'English',
                ])
                ->circular()
                ->visible(outsidePanels: true)
                // ->outsidePanelPlacement(Placement::BottomRight)
                ->outsidePanelPlacement(Placement::TopCenter)
                ;
        });
        \Filament\Resources\Pages\CreateRecord::disableCreateAnother();
        \Filament\Actions\CreateAction::configureUsing(fn(CreateAction $action) => $action->createAnother(false));
    }
}
