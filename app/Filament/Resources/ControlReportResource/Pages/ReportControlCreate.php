<?php

namespace App\Filament\Resources\ControlReportResource\Pages;

use App\Filament\Resources\ControlReportResource;
use App\Models\Topic;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ReportControlCreate extends Page
{
    protected static string $resource = ControlReportResource::class;
    protected static string $view = 'filament.resources.report.createReport';

    public array $topics = []; // Corrected to be plural and typed as array
    public function mount()
    {
        //  $this->recordId = $recordId;
        if (
            !in_array(auth()->user()->position_id, [2, 3, 4, 5]) &&
            (!auth()->user()->hasRole('Super Admin') && !auth()->user()->hasRole('System Admin'))
        ) {
            abort(403, 'You do not have access to this page.');
        }
        // Fetch topics where main_topic_id is not null
        $this->topics = Topic::whereNotNull('main_topic_id')
            ->pluck('title', 'id')
            ->toArray();
    }

    public function getTitle(): string|Htmlable
    {
        return __('Create') . ' ' . __('Control Report');
    }
}
