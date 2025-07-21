<?php

namespace App\Filament\Resources;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Filament\Resources\SubmitTopicResource\Pages;
use App\Models\Agenda;
use App\Models\Axis;
use App\Models\Department;
use App\Models\Topic;
use App\Models\TopicAxis;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Notifications\Collection;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\RepeatableEntry;
use App\Filament\Resources\SubmitTopicResource\RelationManagers;
use App\Models\AgandesTopicForm;
use App\Models\Answer;
use App\Models\Faculty;
use App\Models\SessionDecision;
use App\Models\SessionTopic;
use App\Models\Shop\Order;
use App\Models\TopicAgenda;
use App\Models\TopicAxisInput;
use App\Models\TopicAxisInputOption;
use App\Models\YearlyCalendar;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\IconEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Tables\Actions\MenuItem;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;

class SubmitTopicResource extends Resource
{
    protected static ?string $model = TopicAgenda::class; //(`?` is a nullable type hint).

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';


    /**
     * Builds a form with a wizard that guides the user through selecting a main topic, subtopic, and main axes.
     *
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    /*
    This function is used to create a table with the given $table object
    */
    public static function table(Table $table): Table
    {
        // Set the columns for the table
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('#')
                    ->alignment(Alignment::Center)
                    ->label(__('Agenda Number'))
                    ->rowIndex(),
                // Tables\Columns\TextColumn::make('topic.code')
                //     ->alignment(Alignment::Center)
                //     ->label(__('Code'))
                //     ->translateLabel()
                //     ->searchable()
                //     ->sortable(),
                // Tables\Columns\TextColumn::make('topic.title')
                Tables\Columns\TextColumn::make('topic.title')
                    ->alignment(Alignment::Center)
                    ->label(__('Agenda Type'))
                    ->searchable()
                    ->limit(30)
                    ->sortable(),
                Tables\Columns\TextColumn::make('')
                    ->alignment(Alignment::Center)
                    ->label(__('Title'))
                    ->getStateUsing(function ($record) {
                        // dd(self::initializeTopicsWithoutDecision($record->id));
                        return self::initializeTopicsWithoutDecision($record->id);
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('academic_year.name')
                    ->alignment(Alignment::Center)
                    ->label(__('Academic year'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('owner.name')
                    ->alignment(Alignment::Center)
                    ->label(__('Created by'))
                    // ->searchable()
                    ->hidden(auth()->user()->position_id == 1) // hidden if user position is Acadmic Staff
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->alignment(Alignment::Center)
                    ->label(__('Status'))
                    // Only show this column if user postion head of department, dean of college or with role super or system admin
                    // ->visible(in_array(auth()->user()->position_id, [3, 5]) || auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin'))
                    ->searchable()
                    ->sortable()
                    // Add a badge to the column
                    ->badge()
                    // Get the state of the column using a closure
                    ->getStateUsing(fn($record) => match ($record->status) {
                        0 => __('Pending'),
                        1 => __('Accepted'),
                        2 => __('Rejected'),
                    })
                    // Set the color of the badge based on the status
                    ->color(fn($record) => match ($record->status) {
                        0 => 'warning',
                        1 => 'success',
                        2 => 'danger',
                    }),
                // ->hidden(auth()->user()->position_id == 1), // hidden if user position is Acadmic Staff
                Tables\Columns\TextColumn::make('created_at')
                    ->alignment(Alignment::Center)
                    ->label(__('Agenda Date'))
                    // ->dateTime()
                    ->getStateUsing(function ($record) {
                        $date = $record->created_at->format('d-m-Y');
                        $higriDate = Hijri::DateIndicDigits('d-m-Y', $date);

                        $lastDate = $higriDate . ' / ' . $date;
                        return $lastDate;
                    })
                    ->sortable()
                    ->searchable(),
                // ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->alignment(Alignment::Center)
                    ->translateLabel()
                    // ->dateTime()
                    ->getStateUsing(function ($record) {
                        $date = $record->updated_at->format('d-m-Y');
                        $higriDate = Hijri::DateIndicDigits('d-m-Y', $date);

                        $lastDate = $higriDate . ' / ' . $date;
                        return $lastDate;
                    })
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('yearly_calendar_id')
                    ->label(__('Academic year'))
                    ->options(YearlyCalendar::pluck('name', 'id')),
                SelectFilter::make('status')
                    ->translateLabel()
                    ->options([
                        '0' => __('Pending'),
                        '1' => __('Accepted'),
                        '2' => __('Rejected')
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('Accept')
                    ->color('success')
                    ->translateLabel()
                    // visable when user position is head of department & status pending and he is not the uploader of agenda
                    ->visible(fn($record) => auth()->user()->position_id == 3 && $record->status == 0 && auth()->id() != $record->created_by && $record->classification_reference != 3)
                    ->icon('heroicon-o-check')
                    ->action(action: function ($record): void {
                        $agenda = TopicAgenda::findOrFail($record->id);
                        $agenda->update([
                            'status' => 1,
                            'updates' => 1 // accepted from head_of_dep
                        ]);

                        // send success alert
                        Notification::make()
                            ->title('تم الموافقة على الطلب')
                            ->color('success')
                            ->success()
                            ->send();

                        // sending notification for which create the
                        Notification::make()
                            ->title('تم الموافقة على الطلب')
                            ->body('كود الطلب: ' . $agenda->code)
                            ->sendToDatabase(User::where('id', $agenda->created_by)->get());
                    }),
                Tables\Actions\Action::make('Reject')
                    ->color('danger')
                    ->translateLabel()
                    // visable when user position is head of department & status pending and he is not the uploader of agenda
                    ->visible(fn($record) => auth()->user()->position_id == 3 && $record->status == 0 && auth()->id() != $record->created_by && $record->classification_reference != 3)
                    ->icon('heroicon-o-x-mark')
                    ->form(function (TopicAgenda $agenda, $record) {
                        return [
                            Textarea::make('reject_reason')
                                ->translateLabel()
                                ->required()
                                ->placeholder(__('Enter rejection reason here'))
                                ->validationMessages([
                                    'required' => __('required validation'),
                                ]),
                        ];
                    })
                    ->action(function (array $data, $record): void {
                        $agenda = TopicAgenda::findOrFail($record->id);

                        $agenda->update([
                            'status' => 2,
                            'updates' => 2, // rejected from head_of_dep
                            'note' => $data['reject_reason']
                        ]);

                        // send success alert
                        Notification::make()
                            ->title('تم رفض الطلب')
                            ->color('success')
                            ->success()
                            ->send();

                        // sending notification for which create the
                        Notification::make()
                            ->title('تم رفض الطلب')
                            ->body('كود الطلب: ' . $agenda->code . ' ' . 'سبب الرفض: ' . $data['reject_reason'])
                            ->sendToDatabase(User::where('id', $agenda->created_by)->get());
                    }),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->color('primary')
                        // ->visible(fn($record) => $record->status === 0 || $record->status === 3),
                        ->visible(fn($record) => $record->status === 0 && auth()->id() == $record->created_by),
                    Tables\Actions\ViewAction::make()->label(__('Details')),
                    Tables\Actions\Action::make('View agenda')
                        ->translateLabel()
                        ->color('success')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->action(function ($record) {
                            $appURL = env('APP_URL');

                            $url = $appURL . '/admin/submit-topics/' . $record->id . '/agenda-details';

                            return redirect()->away($url);
                        }),

                    Tables\Actions\Action::make('Coverletter')
                        ->translateLabel()
                        ->color('info')
                        ->icon('heroicon-o-clipboard-document-list')
                        ->action(function ($record) {
                            $ExistSessionForThisRecord = SessionTopic::where('topic_agenda_id', $record->id)->first();

                            // Check if $ExistSessionForThisRecord exists before accessing session_id
                            if ($ExistSessionForThisRecord) {

                                $appURL = env('APP_URL');
                                $url = $appURL . '/admin/submit-topics/' . $record->id . '/cover-letters-details';

                                return redirect()->away($url);
                            }
                        })
                        ->visible(function ($record) {
                            $ExistSessionForThisRecord = SessionTopic::where('topic_agenda_id', $record->id)->first();

                            // Ensure $ExistSessionForThisRecord exists before accessing session_id
                            if ($ExistSessionForThisRecord) {
                                $hasDessionApprovel = SessionDecision::where('agenda_id', $record->id)
                                    ->where('session_id', $ExistSessionForThisRecord->session_id)
                                    ->first();

                                return $hasDessionApprovel && $hasDessionApprovel->approval == 1;
                            }

                            return false; // Hide the action if $ExistSessionForThisRecord is null
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->before(function (Tables\Actions\DeleteAction $action, TopicAgenda  $record) {
                            // Directly check if related data exists in topics_agendas table
                            $count = DB::table('session_topics')
                                ->where('topic_agenda_id', $record->id)
                                ->count();
                            // Check if there are related records in the pivot table
                            $count2 = DB::table('agandes_topic_form')
                                ->where('agenda_id', $record->id)
                                ->count();

                            // If there are related records, detach them first
                            if ($count == 0) {
                                if ($count2 > 0) {
                                    $record->topics()->detach(); // Assuming 'topics' is the relationship method
                                }
                            }

                            // Check if $record exists and if there is related data
                            if ($record->exists && ($count > 0)) {
                                Notification::make()
                                    ->danger()
                                    ->color('danger')
                                    ->title(__('Failed to delete'))
                                    ->body(__('Topic contains related data'))
                                    ->seconds(10)
                                    ->send();

                                // This will halt and cancel the delete action modal.
                                $action->cancel();
                            }
                        })
                        // ->visible(fn($record) => $record->status === 0),
                        ->visible(fn($record) => $record->status === 0 && auth()->id() == $record->created_by),
                ]),

            ])
            ->recordUrl(function ($record) {
                return null;
            }) // disable opening edit mode when click on row
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //
                ])

            ])

            // Filter the data to only include records where the faculty_id matches the current user's faculty_id
            ->query(function (TopicAgenda $query) {

                // // Get the department council record for the current user
                // $departmentCouncil = DB::table('department__councils')
                //     ->where('user_id', auth()->user()->id)
                //     ->get()->first();

                // // Handle the case where no department council record is found
                // if (is_null($departmentCouncil)) {
                //     if (auth()->user()->name != 'Super Admin' && auth()->user()->name != 'System Admin') {

                //         // Return no records by setting an impossible condition
                //         return $query->whereRaw('1 = 0');
                //     }
                //     // If the user is a Super Admin or System Admin, return the original query
                //     return $query;
                // }

                // // Check if the user is not a Super Admin or System Admin
                // if (auth()->user()->name != 'Super Admin' && auth()->user()->name != 'System Admin') {
                //     if ($departmentCouncil->department_id != null && auth()->user()->position_id != 3) {
                //         // dd('!');
                //         return $query->where('department_id', $departmentCouncil->department_id)->where('created_by', auth()->user()->id);
                //     } else {
                //         return $query->where('faculty_id', auth()->user()->faculty_id);
                //     }
                // }
                // // If the user is a Super Admin or System Admin, return the original query
                // return $query;
                $departmentCouncilId = DB::table('department__councils')
                    ->where('user_id', auth()->user()->id)
                    ->pluck('department_id')->toArray();

                if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin')) {
                    // If the user has the role of Super Admin or System Admin, show all sessions
                    return $query;
                }
                // if (in_array(auth()->user()->position_id, [2, 3])) {
                //     $query = TopicAgenda::whereIn('department_id', $departmentCouncilId);
                //     return $query;
                // }
                else {
                    $query = TopicAgenda::where('created_by', auth()->user()->id);
                    return $query;
                }
            });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /*
    The function ensures that the query is scoped to the current user's faculty
    */
    public static function query(): Builder
    {
        return Agenda::query()->where('faculty_id', auth()->user()->faculty_id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubmitTopics::route('/'),
            'create' => Pages\CreateSubmitTopic::route('/create'),
            'edit' => Pages\EditSubmitTopic::route('/{record}/edit'),
            // 'view' => Pages\ViewSubmitTopic::route('/{record}'),
            'agenda-details' => Pages\RequestDetails::route('/{recordId}/agenda-details'),
            'cover-letters-details' => Pages\CoverLetterAgenda::route('/{recordId}/cover-letters-details'),

        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        $department_id = $infolist->record->department_id;
        $faculty_id = $infolist->record->faculty_id;
        $user_id = $infolist->record->created_by;

        $faculty_ArName = Faculty::where('id', $faculty_id)->pluck('ar_name');
        $departmentName = Department::where('id', $department_id)->pluck('ar_name');
        $userName = User::where('id', $user_id)->pluck('name');

        $infolist->record['faculty_ar_name'] = $faculty_ArName;
        $infolist->record['department_ar_name'] = $departmentName;
        $infolist->record['user_name'] = $userName;
        return $infolist
            ->schema([
                TextEntry::make('code')
                    ->label(__('Code')),
                // TextEntry::make('topic.title')
                TextEntry::make('name')
                    ->translateLabel(),
                TextEntry::make('faculty_ar_name')
                    ->label(__('Faculty')),
                TextEntry::make('department_ar_name')
                    ->label(__('Department')),
                TextEntry::make('user_name')
                    ->label(__('Created by')),
                TextEntry::make('status')
                    ->translateLabel()
                    // Only show this column if user postion head of department, dean of college or with role super or system admin
                    ->visible(in_array(auth()->user()->position_id, [3, 5]) || auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin'))
                    // ->searchable()
                    // ->sortable()
                    ->badge()
                    ->getStateUsing(fn($record) => match ($record->status) {
                        0 => __('Pending'),
                        1 => __('Accepted'),
                        2 => __('Rejected'),
                    })
                    // Set the color of the badge based on the status
                    ->color(fn($record) => match ($record->status) {
                        0 => 'warning',
                        1 => 'success',
                        2 => 'danger',
                    }),
                TextEntry::make('updates')
                    ->label(__('Request updates'))
                    ->badge()
                    ->getStateUsing(fn($record) => match ($record->updates) {
                        0 => __('Pending'),
                        1 => __('Request has been accepted'),
                        2 => __('Request has been rejected'),
                        3 => __('Accepted from department council'),
                        4 => __('Rejected from department council'),
                        5 => __('Accepted from faculty council'),
                        6 => __('Rejected from faculty council'),
                    })
                    // Set the color of the badge based on the updates
                    ->color(fn($record) => match ($record->updates) {
                        0 => 'warning',
                        1 => 'success',
                        2 => 'danger',
                        3 => 'success',
                        4 => 'danger',
                        5 => 'success',
                        6 => 'danger',
                    }),
                TextEntry::make('note')
                    ->label(__('Rejected reason'))
                    ->color('danger')
                    ->hidden(fn($record) => $record->status != 2),
                TextEntry::make('created_at')
                    ->translateLabel()
                    // ->dateTime()
                    ->getStateUsing(function ($record) {
                        $date = $record->created_at->format('d-m-Y');
                        $higriDate = Hijri::DateIndicDigits('d-m-Y', $date);

                        $lastDate = $higriDate . ' / ' . $date;
                        return $lastDate;
                    }),
            ])
            ->columns(null)
            ->inlineLabel();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Topics Management');
    }

    public static function getLabel(): ?string
    {
        return __('Agenda');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Self Agendas');
    }

    protected function getRedirectUrl(): ?string
    {
        return static::getResource()::getUrl('index');
    }

    public static function initializeTopicsWithoutDecision($agendaId): array
    {
        // $topicFormate = SessionTopic::where('session_topics.session_id', $session->id)
        $topicFormate = TopicAgenda::where('topics_agendas.id', $agendaId)
            ->join('topics as sub_topic', 'sub_topic.id', '=', 'topics_agendas.topic_id')
            ->join('control_reports as report', 'report.topic_id', '=', 'sub_topic.id')
            ->join('topics as main_topic', 'main_topic.id', '=', 'sub_topic.main_topic_id')
            ->orderBy('sub_topic.main_topic_id', 'asc') // Order by the `main_topic_id`
            ->select(
                'topics_agendas.id as agenda_id',      // Agenda ID
                'sub_topic.id as topic_id',           // Sub-topic ID
                'sub_topic.title as topic_title',     // Sub-topic Title
                'main_topic.title as main_topic',     // Main-topic Title
                'report.topic_formate'        // Topic format (if exists in `topics_agendas`)
            )
            ->get();

        // Map through all topics and use agenda_id as the key
        $formattedTopics = $topicFormate->mapWithKeys(function ($topic) {
            if (!is_null($topic->topic_formate) && $topic->topic_formate != "<p><br></p>") {
                // Pass individual topic, not grouped
                $replacements = self::getTopicReplacements($topic, $topic->topic_formate);

                // Replace the placeholders with actual values
                $content = self::replacePlaceholders($topic->topic_formate, $replacements);
                $value = strip_tags($content);
            } else {
                $value = $topic->topic_title;
            }

            // Use agenda_id as the key
            return [$topic->agenda_id => $value];
        })->toArray();

        return $formattedTopics;
    }
    public static function replacePlaceholders($content, $replacements)
    {
        foreach ($replacements as $key => $value) {
            $content = str_replace($key, $value, $content);
        }
        return $content;
    }
    public static function getTopicReplacements($topicData, $reportTemplate)
    {
        $agenda = TopicAgenda::findOrFail($topicData->agenda_id);
        $userId = TopicAgenda::where('id', $topicData->agenda_id)->value('created_by');
        $topicTitle = Topic::where('id', $topicData->topic_id)->value('title');
        $topicIds = is_array($topicData->topic_id) ? $topicData->topic_id : [$topicData->topic_id];
        $username = User::where('id', $userId)->value('name');

        // Fetch content and ensure it is properly formatted as an array
        $topicagendacontentform = AgandesTopicForm::where('agenda_id', $topicData->agenda_id)
            ->whereIn('topic_id', $topicIds)
            ->pluck('content')
            ->toArray();

        // Combine all content into a single array of decoded JSON objects
        $decodedContents = [];
        foreach ($topicagendacontentform as $jsonString) {
            // Check if the element is a string and contains JSON
            if (is_string($jsonString)) {
                $decoded = json_decode($jsonString, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $decodedContents = array_merge($decodedContents, $decoded);
                } else {
                    // Log or handle invalid JSON
                    return ['error' => 'Invalid JSON content found.'];
                }
            } elseif (is_array($jsonString)) {
                // If it's already an array, just merge it
                $decodedContents = array_merge($decodedContents, $jsonString);
            } else {
                // Handle the case where $jsonString is neither a string nor an array
                return ['error' => 'Unexpected data type encountered.'];
            }
        }


        // Extract all placeholders within curly braces
        preg_match_all('/\{(.*?)\}/', $reportTemplate, $matches);

        $placeholders = $matches[1];


        // Initialize the replacements array
        $replacements = [
            // '{session_number}' => $session->code,
            '{department_name}' => $agenda->departement->ar_name,
            '{faculty_name}' => $agenda->departement->faculty->ar_name,
            '{name_of_topic}' => $topicTitle ?? '',
            // '{deescion_number}' => $decision->order ?? '',
            // '{vote}' => $this->getDecisionStatusMap()[$decision->decision_status] ?? 'حالة غير معروفة',
            // '{vote_type}' => $this->getDecisionTypeStatusMap()[$decision->decision_status] ?? 'حالة غير معروفة',
            // '{justification}' => $decision->decision ?? '',
            // '{decision}' => $decision->decisionChoice ?? '',
            '{uploader}' => $username,
        ];

        // Check if $decodedContents is an array before looping
        if (is_array($decodedContents)) {

            // Search in the decoded content for each placeholder and add it to the replacements
            foreach ($placeholders as $placeholder) {
                foreach ($placeholders as $placeholder) {
                    foreach ($decodedContents as $formField) {

                        $selectableTypes = ['select', 'checkbox-group', 'radio-group'];

                        if (in_array($formField['type'], $selectableTypes)) {
                            $values = $formField['values'];
                            $selectedLabels = [];

                            foreach ($values as $ty) {
                                if (isset($ty['selected']) && $ty['selected'] === true) {
                                    // Collect selected labels
                                    $selectedLabels[] = $ty['label'] ?? '';
                                }
                            }

                            // Implode selected labels into a single string, separated by commas
                            $formField['value'] = implode(', ', $selectedLabels);

                            // Make sure 'label' is set, if not, use the existing label
                            $formField['label'] = $formField['label'] ?? '';

                            if (isset($formField['label']) && $formField['label'] === $placeholder) {
                                // Set the replacement value with the imploded selected values
                                $replacements['{' . $placeholder . '}'] = $formField['value'] ?? '';
                            }
                        } else {
                            if (isset($formField['label']) && $formField['label'] === $placeholder) {
                                $replacements['{' . $placeholder . '}'] = $formField['value'] ?? '';
                                break;
                            }
                        }
                    }
                }
            }
        } else {
            $replacements['error'] = 'Decoded content is not an array.';
        }

        return $replacements;
    }
}
