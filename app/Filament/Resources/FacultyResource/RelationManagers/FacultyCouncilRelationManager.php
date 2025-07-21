<?php

namespace App\Filament\Resources\FacultyResource\RelationManagers;

use App\Models\Faculty;
use App\Models\FacultyCouncil;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\Forms\Components\Button;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\DB;

class FacultyCouncilRelationManager extends RelationManager
{
    protected static string $relationship = 'council';

        // The title of the relation manager
        protected static ?string $title = 'المجلس';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('Faculty dean')
                    ->translateLabel()
                    ->default(function () {
                        $faculty_id = $this->getOwnerRecord()->id;

                        $headOfCouncil_name = User::where('faculty_id', $faculty_id)->where('position_id', 5)->get()->first()->name ?? __("There is no Faculty Dean");

                        return $headOfCouncil_name;
                    })
                    ->readOnly(),

                TextInput::make('Secretary of faculty council')
                    ->translateLabel()
                    ->default(function () {
                        $faculty_id = $this->getOwnerRecord()->id;

                        $secretaryOfFacultyCouncil_name = User::where('faculty_id', $faculty_id)->where('position_id', 4)->get()->first()->name ?? __("There is no Secretary of faculty council");

                        return $secretaryOfFacultyCouncil_name;
                    })
                    ->readOnly(),

                Select::make('members')
                    ->translateLabel()
                    ->placeholder(__('Choose the members of faculty council'))
                    ->multiple()
                    ->options(
                        User::Where('faculty_id', $this->getOwnerRecord()->id) // display all users of the current faculty
                            ->whereNotIn('position_id', [4, 5]) // can't choose the dean & secretary of current faculty
                            ->get()->pluck('name', 'id')->toArray()
                    )
                    ->default(function () {
                        $faculty_id = $this->getOwnerRecord()->id;

                        $membersOfCouncil = FacultyCouncil::where('faculty_id', $faculty_id)
                            ->whereNotIn('position_id', [4, 5]) // don't displaying dean & secretary of faculty
                            ->pluck('user_id')->toArray();

                        return ($membersOfCouncil);
                    })->required()->columnSpanFull()
                    ->validationMessages([
                        'required' => __('required validation'),
                    ]),

            ])->columns(2);
    }
    public function table(Table $table): Table
    {
        $faculty_id = $this->getOwnerRecord()->id;

        // Retrieve the count of records in the faculty_councils table
        $count = FacultyCouncil::where('faculty_id',$faculty_id)->count();
        // Define the label based on the count of records
        $label = $count > 0 ? __('Update faculty council') : __('Formate faculty council');

        return $table
            ->emptyStateHeading(__('No council yet')) // empty data message
            ->emptyStateDescription('') // empty data message description
            ->heading(__('Council'))
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
                    ->icon('heroicon-o-user-group')
                    ->createAnother(false) // disableing create another btn
                    ->modalHeading($label)

                    // formating council function
                    ->action(function (array $data): void {

                        // Prepare data array for insertion
                        $insertData = [];

                        $faculty_id = $this->getOwnerRecord()->id;

                        // Delete existing records for the faculty
                        DB::table('faculty_councils')->where('faculty_id', $faculty_id)->delete();


                        $Faculty_Dean = User::where('name', $data['Faculty dean'])->first(); // getting Faculty_Dean data from his name

                        $Secretary_Of_Faculty_Council = User::where('name', $data['Secretary of faculty council'])->first(); // getting Secretary_Of_Faculty_Council data from his name

                        $members = User::whereIn('id', $data['members'])->get(); // calling members of council data from there ids

                        // Insert dean of faculty
                        $insertData[] = [
                            'user_id' => $Faculty_Dean->id,
                            'position_id' => 5, // dean of faculty
                            'faculty_id' => $faculty_id,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];

                        // Insert secretary of faculty council
                        $insertData[] = [
                            'user_id' => $Secretary_Of_Faculty_Council->id,
                            'position_id' => 4, // Secretary of faculty council
                            'faculty_id' => $faculty_id,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];

                        // Insert members of faculty council
                        foreach ($members as $member) {
                            $insertData[] = [
                                'user_id' => $member->id,
                                'position_id' => 1, // Acadimic Staff
                                'faculty_id' => $faculty_id,
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                        }

                        // Insert data into the database
                        DB::table('faculty_councils')->insert($insertData);

                        // Display success notification
                        Notification::make()
                            ->title(__('Faculty Council formated successfully'))
                            ->color('success')
                            ->send();
                    }),
            ]);
    }

    private static function getPositionAndRoleName()
    {
        return  app()->getLocale() == 'en' ? 'name' : 'ar_name';
    }
}
