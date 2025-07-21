<?php

namespace App\Filament\Resources\SubmitTopicResource\Pages;

use App\Filament\Resources\SubmitTopicResource;
use App\Models\AgandesTopicForm;
use App\Models\Topic;
use App\Models\TopicAgenda;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;

class ViewSubmitTopic extends ViewRecord
{
    protected static string $resource = SubmitTopicResource::class;

    protected function getHeaderActions(): array
    {
        return [

            // Action::make('Status')
            //     ->translateLabel()
            //     ->color('success')
            //     ->form(function (TopicAgenda $agenda, $record) {
            //         $options = [
            //             // 0 => __('Pending'),
            //             1 => __('Accepted'),
            //             2 => __('Rejected'),
            //         ];


            //         return [
            //             Select::make('status')
            //                 ->translateLabel()
            //                 ->native(false)
            //                 ->options($options)
            //                 ->required()
            //                 ->reactive()
            //                 ->validationMessages([
            //                     'required' => __('required validation'),
            //                 ]),

            //             Textarea::make('reject_reason')
            //                 ->translateLabel()
            //                 ->hidden(fn(Get $get): bool => !($get('status') == 2)) // hidden if status isn't rejected
            //                 ->required()
            //                 ->validationMessages([
            //                     'required' => __('required validation'),
            //                 ]),
            //         ];
            //     })
            //     ->action(function (array $data, $record): void {
            //         dd($data);
            //     }),
        ];
    }

}
