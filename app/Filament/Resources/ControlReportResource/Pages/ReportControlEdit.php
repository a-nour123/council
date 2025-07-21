<?php

namespace App\Filament\Resources\ControlReportResource\Pages;

use App\Filament\Resources\ControlReportResource;
use Filament\Resources\Pages\Page;
use App\Models\ControlReport;
use Illuminate\Contracts\Support\Htmlable;

class ReportControlEdit extends Page
{
    protected static string $resource = ControlReportResource::class;
    protected static string $view = 'filament.resources.report.editReport';
    public $existingData;
    public $record;

    public function mount($record)
    {
        if (
            !in_array(auth()->user()->position_id, [2, 3, 4, 5]) &&
            (!auth()->user()->hasRole('Super Admin') && !auth()->user()->hasRole('System Admin'))
        ) {
            abort(403, 'You do not have access to this page.');
        }

        $this->record = $record;
        $this->existingData = ControlReport::find($record);
    }
    public function getTitle(): string|Htmlable
    {
        return __('Edit') . ' ' . __('Control Report');
    }
}
