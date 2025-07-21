<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LDAPResource\Pages;
use App\Filament\Resources\LDAPResource\RelationManagers;
use App\Models\LDAP;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LDAPResource extends Resource
{
    protected static ?string $model = LDAP::class;
    protected static ?string $slug = 'ldap-settings'; // Custom URL instead of 'ldaps'

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canViewAny(): bool
    {
        return auth()->user()->can('ldapConfigration', arguments: User::class);
    }

    // public static function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             //
    //         ]);
    // }

    // public static function table(Table $table): Table
    // {
    //     return $table
    //         ->columns([
    //             //
    //         ])
    //         ->filters([
    //             //
    //         ])
    //         ->actions([
    //             Tables\Actions\EditAction::make(),
    //             Tables\Actions\DeleteAction::make(),
    //         ])
    //         ->bulkActions([
    //             Tables\Actions\BulkActionGroup::make([
    //                 Tables\Actions\DeleteBulkAction::make(),
    //             ]),
    //         ]);
    // }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLDAPS::route('/'),
        ];
    }

    public static function getBreadcrumb(): string
    {
        return '';
    }

    public static function getPluralLabel(): ?string
    {
        return __('LDAP Settings');
    }
}
