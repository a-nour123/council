<?php

namespace App\Filament\Resources\ReportFacultySessionsResource\Pages;

use App\Filament\Resources\ReportFacultySessionsResource;
use App\Models\{
    Department,
    Faculty,
    Session,
    YearlyCalendar
};
use Filament\Actions;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ManageReportFacultySessions extends Page
{
    protected static string $resource = ReportFacultySessionsResource::class;
    protected static string $view = 'filament.resources.reports.faculty_session';

    public  $years, $faculties;
    public function mount()
    {
        $this->years = YearlyCalendar::query()->pluck("name", "id");

        // $this->faculties = Faculty::query()->pluck('ar_name', 'id')->toArray();
    }

    public function getTitle(): string|Htmlable
    {
        return __('Faculty session reports');
    }
    public function getBreadcrumb(): ?string
    {
        return null;
    }
}
