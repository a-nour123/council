<?php

namespace App\Filament\Widgets;


use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ProjectObjectives extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(\App\Models\ProjectObjectives::query())
            ->heading(__('Project Objectives'))
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('')
                    ->alignment(Alignment::Center)
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('name')
                    ->label(__(''))
                    ->wrap()
            ]);
    }
}
