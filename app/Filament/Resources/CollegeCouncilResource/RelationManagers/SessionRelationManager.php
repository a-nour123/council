<?php

namespace App\Filament\Resources\CollegeCouncilResource\RelationManagers;

use App\Models\Agenda;
use App\Models\CollegeCouncil;
use App\Models\SessionTopic;
use App\Models\Topic;
use App\Models\TopicAgenda;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

use function PHPUnit\Framework\returnSelf;

class SessionRelationManager extends RelationManager
{
    protected static string $relationship = 'session';

    public function form(Form $form): Form
    {
        $sessionId = $this->getOwnerRecord()->session_id;

        return $form
            ->schema([
                Repeater::make('Decision')
                    ->label("")
                    ->schema([
                        Forms\Components\Select::make('topic_id')
                            ->label(__('Topic Title'))
                            ->required()
                            ->options(function () use ($sessionId) {
                                // Fetch all session topics for the given session ID
                                $sessionAgendaIds = CollegeCouncil::where('session_id', $sessionId)
                                    ->whereNotNull('topic_id')->pluck('topic_id')->toArray();

                                foreach ($sessionAgendaIds as $agendaId) {
                                    $agendaNames = TopicAgenda::where('id', $agendaId)->value('name');
                                    $parts = explode(' : ', $agendaNames);
                                    $extractedAgendaName = explode(' / ', $parts[1]);

                                    $topicTitles[] = $extractedAgendaName[0];
                                    // $topicTitles[] = TopicAgenda::where('id', $agendaId)->value('name');
                                }

                                // Create associative array mapping topic IDs to their titles
                                $topics = array_combine($sessionAgendaIds, $topicTitles);

                                // dd($topics);
                                return ($topics);
                            })
                            ->native(false)
                            ->reactive(),

                        Forms\Components\Select::make('decision')
                            ->label(__('Take decision'))
                            ->options([
                                1 => __('Accepted'),
                                2 => __('Rejected'),
                                3 => __('Rejected with reason'),
                            ])
                            // ->inline()
                            ->native(false)
                            ->reactive()
                            // enable when choose headquarter
                            ->disabled(fn (Get $get, string $operation): bool => !filled($get('topic_id'))),


                        Forms\Components\Textarea::make('reject_reason')
                            ->translateLabel()
                            ->hidden(fn(Get $get): bool => !($get('decision') == 3)) // hidden if status is reject with reason
                            ->required()
                            ->columnSpanFull(),


                    ])
                    ->addable(false)
                    ->reorderable(false)
                    ->columnSpanFull()
                    ->columns(2)
                    ->defaultItems(function () use ($sessionId) {
                        $topics = count(CollegeCouncil::where('session_id', $sessionId)
                            // ->where('status', '!=', 0)
                            ->whereNotNull('topic_id')
                            ->pluck('topic_id'));
                        return $topics;
                    }),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Topics of department session council minute'))
            ->columns([
                Tables\Columns\TextColumn::make('Topic')
                    ->translateLabel()
                    ->alignment(Alignment::Center)
                    ->getStateUsing(function ($record) {
                        $sessionId = $record->id;

                        $topicIds = CollegeCouncil::where('session_id', $sessionId)
                            ->whereNotNull('topic_id')
                            ->pluck('topic_id');

                        foreach ($topicIds as $topicId) {
                            $agendaNames = TopicAgenda::where('id', $topicId)->value('name');
                            $parts = explode(' : ', $agendaNames);
                            $extractedAgendaName = explode(' / ', $parts[1]);

                            $topicTitles[] = $extractedAgendaName[0];
                            // $topicTitles[] = TopicAgenda::where('id', $topicId)->value('name');
                        }

                        return (implode('<br><br>', $topicTitles));
                        // dd($topicTitles);
                    })
                    ->html(),

                Tables\Columns\TextColumn::make('status')
                    ->translateLabel()
                    ->alignment(Alignment::Center)
                    ->getStateUsing(function ($record) {
                        $sessionId = $record->id;

                        // Fetch topic_ids with their statuses
                        $topicStatuses = CollegeCouncil::where('session_id', $sessionId)
                            ->whereNotNull('topic_id')
                            ->pluck('status', 'topic_id')
                            ->toArray();

                        // Map statuses to human-readable values
                        $statusLabels = [
                            0 => __('Pending'),
                            1 => __('Accepted'),
                            2 => __('Rejected'),
                            3 => __('Rejected with reason'),
                        ];

                        // Convert each status to its corresponding label
                        $statusTexts = array_map(function ($status) use ($statusLabels) {
                            return $statusLabels[$status] ?? 'unknown'; // Default to 'unknown' if status is not in the array
                        }, $topicStatuses);

                        return implode('<br><br>', $statusTexts);
                        // dd($statusTexts);
                    })
                    ->color(function ($record) {
                        $sessionId = $record->id;

                        $status = CollegeCouncil::where('session_id', $sessionId)
                            ->whereNotNull('topic_id')
                            ->pluck('status')
                            ->first();

                        return match ($status) {
                            0 => 'warning',
                            1 => 'success',
                            2 => 'danger',
                            3 => 'danger',
                            default => 'secondary',
                        };
                    })
                    ->html(),

                Tables\Columns\TextColumn::make('created_at')
                    ->translateLabel()
                    ->alignment(Alignment::Center)
                    ->getStateUsing(function ($record) {
                        $sessionId = $record->id;

                        $createdAtTimes = CollegeCouncil::where('session_id', $sessionId)
                            ->whereNotNull('topic_id')
                            ->pluck('created_at')->toArray();

                        return (implode('<br><br>', $createdAtTimes));
                    })
                    ->html()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->translateLabel()
                    ->alignment(Alignment::Center)
                    ->getStateUsing(function ($record) {
                        $sessionId = $record->id;

                        $updatedAtTimes = CollegeCouncil::where('session_id', $sessionId)
                            ->whereNotNull('topic_id')
                            ->pluck('updated_at')->toArray();

                        return (implode('<br><br>', $updatedAtTimes));
                    })
                    ->html()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->paginated(false) // disable the pagination
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading(__("Take decision"))
                    ->createAnother(false) // disableing create another btn
                    ->slideOver()
                    ->label(__("Take decision"))
                    ->hidden(function () {
                        $status = $this->getOwnerRecord()->status;

                        if ($status == 0 || $status == 4) {
                            return false;
                        } else { //hidden the button when taking decision (status) for all in same time
                            return true;
                        }
                    })
                    ->action(function (array $data): void {
                        $sessionId = $this->getOwnerRecord()->session_id;

                        $decisionData = $data['Decision'];

                        foreach ($decisionData as $decision) {
                            $topicId = $decision['topic_id'];
                            $status = $decision['decision'];

                            // Update the relevant CollegeCouncil records
                            CollegeCouncil::where('session_id', $sessionId)
                                ->whereNotNull('topic_id')
                                ->where('topic_id', $topicId)
                                ->update(['status' => (int) $status]);
                        }

                        // Update CollegeCouncil record to knowing the user about his request is has action now
                        CollegeCouncil::where('session_id', $sessionId)
                            ->whereNull('topic_id')
                            ->update(['status' => 4]);

                        Notification::make()
                            ->title(__('Decision saved successfully'))
                            ->success()
                            ->duration(3000)
                            ->send();
                    }),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
