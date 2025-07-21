<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TopicResource\Pages;
use App\Filament\Resources\TopicResource\RelationManagers;
use App\Models\Axis;
use App\Models\ControlReport;
use App\Models\ControlReportFaculty;
use App\Models\Input;
use App\Models\Topic;
use App\Models\TopicAxis;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class TopicResource extends Resource
{
    protected static ?string $model = Topic::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    // protected static ?int $navigationSort = 5;

    protected static int $globalSearchResultsLimit = 5;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => __('unique validation'),
                        'required' => __('required validation'),
                    ])
                    ->translateLabel()
                    ->maxLength(255),
                Forms\Components\Select::make('main_topic_id')
                    ->translateLabel()
                    ->relationship('mainTopic', 'title')
                    ->hidden(
                        fn(Topic $topic, string $operation): bool => ($operation == 'edit' && !($topic->main_topic_id))
                    )
                    ->live(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('#')
                    ->alignment(Alignment::Center)
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('code')
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->translateLabel()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('order')
                //     ->alignment(Alignment::Center)
                //     ->searchable()
                //     ->translateLabel()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->translateLabel()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->alignment(Alignment::Center)
                    ->badge()
                    ->translateLabel()
                    // ->getStateUsing(fn ($record) => (!$record->main_topic_id) ? 'Main Topic' : 'Sub Topic')->color(fn (string $state): string => match ($state) {
                    //     'Main Topic' => 'info',
                    //     'Sub Topic' => 'success',
                    // }),
                    ->getStateUsing(function ($record) {
                        if (!$record->main_topic_id) {
                            return __('Main Topic');
                        } else {
                            return __('Sub Topic');
                        }
                    })
                    ->color(function ($record) {
                        if (!$record->main_topic_id) {
                            return 'success';
                        } else {
                            return 'info';
                        }
                    }),
                Tables\Columns\TextColumn::make('mainTopic.title')
                    ->alignment(Alignment::Center)
                    ->numeric()
                    ->translateLabel()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->alignment(Alignment::Center)
                    ->dateTime()
                    ->translateLabel()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->alignment(Alignment::Center)
                    ->dateTime()
                    ->translateLabel()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // ->defaultSort('created_at', 'desc')
            ->defaultSort(
                fn(Builder $query) => $query
                    ->orderByRaw('IFNULL(main_topic_id, id) DESC') // Then, order by id, keeping subtopics under their main topics
                    ->orderByRaw('main_topic_id IS NULL DESC')  // First, order by whether it's a main topic or a subtopic (main topics first)
            )
            ->filters([
                Tables\Filters\TernaryFilter::make('main_topic_id')
                    ->label(__('Status'))
                    ->placeholder(__('All Topics'))
                    ->trueLabel(__('Main Topic'))
                    ->falseLabel(__('Sub Topic'))
                    ->queries(
                        true: fn(Builder $query) => $query->whereNull('main_topic_id'),
                        false: fn(Builder $query) => $query->whereNotNull('main_topic_id'),
                        blank: fn(Builder $query) => $query, // In this example, we do not want to filter the query when it is blank.
                    )
            ])
            ->query(function (Topic $query) {
                return $query;
            })
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, Topic $record) {
                        // Directly check if related data exists in topics_agendas table
                        $count = DB::table('topics_agendas')
                            ->where('topic_id', $record->id)
                            ->count();
                        $count2 = DB::table('topics')
                            ->where('main_topic_id', $record->id)
                            ->count();
                        // Check if $record exists and if there is related data
                        if ($record->exists && ($count > 0 || $count2 > 0)) {
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
                    }),
                Tables\Actions\Action::make('Clone Data')
                    ->label(function ($record) {
                        return __('Clone');
                    })
                    ->color('warning')
                    ->icon('heroicon-o-play')
                    ->action(function ($record) {
                        $topic = Topic::find($record->id);
                        $existingReportData = ControlReport::where('topic_id', $record->id)->first();
                        $existingReportFacultyData = ControlReportFaculty::where('topic_id', $record->id)->first();

                        $titleSuffix = 'مكرر';

                        do {
                            // Generate a random number
                            $randomNumber = rand(1000, 9999); // You can adjust the range as needed

                            // Concatenate the title with the suffix and random number
                            $title = $topic->title . ' ' . $titleSuffix . ' ' . $randomNumber;

                            // Check if the title already exists in the database
                            $titleExists = Topic::where('title', $title)->exists();
                        } while ($titleExists);

                        $latestRecord = Topic::latest('id')->first();
                        $latestCode = $latestRecord->code ?? 'tpc_0';
                        $latestNumber = intval(preg_replace('/[^0-9]+/', '', $latestCode));
                        $newNumber = $latestNumber + 1;
                        $newCode = 'tpc_' . $newNumber;
                        $latestOrder = intval($latestRecord->order ?? '0');
                        $newOrder = $latestOrder + 1;

                        if (is_null($topic->main_topic_id)) {
                            $newTopic = new Topic();
                            $newTopic->title = $title;
                            $newTopic->code = $newCode;
                            $newTopic->order = $newOrder;
                            $newTopic->classification_reference = $topic->classification_reference;
                            $newTopic->save();
                        } else {
                            $formData = TopicAxis::where('topic_id', $record->id)->get('field_data'); // JSON string
                            $AxisId = TopicAxis::where('topic_id', $record->id)->first()->axis_id;
                            $mainTopicId = $topic->main_topic_id;

                            $newTopic = new Topic();
                            $newTopic->title = $title;
                            $newTopic->code = $newCode;
                            $newTopic->order = $newOrder;
                            $newTopic->main_topic_id = $mainTopicId;
                            $newTopic->classification_reference = $topic->classification_reference;
                            $newTopic->escalation_authority = $topic->escalation_authority;
                            $newTopic->decisions = $topic->decisions;
                            $newTopic->save();

                            if ($formData) {
                                $decodedFormData = json_decode($formData, true);
                                if (is_array($decodedFormData) && !empty($decodedFormData)) {
                                    foreach ($decodedFormData as $axisId => $content) {
                                        if (!empty($content)) {
                                            $axis = Axis::find($AxisId);
                                            if ($axis) {
                                                // Attach axis with JSON encoded field_data
                                                $newTopic->axes()->attach($AxisId, ['field_data' => $content['field_data']]);
                                            }
                                        }
                                    }
                                }
                            }



                            if ($existingReportData) {
                                ControlReport::create([
                                    'topic_id' => $newTopic->id,
                                    'content' => $existingReportData->content,
                                    'topic_formate' => $existingReportData->topic_formate
                                ]);
                            }
                            if ($existingReportFacultyData) {
                                ControlReportFaculty::create([
                                    'topic_id' => $newTopic->id,
                                    'content' => $existingReportFacultyData->content,
                                    'topic_formate' => $existingReportFacultyData->topic_formate

                                ]);
                            }
                        }

                        Notification::make()
                            ->success()
                            ->color('success')
                            ->title(__('Classify has been copied successfully'))
                            ->body(__('Classify title') . ' => ' . $title)
                            ->seconds(10)
                            ->send();
                    }),


            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListTopics::route('/'),
            'create' => Pages\CreateTopic::route('/create'),
            'edit' => Pages\EditTopic::route('/{record}/edit'),
            'coverLetter' => Pages\CoverLetter::route('/{record}/CoverLeter'),
            // 'attandence-list' => Pages\cloneTopic::route('/{recordId}/attandence-list'),

            // 'clone-Topic' => Pages\cloneTopic::route('/{recordId}/clone-Topic')
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Topics Management');
    }

    public static function getLabel(): ?string
    {
        return __('Classification');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Classifications');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'عنوان الموضوع' => $record->title,
            'تصنيف الموضوع' => $record->main_topic_id ? 'فرعي' : "رئيسي",
        ];
    }
}
