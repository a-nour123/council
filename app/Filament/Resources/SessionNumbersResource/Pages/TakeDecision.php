<?php

namespace App\Filament\Resources\SessionNumbersResource\Pages;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Filament\Resources\SessionNumbersResource;
use App\Models\{
    Session,
    CollegeCouncil,
    SessionTopic
};
use Carbon\Carbon;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\App;

class TakeDecision extends Page
{
    protected static string $resource = SessionNumbersResource::class;
    protected static string $view = 'filament.resources.college-council.pages.decision';

    public $session, $topics;

    public function mount($recordId)
    {
        if (auth()->user()->position_id == 5) {

            // if sesssion already has token action
            if (!CollegeCouncil::where('session_id', $recordId)->exists()) {
                $session = Session::findOrFail($recordId);
                $sessionTopics = SessionTopic::where('session_topics.session_id', $recordId)
                    ->join('topics_agendas as agenda', 'agenda.id', '=', 'session_topics.topic_agenda_id')
                    ->join('topics as topic', 'topic.id', '=', 'agenda.topic_id')
                    ->select(
                        'agenda.id as agenda_id',
                        'agenda.escalation_authority as agenda_escalation', // 1=> refer to departement, 2=> refer to college
                        'topic.title as agenda_topic',
                    )
                    ->get();

                // dd($sessionTopics->toArray());
                $this->session = $session;
                $this->topics = $sessionTopics;
            } else {
                abort(403, "Sorry session already has decision");
            }
        } else {
            abort(403);
        }
    }


    public function getTitle(): string|Htmlable
    {
        return __("Decision of session's topics");
    }
}
