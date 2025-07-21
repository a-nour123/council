<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportFacultySessionsResource\Pages;
use App\Filament\Resources\ReportFacultySessionsResource\RelationManagers;
use App\Models\FacultySession;
use App\Models\Session;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReportFacultySessionsResource extends Resource
{
    protected static ?string $model = FacultySession::class;
    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canViewAny(): bool
    {
        return auth()->user()->can('reports', FacultySession::class);
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
            'index' => Pages\ManageReportFacultySessions::route('/'),
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
        return __('Faculty session reports');
    }
}
