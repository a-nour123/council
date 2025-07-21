<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AxisResource\Pages;
use App\Filament\Resources\AxisResource\Pages\CreateAxis;
use App\Filament\Resources\AxisResource\Pages\EditAxesPage as PagesEditAxesPage;
use App\Filament\Resources\AxisResource\Pages\EditAxis;
use App\Filament\Resources\AxisResource\Pages\ListAxes;
use Filament\Forms\Components\ViewField;
use App\Models\Axis;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\YesResource\Pages\EditAxesPage;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action as TablesAction;

class AxisResource extends Resource
{
    protected static ?string $model = Axis::class;

    protected static ?string $navigationIcon = 'heroicon-o-bars-arrow-up';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('title')
                //     ->translateLabel()
                //     ->required()
                //     ->unique(ignoreRecord: true)
                //     ->validationMessages([
                //         'unique' => __('unique validation'),
                //     ])
                //     ->maxLength(255),

                ViewField::make('form_builder')
                    ->view('FormBuilder/create'),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('#')
                    ->alignment(Alignment::Center)
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('title')
                    ->alignment(Alignment::Center)
                    ->translateLabel()
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
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                // Tables\Actions\Action::make('Manage lessons')
                // ->color('success')
                // ->icon('heroicon-m-academic-cap')
                // ->url(function (Axis $record): string {
                //     return route('filament.resources.axis.edit', ['record' => $record->id]);
                // }),
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
            'index' => ListAxes::route('/list'),
            'create' => CreateAxis::route('/create'),
            'edit' => Pages\EditAxis::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Topics Management');
    }

    public static function getLabel(): ?string
    {
        return __('Axie');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Axes');
    }
}
