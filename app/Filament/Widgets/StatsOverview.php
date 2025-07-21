<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\UserResource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 0;

    public function table(Table $table): Table
    {
        $user = auth()->user();

        // Check if user and faculty exist to avoid errors
        $userName = $user ? $user->name : 'N/A';
        $faculty = $user && $user->faculty ? $user->faculty->ar_name : 'بدون كلية';
        $department = $user && $user->department ? $user->department->ar_name : 'بدون قسم';
        $position = $user && $user->position ? $user->position->ar_name : 'بدون منصب';
        $acadimicRank = $user && $user->acadimic_rank ? $user->acadimic_rank->ar_name : 'بدون رتبة علمية';

        return $table
            ->query(UserResource::getEloquentQuery()->where('id', $user->id))
            ->heading('')
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->translateLabel()
                    ->alignment(Alignment::Center)
                    ->getStateUsing($userName),
                Tables\Columns\TextColumn::make('faculty')
                    ->translateLabel()
                    ->alignment(Alignment::Center)
                    ->getStateUsing($faculty),
                Tables\Columns\TextColumn::make('department')
                    ->translateLabel()
                    ->alignment(Alignment::Center)
                    ->getStateUsing($department),
                Tables\Columns\TextColumn::make('position')
                    ->translateLabel()
                    ->alignment(Alignment::Center)
                    ->getStateUsing($position),
                Tables\Columns\TextColumn::make('Acadimic rank')
                    ->translateLabel()
                    ->alignment(Alignment::Center)
                    ->getStateUsing($acadimicRank),
            ]);
    }
}
