<?php

namespace App\Filament\Resources\FacultyResource\RelationManagers;

use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Unique;

class DepartmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'departments';

        // The title of the relation manager
        protected static ?string $title = 'الأقسام';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('ar_name')
                    ->required()
                    ->label(__('Arabic Name'))
                    // make field unique in same Faculty but accept in another one
                    ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule, callable $get) {

                        $department_ArName = $get('ar_name'); // ar_name input
                        $department_FacultyId = $this->getOwnerRecord()->id; // faculty_id from the relation

                        $department_Exist_ArName = DB::table('departments')->where('ar_name', $department_ArName)
                            ->where('faculty_id', $department_FacultyId)
                            ->first()->ar_name ?? $department_ArName;

                        $department_Exist_FacultyId = DB::table('departments')->where('ar_name', $department_ArName)
                            ->where('faculty_id', $department_FacultyId)
                            ->first()->faculty_id ?? $department_FacultyId;

                        return $rule->where('ar_name', $department_Exist_ArName)->where('faculty_id', $department_Exist_FacultyId);
                    })
                    /*
                        let field not accept just numbers or just special characters or english letters but allow
                        just arabic letters or arabic letters with special characters or numbers or with the both in same time
                    */
                    ->rules(['regex:/^(?![a-zA-Z])(?=.*[\p{Arabic}])[\p{Arabic}\d\s!@#$%^&*()\-_=+{}|[\]\\;:",.<>?]+$/u'])
                    ->validationMessages([
                        'unique' => __('unique validation'),
                        'regex' => __('regex validation'),
                        'required' => __('required validation'),
                    ])
                    ->maxLength(255),
                Forms\Components\TextInput::make('en_name')
                    ->required()
                    ->label(__('English Name'))
                    // make field unique in same Faculty but accept in another one
                    ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule, callable $get) {

                        $department_EnName = $get('en_name'); // en_name input
                        $department_FacultyId = $this->getOwnerRecord()->id; // faculty_id from the relation

                        $department_Exist_EnName = DB::table('departments')->where('en_name', $department_EnName)
                            ->where('faculty_id', $department_FacultyId)
                            ->first()->en_name ?? $department_EnName;

                        $department_Exist_FacultyId = DB::table('departments')->where('en_name', $department_EnName)
                            ->where('faculty_id', $department_FacultyId)
                            ->first()->faculty_id ?? $department_FacultyId;

                        return $rule->where('en_name', $department_Exist_EnName)->where('faculty_id', $department_Exist_FacultyId);
                    })
                    /*
                        let field not accept just numbers or just special characters or arabic letters but allow
                        just english letters or english letters with special characters or numbers or with the both in same time
                    */
                    ->rules(['regex:/^(?![\p{Arabic}])(?=.*[a-zA-Z])[a-zA-Z\d\s!@#$%^&*()\-_=+{}|[\]\\;:",.<>?]+$/u'])
                    ->validationMessages([
                        'unique' => __('unique validation'),
                        'regex' => __('regex validation'),
                        'required' => __('required validation'),
                    ])
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading(__('No departments yet')) // empty data message
            ->emptyStateDescription('') // empty data message description
            ->heading(__('Departments')) // the title of table
            ->columns([
                Tables\Columns\TextColumn::make('#')
                    ->alignment(Alignment::Center)
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('code')
                    ->alignment(Alignment::Center)
                    ->translateLabel()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ar_name')
                    ->alignment(Alignment::Center)
                    ->label(__('Arabic Name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('en_name')
                    ->alignment(Alignment::Center)
                    ->label(__('English Name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->alignment(Alignment::Center)
                    ->translateLabel()
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->alignment(Alignment::Center)
                    ->translateLabel()
                    ->dateTime()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->mutateFormDataUsing(function (array $data): array {
                    // Get the latest code from the database
                    $latestCode = Department::orderBy('code', 'desc')->first()->code ?? 'dept_0';

                    // Extract the number part from the latest code
                    $latestNumber = intval(preg_replace('/[^0-9]+/', '', $latestCode));

                    // Increment the number
                    $newNumber = $latestNumber + 1;

                    // Generate the new code
                    $newCode = 'dept_' . $newNumber;
                    $data['code'] = $newCode;
                    return $data;
                })->label(__('Add Department'))->modalHeading(__("Add Department")),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->modalHeading(__("Edit Department")),
                Tables\Actions\DeleteAction::make()->modalHeading(__("Delete Department")),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->modalHeading(__("Delete Departments")),
                ]),
            ]);
    }
}
