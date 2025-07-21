<?php

namespace App\Filament\Resources\ReportAgendasResource\Pages;

use App\Filament\Resources\ReportAgendasResource;
use App\Models\{
    Department,
    Faculty,
    YearlyCalendar
};
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ManageReportAgendas extends Page
{
    protected static string $resource = ReportAgendasResource::class;
    protected static string $view = 'filament.resources.reports.agenda';

    public  $years, $faculties, $departments;

    public function mount()
    {
        $this->years = YearlyCalendar::query()->pluck("name", "id");

        $this->faculties = Faculty::query()->pluck('ar_name', 'id')->toArray();
    }

    public function getTitle(): string|Htmlable
    {
        return __('Agendas reports');
    }
    public function getBreadcrumb(): ?string
    {
        return null;
    }
}
