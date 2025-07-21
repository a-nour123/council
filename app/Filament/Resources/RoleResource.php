<?php
/*

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Auth;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';
    // protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->minLength(2)
                    ->maxLength(255)
                    ->translateLabel()
                    ->unique(ignoreRecord: true),

                Select::make('permissions')
                    ->relationship(name: 'permissions', titleAttribute: 'name')
                    ->multiple()
                    ->translateLabel()
                    ->preload()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->translateLabel(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }


    
    Retrieves a modified Eloquent query, conditionally excluding the "Super Admin" user
    based on the currently logged-in user's role.

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        // Check if the logged-in user is a Super Admin
        if ($user && $user->name === 'Super Admin') {
            return parent::getEloquentQuery();
        } else {
            // Apply the name filtering for non-Super Admin users
            return parent::getEloquentQuery()->where('name', '!=', 'Super Admin');
        }

    }
    public static function getNavigationGroup(): ?string
    {
        return __('Settings');
    }

    public static function getLabel(): ?string
    {
        return __('Role');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Roles');
    }
}
*/
