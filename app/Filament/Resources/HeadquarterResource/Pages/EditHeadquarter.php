<?php

namespace App\Filament\Resources\HeadquarterResource\Pages;

use App\Filament\Resources\HeadquarterResource;
use App\Models\Headquarter;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditHeadquarter extends EditRecord
{
    protected static string $resource = HeadquarterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //putting condition to stop delete record if found related data
            Actions\DeleteAction::make()
                // ->before(function (Actions\DeleteAction $action, Headquarter $record) {
                //     if ($record->faculties()->exists()) {
                //         Notification::make()
                //             ->danger()
                //             ->color('danger')
                //             ->title(__('Failed to delete'))
                //             ->body(__('Headquarter contains on faculties related'))
                //             ->seconds(10)
                //             ->send();

                //             // This will halt and cancel the delete action modal.
                //             $action->cancel();
                //     }
                // }),
        ];
    }
}
