<?php

namespace App\Filament\Resources\ReportSessionsResource\Pages;

use App\Filament\Resources\ReportSessionsResource;
use App\Models\{
    Department,
    Faculty,
    Session,
    YearlyCalendar
};
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ManageReportSessions extends Page
{
    protected static string $resource = ReportSessionsResource::class;
    protected static string $view = 'filament.resources.reports.session';

    public  $years, $faculties, $departments;
    public function mount()
    {
        $this->years = YearlyCalendar::query()->pluck("name", "id");

        $this->faculties = Faculty::query()->pluck('ar_name', 'id')->toArray();
    }

    public function getTitle(): string|Htmlable
    {
        return __('Session departments reports');
    }
    public function getBreadcrumb(): ?string
    {
        return null;
    }
}
