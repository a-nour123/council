<?php
/*
namespace App\Filament\Resources;

use App\Filament\Resources\ControlReportResource\Pages;
use App\Filament\Resources\ControlReportResource\RelationManagers;
use App\Models\ControlReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkActionGroup;

class ControlReportResource extends Resource
{
    protected static ?string $model = ControlReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Define form fields here
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('topicReport.title')
                    ->translateLabel()
                    ->searchable()
                    ->alignment(Alignment::Center)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->translateLabel()
                    ->dateTime()
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->translateLabel()
                    ->dateTime()
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Define table filters here
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label(function ($record) {
                        return __('Edit');
                    })
                    ->icon('heroicon-o-play')
                    ->action(function ($record) {
                        $host = request()->getSchemeAndHttpHost();
                        $url = $host . '/councils/public/admin/control-reports/' . $record->id . '/editReport';

                        return redirect()->away($url);
                    }),
            ])
            ->recordUrl(function ($record) {
                return null;
            }) // disable opening edit mode whenr click on row
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(), // Default delete bulk action provided by Filament
                ]),
            ])
            ->query(function (ControlReport $query) {

                if (auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('System Admin')) {
                    // If the user has the role of Super Admin or System Admin, show all sessions
                    return $query;
                }
                if (in_array(auth()->user()->position_id, [2, 3, 4, 5])) {
                    return $query;
                }else{
                    abort(403, 'You do not have access to this page.');
                }
                // return $query->where('id', 0);
            });
    }

    public static function getRelations(): array
    {
        return [
            // Define any relation managers if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListControlReports::route('/'),
            'create' => Pages\CreateControlReport::route('/create'),
            'edit' => Pages\EditControlReport::route('/{record}/edit'),
            'report-details-add' => Pages\ReportControlCreate::route('createReport'),
            'report-details-edit' => Pages\ReportControlEdit::route('/{record}/editReport'),
        ];
    }
    public static function getNavigationGroup(): ?string
    {
        return __('Topics Management');
    }
    public static function getLabel(): ?string
    {
        return __('Control Report');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Control Reports');
    }
}
*/
