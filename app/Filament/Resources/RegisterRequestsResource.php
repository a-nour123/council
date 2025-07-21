<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegisterRequestsResource\Pages;
use App\Filament\Resources\RegisterRequestsResource\RelationManagers;
use App\Models\RegisterRequests;
use App\Models\Role;
use App\Models\User;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Filament\Infolists\Components\TextEntry;

class RegisterRequestsResource extends Resource
{
    protected static ?string $model = RegisterRequests::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // public static function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             Forms\Components\TextInput::make('user_id')
    //                 ->label(__('Username'))
    //                 ->required()
    //                 ->readOnly(),
    //             Forms\Components\TextInput::make('department_id')
    //                 ->label(__('Department'))
    //                 ->required()
    //                 ->readOnly(),
    //         ]);
    // }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('#')
                    ->alignment(Alignment::Center)
                    ->rowIndex(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('Username'))
                    ->alignment(Alignment::Center)
                    ->numeric()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label(__('Email'))
                    ->alignment(Alignment::Center)
                    ->numeric()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.faculty.' . self::getFacultyOrDepartmentName())
                    ->label(__('Faculty'))
                    ->alignment(Alignment::Center)
                    ->numeric()
                    ->searchable()
                    ->sortable()
                    ->visible(function () {
                        if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin')) {
                            return true;
                        } else {
                            return false;
                        }
                    }),

                Tables\Columns\TextColumn::make('department.' . self::getFacultyOrDepartmentName())
                    ->label(__('Department'))
                    ->alignment(Alignment::Center)
                    ->numeric()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->translateLabel()
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->translateLabel()
                    ->alignment(Alignment::Center)
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\ActionGroup::make([
                Tables\Actions\ViewAction::make()
                    ->modalHeading(__('Registration applicant data')),
                Tables\Actions\Action::make('accept')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->translateLabel()
                    ->action(function (array $data, $record): void {
                        $userId = $record->user_id;
                        $departmentId = $record->department_id;

                        $userData = User::find($userId);
                        $userData->update([
                            'is_active' => 1 // let user active to access the site
                        ]);

                        $userRole = Role::where('name', 'Member')->first();
                        $userData->assignRole($userRole);  // Assign role to the created user

                        // deleting the record
                        $record->delete();

                        Notification::make()
                            ->title(__('User now is active'))
                            ->icon('heroicon-o-x-circle')
                            ->success()
                            ->color('success')
                            ->duration(1500)
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->translateLabel()
                    ->action(function (array $data, $record): void {
                        $userId = $record->user_id;
                        $departmentId = $record->department_id;

                        $userData = User::find($userId);
                        $userData->update([
                            'is_active' => 0 // let user not active to access the site
                        ]);

                        $userRole = Role::where('name', 'Member')->first();
                        $userData->assignRole($userRole);  // Assign role to the created user

                        // deleting the record
                        $record->delete();

                        Notification::make()
                            ->title(__('User now is not active'))
                            ->icon('heroicon-o-x-circle')
                            ->warning()
                            ->color('warning')
                            ->duration(1500)
                            ->send();
                    }),
                // ])
            ])
            ->defaultSort('created_at', 'desc')
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ])
            ->recordUrl(function ($record) {
                return null;
            }) // disable opening edit mode whenr click on row
            ->query(function (RegisterRequests $query) {

                if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin')) {
                    // If the user has the role of Super Admin or System Admin, show all sessions
                    return $query;
                }

                if (auth()->user()->hasRole('Faculty Admin')) { // if user is has postion head of department
                    $facultyDepartmentslIds = Department::where('faculty_id', auth()->user()->faculty_id)
                        ->pluck('id')->toArray();

                    $query = RegisterRequests::whereIn('department_id', $facultyDepartmentslIds);

                    return $query;
                }

                if (auth()->user()->position_id == 3) { // if user is has postion head of department
                    $userDepartmentslIds = DB::table('department__councils')
                        ->where('user_id', auth()->user()->id)
                        ->pluck('department_id')->toArray();

                    $query = RegisterRequests::whereIn('department_id', $userDepartmentslIds);

                    return $query;
                } else {
                    abort(403, 'You do not have access to this page.');
                }
                // return $query->where('id', 0);
            });
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('user.ar_name')
                    ->label(__('Arabic Name')),
                TextEntry::make('user.en_name')
                    ->label(__('English Name')),
                TextEntry::make('user.acadimic_rank.' . self::getAcadimicRankName())
                    ->label(__('Acadimic rank')),
                TextEntry::make('user.phone')
                    ->label(__('Phone number')),
                TextEntry::make('user.email')
                    ->label(__('Email')),
                TextEntry::make('user.faculty.' . self::getFacultyOrDepartmentName())
                    ->label(__('Faculty')),
                TextEntry::make('user.headquarter.name')
                    ->label(__('Headquarter')),
                TextEntry::make('department.' . self::getFacultyOrDepartmentName())
                    ->translateLabel(),
                TextEntry::make('created_at')
                    ->label(__('Send request at'))
                    ->dateTime(),
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
            'index' => Pages\ListRegisterRequests::route('/'),
            // 'create' => Pages\CreateRegisterRequests::route('/create'),
            // 'edit' => Pages\EditRegisterRequests::route('/{record}/edit'),
        ];
    }

    private static function getFacultyOrDepartmentName()
    {
        return app()->getLocale() == 'en' ? 'en_name' : 'ar_name';
    }
    private static function getAcadimicRankName()
    {
        return app()->getLocale() == 'en' ? 'name' : 'ar_name';
    }
    public static function getLabel(): ?string
    {
        return __('Register Request');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Register Requests');
    }
}
