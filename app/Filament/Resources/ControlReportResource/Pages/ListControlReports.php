<?php

namespace App\Filament\Resources\ControlReportResource\Pages;

use App\Filament\Resources\ControlReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListControlReports extends ListRecords
{
    protected static string $resource = ControlReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\Action::make('Create Report')
            //     ->label(__('Create') . ' ' . __('Control Report'))
            //     ->icon('heroicon-o-pencil')
            //     ->action(function ($record) {
            //         $host = request()->getSchemeAndHttpHost();
            //         $url = $host . '/councils/public/admin/control-reports/createReport';

            //         return redirect()->away($url);
            //     }),
        ];
    }
}
