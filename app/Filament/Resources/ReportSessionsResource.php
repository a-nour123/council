<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportSessionsResource\Pages;
use App\Filament\Resources\ReportSessionsResource\RelationManagers;
use App\Models\Session;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReportSessionsResource extends Resource
{
    protected static ?string $model = Session::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canViewAny(): bool
    {
        return auth()->user()->can('reports', Session::class);
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
            'index' => Pages\ManageReportSessions::route('/'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Reports');
    }
    public static function getBreadcrumb(): string
    {
        return '';
    }

    public static function getPluralLabel(): ?string
    {
        return __('Session departments reports');
    }
}
