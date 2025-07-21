<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportAgendasResource\Pages;
use App\Filament\Resources\ReportAgendasResource\RelationManagers;
use App\Models\Session;
use App\Models\TopicAgenda;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReportAgendasResource extends Resource
{
    protected static ?string $model = TopicAgenda::class;
    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    public static function canViewAny(): bool
    {
        return auth()->user()->can('reports', TopicAgenda::class);
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
            'index' => Pages\ManageReportAgendas::route('/'),
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
        return __('Agendas reports');
    }
}
