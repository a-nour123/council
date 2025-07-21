<?php

namespace App\Filament\Resources\CollegeCouncilResource\Pages;

use App\Filament\Resources\CollegeCouncilResource;
use App\Models\Session;
use App\Models\SessionTopic;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateCollegeCouncil extends CreateRecord
{
    protected static string $resource = CollegeCouncilResource::class;

    // protected function afterCreate(): void
    // {
    //     $sessionId = $this->record->id;

    //     // Get all topic_agenda_ids related to the selected session using query builder
    //     $topicAgendas = DB::table('session_topics')
    //     ->join('topics_agendas', 'session_topics.topic_agenda_id', '=', 'topics_agendas.id')
    //     ->where('session_topics.session_id', 1)
    //     ->pluck('topics_agendas.topic_id');
    //     dd($topicAgendas);

    //     // Store these topic IDs in session_topics table
    //     foreach ($topicAgendas as $topicId) {
    //         SessionTopic::create([
    //             'session_id' => $sessionId,
    //             'topic_id' => $topicId,
    //         ]);
    //     }
    // }

    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     // Get the selected session ID from the form data
    //     $sessionId = $data['session_id'];

    //     // Get all topic_agenda_ids related to the selected session using query builder
    //     $topicAgendas = DB::table('session_topics')
    //         ->join('topics_agendas', 'session_topics.topic_agenda_id', '=', 'topics_agendas.id')
    //         ->where('session_topics.session_id', $sessionId)
    //         ->pluck('topics_agendas.topic_id');


    //     // Store these topic IDs in session_topics table
    //     foreach ($topicAgendas as $topicId) {
    //         DB::table('college_councils')->insert([
    //             'session_id' => $sessionId,
    //             'topic_id' => $topicId,
    //         ]);
    //     }

    //     return $data;
    // }

    protected function afterCreate()
    {
        // Get the session ID from the created record
        $sessionId = $this->record->session_id;

        // Get all topic_agenda_ids related to the selected session using query builder
        $topicAgendas = DB::table('session_topics')
            ->join('topics_agendas', 'session_topics.topic_agenda_id', '=', 'topics_agendas.id')
            ->where('session_topics.session_id', $sessionId)
            ->pluck('topics_agendas.id');

        // Current timestamp
        $currentTimestamp = Carbon::now();

        // Ensure no duplicate entries
        foreach ($topicAgendas as $topicId) {
            // Check if the record already exists
            $exists = DB::table('college_councils')
                ->where('session_id', $sessionId)
                ->where('topic_id', $topicId)
                ->exists();

            if (!$exists) {
                DB::table('college_councils')->insert([
                    'session_id' => $sessionId,
                    'topic_id' => $topicId,
                    'created_at' => $currentTimestamp,
                    'updated_at' => $currentTimestamp,
                ]);
            }
        }
    }
}
