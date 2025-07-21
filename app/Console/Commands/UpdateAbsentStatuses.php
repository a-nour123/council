<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Session;
use App\Models\SessionAttendanceInvite;

class UpdateAbsentStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'session:update-absent-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update user statuses to absent based on session start time';

    /**
     * Execute the console command.
     *
     * @return int
     */
    // public function handle()
    // {
    //     // Get the current date and time
    //     $now = now();

    //     // Fetch sessions that have started and have users associated with them without any status
    //     $sessions = Session::where('start_time', '<=', $now)
    //         ->whereHas('attendances', function ($query) {
    //             $query->whereNull('status');  // Check if the status is null
    //         })
    //         ->get();

    //     foreach ($sessions as $session) {
    //         // Get user invites for the session that do not have a status
    //         $invites = SessionAttendanceInvite::where('session_id', $session->id)
    //             ->whereNull('status')  // Check if the status is null
    //             ->get();

    //         foreach ($invites as $invite) {
    //             // Update invite status to 'absent'
    //             $invite->update(['status' => 'absent']);
    //         }

    //         // Log the session ID that was updated
    //         $this->info("Updated absent status for session ID {$session->id}");
    //     }

    //     return 0; // Indicate that the command was successful
    // }
}
