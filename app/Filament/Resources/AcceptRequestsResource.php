<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AcceptRequestsResource\Pages;
use App\Filament\Resources\AcceptRequestsResource\RelationManagers;
use App\Models\AgandesTopicForm;
use App\Models\TopicAgenda;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use App\Models\Department_Council;
use Filament\Tables\Filters\SelectFilter;
use App\Models\Department;
use App\Models\FacultyCouncil;
use App\Models\Topic;
use Filament\Forms\Components\Textarea;

class AcceptRequestsResource extends Resource
{
    protected static ?string $model = TopicAgenda::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    public static function canViewAny(): bool
    {
        return auth()->user()->can('acceptRequests', TopicAgenda::class);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        // dd($table);
        return $table
            ->emptyStateHeading(__('No Requests need to accept yet')) // empty data message
            ->columns([
                Tables\Columns\TextColumn::make('#')
                    ->alignment(Alignment::Center)
                    ->label(__('Agenda Number'))
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('topic.title')
                    ->alignment(Alignment::Center)
                    ->label(__('Agenda Type'))
                    ->searchable()
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
                Tables\Columns\TextColumn::make('departement.faculty.' . self::getFacultyAndDepartmentName())
                    ->alignment(Alignment::Center)
                    ->label(__('Faculty'))
                    ->visible(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('departement.' . self::getFacultyAndDepartmentName())
                    ->alignment(Alignment::Center)
                    ->label(__('Department'))
                    ->visible(function () {
                        $userDepartmentsCount = Department_Council::where('user_id', auth()->id())->pluck('department_id')->count();
                        if (!auth()->user()->hasRole('Super Admin') && !auth()->user()->hasRole('System Admin') && $userDepartmentsCount == 1)
                            return false;
                        else
                            return true;
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('owner.name')
                    ->alignment(Alignment::Center)
                    ->label(__('Created by'))
                    ->searchable()
                    // ->hidden(auth()->user()->position_id == 1) // hidden if user position is Acadmic Staff
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->alignment(Alignment::Center)
                    ->label(__('Agenda Date'))
                    ->dateTime()
                    ->sortable()
                    ->searchable(),
                // ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->alignment(Alignment::Center)
                    ->translateLabel()
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordUrl(function ($record) {
                return null;
            }) // disable opening edit mode when click on row
            ->filters([
                SelectFilter::make('department_id')
                    ->label(__('Department'))
                    ->options(function () {
                        if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin')) {
                            return Department::pluck(self::getFacultyAndDepartmentName(), 'id');
                        } else {
                            $userDepartments = Department_Council::where('department__councils.user_id', auth()->id())
                                ->join('departments', 'departments.id', '=', 'department__councils.department_id')
                                ->select(
                                    'departments.' . self::getFacultyAndDepartmentName() . ' as department_name',
                                    'departments.id as department_id',
                                );

                            return $userDepartments->pluck('department_name', 'department_id');
                        }
                    })
                    ->visible(function () {
                        $userDepartmentsCount = Department_Council::where('user_id', auth()->id())->pluck('department_id')->count();
                        if (!auth()->user()->hasRole('Super Admin') && !auth()->user()->hasRole('System Admin') && $userDepartmentsCount == 1)
                            return false;
                        else
                            return true;
                    })
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Accept')
                    ->color('success')
                    ->translateLabel()
                    // visable when user position is head of department & status pending and he is not the uploader of agenda
                    ->visible(fn($record) => (auth()->user()->position_id == 3 || auth()->user()->position_id == 5) && $record->status == 0 /*&& auth()->id() != $record->created_by*/)
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
                    ->visible(fn($record) => (auth()->user()->position_id == 3 || auth()->user()->position_id == 5) && $record->status == 0 /*&& auth()->id() != $record->created_by*/)
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
                Tables\Actions\ViewAction::make()
                    ->label(__('Details')),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('Accept all')
                        ->color('success')
                        ->translateLabel()
                        ->icon('heroicon-o-check')
                        ->action(function (EloquentCollection $records): void {
                            foreach ($records as $record) {
                                $agenda = TopicAgenda::findOrFail($record->id);

                                $agenda->update([
                                    'status' => 1,
                                    'updates' => 1 //accept from head of department
                                ]);

                                // sending notification for which create the
                                Notification::make()
                                    ->title('تم الموافقة على الطلب')
                                    ->body('كود الطلب: ' . $agenda->code)
                                    ->sendToDatabase(User::where('id', $agenda->created_by)->get());
                            }

                            // send success alert
                            Notification::make()
                                ->title('تم الموافقة على كل الطلبات المحددة')
                                ->color('success')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('Reject all')
                        ->color('danger')
                        ->translateLabel()
                        ->icon('heroicon-o-x-mark')
                        ->form(function (EloquentCollection $records) {
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
                        ->action(function (EloquentCollection $records, array $data): void {
                            foreach ($records as $record) {
                                $agenda = TopicAgenda::findOrFail($record->id);

                                $agenda->update([
                                    'status' => 2,
                                    'updates' => 2, //reject from head of department
                                    'note' => $data['reject_reason']
                                ]);

                                // sending notification for which create the
                                Notification::make()
                                    ->title('تم رفض الطلب')
                                    ->body('كود الطلب: ' . $agenda->code . ' ' . 'سبب الرفض: ' . $data['reject_reason'])
                                    ->sendToDatabase(User::where('id', $agenda->created_by)->get());
                            }

                            // send success alert
                            Notification::make()
                                ->title('تم رفض كل الطلبات المحددة')
                                ->color('success')
                                ->success()
                                ->send();
                        })
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->query(function (TopicAgenda $query) {

                $departmentCouncilId = DB::table('department__councils')
                    ->where('user_id', auth()->user()->id)
                    ->pluck('department_id')->toArray();

                if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin')) {
                    // If the user has the role of Super Admin or System Admin, show all sessions
                    return $query->where('status', 0);
                }
                if (in_array(auth()->user()->position_id, [3])) {
                    $query = TopicAgenda::where('status', 0)
                        ->whereIn('department_id', $departmentCouncilId)
                        ->whereIn('classification_reference', [1, 2]);
                    return $query;
                }
                if (in_array(auth()->user()->position_id, [5])) {

                    $facultyDean = FacultyCouncil::where('position_id', 5)->where('user_id', auth()->user()->id)->latest()->first();
                    $query = TopicAgenda::where('status', 0)
                        ->where('classification_reference', 3)
                        ->where('faculty_id', $facultyDean->faculty_id);

                    return $query;
                } else {
                    abort(403);
                }
            });
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAcceptRequests::route('/'),
            'create' => Pages\CreateAcceptRequests::route('/create'),
            'edit' => Pages\EditAcceptRequests::route('/{record}/edit'),
            'view' => Pages\ViewAcceptRequests::route('/{record}'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Topics Management');
    }

    public static function getLabel(): ?string
    {
        return __('Accept Request');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Accept Requests');
    }
    private static function getFacultyAndDepartmentName()
    {
        return app()->getLocale() == 'en' ? 'en_name' : 'ar_name';
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
