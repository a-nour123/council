<?php

namespace App\Filament\Resources\CollegeCouncilResource\Pages;

use App\Filament\Resources\CollegeCouncilResource;
use App\Models\CollegeCouncil;
use App\Models\Session;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCollegeCouncil extends EditRecord
{
    protected static string $resource = CollegeCouncilResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $sessionId = $data['session_id'];
        $sessionCode = Session::where('id',$sessionId)->value('code');
        $data['sessionCode'] = $sessionCode;

        // dd($data);
        return $data;
    }
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $sessionId = $data['session_id'];
        $status = $data['status'];

        $rejectReason = isset($data['reject_reason']) ? $data['reject_reason'] : null;

        if($rejectReason != null){
            CollegeCouncil::where('session_id',$sessionId)->update(['status' => $status ,'reject_reason' => $rejectReason]);
        }else{
            CollegeCouncil::where('session_id',$sessionId)->update(['status' => $status]);
        }

        // dd($data);
        return $data;
    }
}
