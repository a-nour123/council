<?php

namespace App\Filament\Resources\HeadquarterResource\RelationManagers;

use App\Models\Department;
use App\Models\Faculty;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Unique;

class FacultiesRelationManager extends RelationManager
{
    protected static string $relationship = 'faculties';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('ar_name')
                    ->label(__('Arabic Name'))
                    ->required()
                    // make field unique in same headquarter but accept current value on update
                    ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule, callable $get) {

                        $faculty_ArName = $get('ar_name'); // ar_name input
                        $faculty_HeadquarterId = $this->getOwnerRecord()->id; // headquarter_id from the relation

                        $faculty_Exist_ArName = DB::table('faculties')->where('ar_name', $faculty_ArName)
                            ->where('headquarter_id', $faculty_HeadquarterId)
                            ->first()->ar_name ?? $faculty_ArName;

                        $faculty_Exist_HeadquarterId = DB::table('faculties')->where('ar_name', $faculty_ArName)
                            ->where('headquarter_id', $faculty_HeadquarterId)
                            ->first()->headquarter_id ?? $faculty_HeadquarterId;

                        return $rule->where('ar_name', $faculty_Exist_ArName)->where('headquarter_id', $faculty_Exist_HeadquarterId);
                    })
                    /*
                        let field not accept just numbers or just special characters or english letters just arabic letters
                        or arabic letters with special characters or numbers not with the both in same time
                    */
                    ->rules(['regex:/^[\p{Arabic}\s]+(?:[\d]+|[!@#$%^&*()\-_=+{}|[\]\\;:",.<>?]+)?$/u'])
                    ->validationMessages([
                        'unique' => __('unique validation'),
                        'regex' => __('regex validation'),
                    ])
                    ->maxLength(255),
                Forms\Components\TextInput::make('en_name')
                    ->label(__('English Name'))
                    ->required()
                    // make field unique in same headquarter but accept current value on update
                    ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule, callable $get) {

                        $faculty_EnName = $get('en_name'); // en_name input
                        $faculty_HeadquarterId = $this->getOwnerRecord()->id; // headquarter_id from the relation

                        $faculty_Exist_EnName = DB::table('faculties')->where('en_name', $faculty_EnName)
                            ->where('headquarter_id', $faculty_HeadquarterId)
                            ->first()->en_name ?? $faculty_EnName;

                        $faculty_Exist_HeadquarterId = DB::table('faculties')->where('en_name', $faculty_EnName)
                            ->where('headquarter_id', $faculty_HeadquarterId)
                            ->first()->headquarter_id ?? $faculty_HeadquarterId;

                        return $rule->where('en_name', $faculty_Exist_EnName)->where('headquarter_id', $faculty_Exist_HeadquarterId);
                    })
                    /*
                        let field not accept just numbers or just special characters or arabic letters just english letters
                        or english letters with special characters or numbers not with the both in same time
                    */
                    ->rules(['regex:/^[a-zA-Z ]+(?:[\d]+|[!@#$%^&*()\-_=+{}|[\]\\;:",.<>?]+)?$/'])
                    ->validationMessages([
                        'unique' => __('unique validation'),
                        'regex' => __('regex validation'),
                    ])
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Faculties')) // the title of table
            ->columns([
                Tables\Columns\TextColumn::make('#')
                    ->alignment(Alignment::Center)
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('code')
                    ->translateLabel()
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ar_name')
                    ->label(__('Arabic Name'))
                    ->searchable()
                    ->alignment(Alignment::Center)
                    ->sortable(),
                Tables\Columns\TextColumn::make('en_name')
                    ->label(__('English Name'))
                    ->searchable()
                    ->alignment(Alignment::Center)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->translateLabel()
                    ->alignment(Alignment::Center)
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->translateLabel()
                    ->alignment(Alignment::Center)
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->mutateFormDataUsing(function (array $data): array {
                    // Get the latest code from the database
                    $latestCode = Faculty::orderBy('code', 'desc')->first()->code ?? 'fa_0';

                    // Extract the number part from the latest code
                    $latestNumber = intval(preg_replace('/[^0-9]+/', '', $latestCode));

                    // Increment the number
                    $newNumber = $latestNumber + 1;

                    // Generate the new code
                    $newCode = 'fa_' . $newNumber;
                    $data['code'] = $newCode;
                    return $data;
                })->label(__('Add Faculty'))->modalHeading(__("Add Faculty")),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->modalHeading(__("Edit Faculty")),
                //putting condition to stop delete record if found related data
                Tables\Actions\DeleteAction::make()
                    ->modalHeading(__("Delete Faculty"))
                    ->before(function (Tables\Actions\DeleteAction $action, Faculty $record) {
                        if ($record->departments()->exists()) {
                            Notification::make()
                                ->danger()
                                ->color('danger')
                                ->title(__('Failed to delete'))
                                ->body(__('Faculty contains on departments related'))
                                ->seconds(10)
                                ->send();

                            // This will halt and cancel the delete action modal.
                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->modalHeading(__("Delete Faculties")),
                ]),
            ]);
    }
}
