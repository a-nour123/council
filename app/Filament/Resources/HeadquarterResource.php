<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeadquarterResource\Pages;
use App\Filament\Resources\HeadquarterResource\RelationManagers;
use App\Models\Faculty;
use App\Models\Headquarter;
use App\Models\FacultyHeadquarter;
use Filament\Actions\Modal\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HeadquarterResource extends Resource
{
    protected static ?string $model = Headquarter::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?int $navigationSort = 2;


    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->translateLabel()
                    ->unique(ignoreRecord: true) // make field unique but accept current value on update
                    ->required()
                    ->rules(['regex:/^(?=.*[\p{L}\p{Arabic}])[\p{L}\p{Arabic}0-9\s!@#$%^&*()-_=+`~]+$/u'])
                    ->validationMessages([
                        'regex' => __('regex validation'),
                        'unique' => __('unique validation'),
                        'required' => __('required validation'),
                    ])
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->translateLabel()
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => __('unique validation'),
                        'required' => __('required validation'),
                    ])
                    ->maxLength(25),
                Forms\Components\Textarea::make('address')
                    ->translateLabel()
                    ->required()
                    ->validationMessages([
                        'required' => __('required validation'),
                    ])
                    ->maxLength(65535)
                    ->columnSpanFull(),
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
                    ->translateLabel()
                    ->searchable()
                    ->alignment(Alignment::Center)
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->translateLabel()
                    ->searchable()
                    ->alignment(Alignment::Center)
                    ->sortable(),
                Tables\Columns\TextColumn::make('address')
                    ->translateLabel()
                    ->searchable()
                    ->alignment(Alignment::Center)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->translateLabel()
                    ->dateTime()
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->translateLabel()
                    ->dateTime()
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                //putting condition to stop delete record if found related data
                Tables\Actions\DeleteAction::make()
                // ->before(function (Tables\Actions\DeleteAction $action, Headquarter $record) {
                //     if ($record->faculties()->exists()) {
                //         Notification::make()
                //             ->danger()
                //             ->color('danger')
                //             ->title(__('Failed to delete'))
                //             ->body(__('Headquarter contains on faculties related'))
                //             ->seconds(10)
                //             ->send();

                //         // This will halt and cancel the delete action modal.
                //         $action->cancel();
                //     }
                // }),
            ])
            ->recordUrl(function ($record) {
                return null;
            }) // disable opening edit mode whenr click on row
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        $headquarter_id = $infolist->record->id;

        $facultyIds = FacultyHeadquarter::where('headquarter_id', $headquarter_id)->pluck('faculty_id')->toArray();

        $faculty_EnName = array_map(function ($facultyId) {
            $enName = Faculty::where('id', $facultyId)->value('en_name');
            return $enName;
        }, $facultyIds);

        $faculty_ArName = array_map(function ($facultyId) {
            $arName = Faculty::where('id', $facultyId)->value('ar_name');
            return $arName;
        }, $facultyIds);

        $infolist->record['faculty_EnName'] = $faculty_EnName;
        $infolist->record['faculty_ArName'] = $faculty_ArName;

        return $infolist
            ->schema([
                Components\Section::make()
                    ->schema([
                        Components\TextEntry::make('name')
                            ->translateLabel(),
                        Components\TextEntry::make('address')
                            ->translateLabel(),
                        Components\TextEntry::make('created_at')
                            ->translateLabel(),
                        Components\TextEntry::make('updated_at')
                            ->translateLabel(),
                    ])->columns(2),

                Components\Section::make('Faculties')
                    ->heading(__('Faculties'))
                    ->schema([
                        Components\TextEntry::make('faculty_EnName')
                            ->label(__('English Name'))
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->expandableLimitedList(),

                        Components\TextEntry::make('faculty_ArName')
                            ->label(__('Arabic Name'))
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->expandableLimitedList(),

                    ])->translateLabel()->columns(2)->collapsed()
                    // make this section hidden where headquarter doesn't containing faculties related
                    ->hidden(fn(Headquarter $headquarter): bool => (!$headquarter->faculties()->exists()))
            ]);
    }

    // public static function getRelations(): array
    // {
    //     return [
    //         RelationManagers\FacultiesRelationManager::class,
    //     ];
    // }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHeadquarters::route('/'),
            'create' => Pages\CreateHeadquarter::route('/create'),
            'edit' => Pages\EditHeadquarter::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        return __('Headquarter');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Headquarters');
    }
}
