<?php

namespace App\Filament\Resources\DepartmentResource\RelationManagers;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\Department_Council;
use App\Models\FacultyCouncil;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class DepartmentCouncilRelationManager extends RelationManager
{
    protected static string $relationship = 'council';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('head of department')
                    ->translateLabel()
                    ->placeholder(__('Choose the head of department'))

                    ->options(function () { //display users whome just on this faculty & Head of department
                        $faculty_id = $this->getOwnerRecord()->faculty_id;

                        // $users = User::where('faculty_id', $faculty_id)->where('position_id', 3)->pluck('name', 'id');
                        $users = User::where('faculty_id', $faculty_id)->where('position_id', 3)->pluck('name', 'id');

                        return $users;
                    })

                    ->default(function () {
                        $department_id = $this->getOwnerRecord()->id;

                        $head_of_council = Department_Council::where('department_id', $department_id)
                            ->where('position_id', 3) //display user who has Head of department position
                            ->pluck('user_id')->first();

                        return $head_of_council;
                    })
                    ->required()
                    ->validationMessages([
                        'required' => __('required validation'),
                    ])
                    ->native(false),

                Select::make('secretary of department council')
                    ->translateLabel()
                    ->placeholder(__('Choose the secretary of council'))

                    ->options(function () { //display users whome just on this faculty & position Secretary of department council
                        $faculty_id = $this->getOwnerRecord()->faculty_id;

                        $users = User::where('faculty_id', $faculty_id)->where('position_id', 2)->pluck('name', 'id');

                        return $users;
                    })

                    ->default(function () {
                        $department_id = $this->getOwnerRecord()->id;

                        $secretary_of_department_council = Department_Council::where('department_id', $department_id)
                            ->where('position_id', 2) //display user who has Secretary of department council position
                            ->pluck('user_id')->first();

                        return $secretary_of_department_council;
                    })
                    ->required()
                    ->validationMessages([
                        'required' => __('required validation'),
                    ])
                    ->native(false),

                Select::make('members')
                    ->translateLabel()
                    ->placeholder(__('Choose the members of council'))
                    ->multiple()

                    //display all users whome just on this faculty & all has position facultyDean & filterd selectd head and secretary of this form
                    ->options(function (Get $get) {
                        $faculty_id = $this->getOwnerRecord()->faculty_id;

                        $selectedHeadOfDep = $get('head of department');
                        $selectedSecOfDep = $get('secretary of department council');

                        if (!$selectedHeadOfDep && !$selectedSecOfDep) {
                            return [];
                        }
                        $selectedUsers = [$selectedHeadOfDep, $selectedSecOfDep];

                        // $users = User::where('faculty_id', $faculty_id)->where('position_id', 1)->pluck('name', 'id');
                        $users = User::where('faculty_id', $faculty_id)->orWhere('position_id', 5)->pluck('name', 'id');

                        // Remove selected users from the users list
                        $filteredUsers = $users->reject(function ($value, $key) use ($selectedUsers) {
                            return in_array($key, $selectedUsers);
                        });

                        // dd($selectedUsers);
                        return $filteredUsers;
                    })

                    ->default(function () {
                        $department_id = $this->getOwnerRecord()->id;

                        $members_of_council = Department_Council::where('department_id', $department_id)
                            ->where('position_id', 1) //display user who has acadmic staff position
                            ->pluck('user_id')->toArray();

                        return $members_of_council;
                    })
                    ->required()->columnSpanFull()
                    ->validationMessages([
                        'required' => __('required validation'),
                    ])
                    ->native(false),

            ])->columns(2);
    }
    public function table(Table $table): Table
    {
        $department_id = $this->getOwnerRecord()->id;

        // Retrieve the count of records in the department__councils table
        $count = Department_Council::where('department_id', $department_id)->count();
        // Define the label based on the count of records
        $label = $count > 0 ? __('Update department council') : __('Formate department council');

        return $table
            ->heading(__('Council'))
            ->emptyStateHeading(__('No council yet')) // empty data message
            ->emptyStateDescription('') // empty data message description
            ->columns([
                Tables\Columns\TextColumn::make('#')
                    ->alignment(Alignment::Center)
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('user.name')
                    ->alignment(Alignment::Center)
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('position.' . self::getPositionAndRoleName())
                    ->alignment(Alignment::Center)
                    ->label(__('Position at council'))
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
            ->paginated(false) // disable the pagination
            ->headerActions([

                Tables\Actions\CreateAction::make()
                    ->label($label)
                    // ->color('success')
                    ->slideOver()
                    ->modalHeading($label)
                    ->icon('heroicon-o-user-group')
                    ->createAnother(false) // disableing create another btn

                    // formating council function
                    ->action(function (array $data, Department_Council $department_council_record, Department $department_record): void {

                        // Prepare data array for insertion
                        $insertData = [];

                        $department_id = $this->getOwnerRecord()->id;

                        //discarding old data
                        DB::table('department__councils')->where('department_id', $department_id)->delete();

                        // Insert head of department
                        $insertData[] = [
                            'user_id' => $data['head of department'],
                            'position_id' => 3, // Head of department
                            'department_id' => $department_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        // Insert secretary of department council
                        $insertData[] = [
                            'user_id' => $data['secretary of department council'],
                            'position_id' => 2, // secretary of department council
                            'department_id' => $department_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        // Insert members of department council
                        foreach ($data['members'] as $member) {
                            $insertData[] = [
                                'user_id' => $member,
                                'position_id' => 1, // acadimic staff
                                'department_id' => $department_id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }

                        // Insert data into the database
                        $department_council_record->insert($insertData);

                        // Display success notification
                        Notification::make()
                            ->title(__('Department Council formated successfully'))
                            ->color('success')
                            ->send();
                    }),
            ]);
    }
    private static function getPositionAndRoleName()
    {
        return app()->getLocale() == 'en' ? 'name' : 'ar_name';
    }
}
