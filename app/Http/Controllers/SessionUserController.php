<?php

namespace App\Http\Controllers;

use Alkoumi\LaravelHijriDate\Hijri;
use App\Filament\Resources\SessionDepartemtnResource;
use App\Models\AgandesTopicForm;
use App\Models\AgendaImage;
use App\Models\ClassificationDecision;
use App\Models\Department;
use App\Models\Session;
use App\Models\SessionAttendanceInvite;
use App\Models\SessionDecision;
use App\Models\SessionEmail;
use App\Models\SessionTopic;
use App\Models\SessionUser;
use App\Models\Topic;
use App\Models\TopicAgenda;
use App\Models\User;
use App\Models\UserDecisionVote;
use App\Models\YearlyCalendar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotificationsAction;
// use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

use ArPHP\I18N\Arabic;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\error;

class SessionUserController extends Controller
{
    public $recordId, $SessionCode, $depName, $depNameEn, $facName, $facNameEn, $DepHeadName, $SessionPlace;
    public $startTime, $startDate, $higriDate, $dayName, $acadimicYear, $endDateTime, $createdBy;
    public $members = [], $invitedMembers = [], $topics = [], $decisions = [], $fullTopics = [];
    public $decisionApproval, $processedReports, $decisionsStatusDependOnHead = [];
    public $sessionResposibleId;

    public function saveAttendance(Request $request)
    {

        $session_id = $request->input('session_id');
        $attendances = $request->input('attendance');

        foreach ($attendances as $user_id => $status) {

            $test = SessionAttendanceInvite::updateOrCreate(
                ['session_id' => $session_id, 'user_id' => $user_id],
                [
                    'actual_status' => $status,
                    'taken' => 1
                ]
            );
        }

        return response()->json(['message' => 'Attendance records updated successfully']);
    }


    public function fetchAttendance($session_id, $locale = null)
    {
        // Set the locale for the current request
        app()->setLocale($locale);

        $users = User::whereIn('id', function ($query) use ($session_id) {
            $query->select('user_id')
                ->from('session_user')
                ->where('session_id', $session_id)
                ->union(
                    \DB::table('session_emails')
                        ->select('user_id')
                        ->where('session_id', $session_id)
                );
        })->get();

        // Fetch status and absent_reason from session_attendance_invites for each user
        foreach ($users as $user) {
            $attendanceInvite = SessionAttendanceInvite::where('user_id', $user->id)->where('session_id', $session_id)->first();
            if ($attendanceInvite) {
                $user->status = $attendanceInvite->status;
                $user->absent_reason = $attendanceInvite->absent_reason;
                $user->actual_status = $attendanceInvite->actual_status;
                $user->taken = $attendanceInvite->taken;
            } else {
                // Set default values if no invite found
                $user->status = null;
                $user->absent_reason = null;
                $user->actual_status = null;
                $user->taken = null;
            }
        }


        $decisions = SessionDecision::where('session_id', $session_id)->get();
        $decisionApproval = [];
        foreach ($decisions as $decision) {
            $decisionApproval = $decision->approval;
        };

        // Build array containing id, name, status, and absent_reason for each user
        $userData = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'status' => $user->status,
                'absent_reason' => $user->absent_reason,
                'actual_status' => $user->actual_status,
                'taken' => $user->taken,
            ];
        })->toArray();
        // $userData['decision'] = $decisionApproval;

        return view('filament.resources.session-departemtn-resource.pages.attendance-list', compact('userData', 'decisionApproval', 'locale'));
    }

    public function getusersattandence($record)
    {

        $session = Session::findOrFail($record);

        $users = User::whereIn('id', function ($query) use ($record) {
            $query->select('user_id')
                ->from('session_user')
                ->where('session_id', $record);
        })->get();
        foreach ($users as $user) {
            $attendanceInvite = SessionAttendanceInvite::where('user_id', $user->id)->first();
            $user->status = $attendanceInvite->status ?? null;
            $user->actual_status = $attendanceInvite->actual_status ?? null;
            $user->absent_reason = $attendanceInvite->absent_reason ?? null;
        }

        $userData = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'status' => $user->status,
                'actual_status' => $user->actual_status,
                'absent_reason' => $user->absent_reason
            ];
        })->toArray();

        // Assuming you want to return this data as JSON
        return response()->json(['users' => $userData]);
    }
    public function saveTime(Request $request)
    {
        $hour = (int) $request->input('hour');
        $minute = (int) $request->input('minute');
        $second = (int) $request->input('second');
        $recordId = $request->input('recordId');

        // Find the session record
        $session = Session::find($recordId);

        if (!$session) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        // Convert start_time to a Carbon instance
        $startTime = Carbon::parse($session->start_time);

        // Calculate the total time in seconds
        $totalSeconds = ($hour * 3600) + ($minute * 60) + $second;

        // Add the total seconds to the start time
        $actualEndTime = $startTime->addSeconds($totalSeconds);

        // Update the session record with the calculated end time
        $session->actual_end_time = $actualEndTime;
        $session->save();

        return response()->json(['message' => 'Thank you, the actual session end time has been saved.']);
    }
    public function GetFormForSession(Request $request)
    {

        $sessionId = $request->id;

        // Step 1: Get all user IDs related to the session
        $userIdsFromUsers = DB::table('session_user')
            ->where('session_id', $sessionId)
            ->pluck('user_id')
            ->toArray();


        // Step 2: Get all user IDs from faculty_session_emails
        $userIdsFromEmails = DB::table('session_emails')
            ->where('session_id', $sessionId)
            ->pluck('user_id')
            ->toArray();
        // Merge the two arrays and remove duplicates
        $userIds = array_unique(array_merge($userIdsFromUsers, $userIdsFromEmails));

        // Get the count of total users
        $totalUsers = count($userIds);


        // Step 6: Check if all users have `taken = 1`
        $allUsersTaken = SessionAttendanceInvite::where('session_id', $sessionId)
            ->whereIn('user_id', $userIds)
            ->where('taken', '=', 1)
            ->count(); // Call count() as a method

        if ($allUsersTaken < 1) {
            return response()->json([
                'error' => "لا يمكنك إكمال الجلسة قبل تأكيد الحضور لجميع الأعضاء"
            ], 400);
        }


        // Step 2: Get users who are absent (actual_status = 2 or 3) in SessionAttendanceInvite
        $absentUsers = SessionAttendanceInvite::whereIn('actual_status', [2, 3])
            ->where('session_id', $sessionId)
            ->whereIn('user_id', $userIds)
            ->pluck('user_id')
            ->toArray();

        // Count the number of absent users
        $absentUsersCount = count($absentUsers);

        // Step 3: Calculate the attendance threshold (1/3 of total users)
        $requiredAttendanceThreshold = $totalUsers / 3; // Use `ceil()` to round up
        // Step 4: Check if absent users exceed 1/3 of total users
        if ($absentUsersCount > $requiredAttendanceThreshold) {
            return response()->json([
                'error' => "لا يمكنك إكمال الجلسة بسبب غياب أكثر من ثلث الأعضاء"
            ], 400);
        }

        // If validation passes, continue with your logic


        // Retrieve session topic IDs associated with the session ID
        $topicSessionIds = SessionTopic::where('session_topics.session_id', $sessionId)
            ->join('topics_agendas as agendas', 'agendas.id', '=', 'session_topics.topic_agenda_id')
            ->join('topics', 'topics.id', '=', 'agendas.topic_id')
            ->orderBy('topics.order', 'asc') // Order by the topic's order first
            ->orderBy('session_topics.topic_agenda_id', 'asc') // Then order by the topic_agenda_id
            ->pluck('session_topics.topic_agenda_id'); // Pluck only the topic_agenda_id

        $session = Session::findOrFail($sessionId);

        // Retrieve form data based on agenda IDs associated with the session topics
        $formData = AgandesTopicForm::whereIn('agandes_topic_form.agenda_id', $topicSessionIds)
            ->join('topics_agendas as agendas', 'agendas.id', '=', 'agandes_topic_form.agenda_id')
            ->join('topics', 'topics.id', '=', 'agendas.topic_id')
            ->orderBy('topics.order', 'asc') // Order by the topic's order first
            ->orderBy('agandes_topic_form.agenda_id', 'asc') // Then order by the agenda_id
            ->select('agandes_topic_form.*') // Explicitly select required fields
            ->get();

        // Retrieve session decisions based on session ID and agenda IDs
        $sessionDecisions = SessionDecision::where('session_decisions.session_id', $sessionId)
            ->whereIn('session_decisions.agenda_id', $topicSessionIds)
            ->join('topics_agendas as agendas', 'agendas.id', '=', 'session_decisions.agenda_id')
            ->join('topics', 'topics.id', '=', 'agendas.topic_id')
            ->orderBy('topics.order', 'asc') // First, order by the topic's order
            ->orderBy('session_decisions.agenda_id', 'asc') // Then, order by agenda_id
            ->select('session_decisions.*') // Explicitly select required fields
            ->get();


        // Get decision names from ClassificationDecision
        $allDecisions = ClassificationDecision::all()->keyBy('id'); // Map decisions by ID for easy lookup

        // Group the form data and session decisions by topic_id and agenda_id
        $groupedData = [];
        foreach ($formData as $item) {
            $topicTitileFormate = $this->initializeTopicsWithoutDecision($session);

            $topicData = Topic::with([
                'mainTopic' => function ($query) {
                    $query->select('id', 'title', 'order'); // Specify columns to retrieve
                }
            ])->findOrFail($item->topic_id);

            $key = $item->agenda_id . '_' . $item->topic_id;
            if (!isset($groupedData[$key])) {
                $groupedData[$key] = [
                    'contents' => [],
                    'agendaId' => $item->agenda_id,
                    'topicId' => $item->topic_id,
                    'mainTopicId' => $topicData->mainTopic->id ?? null,
                    'mainTopicOrder' => (int)$topicData->mainTopic->order ?? null,
                    'topicOrder' => (int)$topicData->order ?? null,
                    // 'topicTitle' => $item->topic->title,
                    'topicTitle' => strip_tags($topicTitileFormate[$item->agenda_id]),
                    'topicDecisionsChoice' => $item->topic->decisions,  // Store the decision IDs
                    'sessionId' => $sessionId,
                    'decisions' => [],  // Store the decision objects here
                    'decisions_approval' => null,  // Will hold the approval status of decisions
                    'files' => [],  // Store associated files here
                ];
            }
            $groupedData[$key]['contents'][] = $item->content;
        }

        foreach ($sessionDecisions as $decision) {
            $key = $decision->agenda_id . '_' . $decision->topic_id;
            if (isset($groupedData[$key])) {
                $groupedData[$key]['decisions'][] = $decision;
                $currentDecisions = $groupedData[$key]['decisions'];
                foreach ($currentDecisions as $currentDecision) {
                    $decisionApproval = $currentDecision->approval;
                }
                $groupedData[$key]['decisions_approval'] = $decisionApproval;
            }
        }

        // Get decision names for the selected decision IDs
        foreach ($groupedData as $key => $data) {
            $decisionsNames = [];
            foreach (explode(',', $data['topicDecisionsChoice']) as $decisionId) {
                if (isset($allDecisions[$decisionId])) {
                    $decisionsNames[] = $allDecisions[$decisionId]->name;
                }
            }
            $groupedData[$key]['decisionsNames'] = $decisionsNames; // Store decision names in the grouped data
        }

        // Retrieve files associated with each agenda_id
        foreach ($groupedData as $key => $data) {
            $files = AgendaImage::where('agenda_id', $data['agendaId'])->get(); // Adjust according to your file model
            foreach ($files as $file) {
                // Assuming $file->path is relative to the storage/app/public directory
                $groupedData[$key]['files'][] = [
                    'file_path' => asset('storage/' . $file->file_path), // Generates a URL like '/storage/path/to/file.jpg'
                    'file_name' => $file->file_name
                ];
            }
        }

        uasort($groupedData, function ($a, $b) {
            // First, compare by mainTopicOrder
            if ($a['mainTopicOrder'] === $b['mainTopicOrder']) {
                // If mainTopicOrder is the same, compare by topicOrder
                return $a['topicOrder'] <=> $b['topicOrder'];
            }
            // Otherwise, compare by mainTopicOrder
            return $a['mainTopicOrder'] <=> $b['mainTopicOrder'];
        });

        // Send the data as a JSON response
        $filteredFormData = array_values($groupedData);
        return response()->json($filteredFormData);
    }




    // public function GetFormForSession(Request $request)
    // {
    //     $sessionId=$request->id;
    //     $topicSessionId = SessionTopic::where('session_id', $request->id)->pluck('topic_agenda_id');
    //     $formData = AgandesTopicForm::whereIn('agenda_id', $topicSessionId)->get();
    //     // dd($formData);
    //     return response()->json($formData , $sessionId);
    // }

    public function loadFormContent(Request $request, $locale = null)
    {
        // dd($locale);
        return view('filament.resources.session-departemtn-resource.pages.form-content', compact('locale'));
    }
    public function saveDecision(Request $request)
    {

        try {
            $formData = $request->input('formData');

            $agendaOrderInSession = 0;
            foreach ($formData as $data) {

                // Check if all fields in $data are null or empty
                if ($this->isEmptyFormData($data)) {
                    continue; // Skip insertion for empty data
                }
                $decisionOrderAgenda = (int) TopicAgenda::where('id', $data['agendaId'])->where('topic_id', $data['topicId'])
                    ->value('order');
                $SessionOrderDecision = (int) Session::where('id', $data['sessionId'])
                    ->value('order');

                $data['order'] = $decisionOrderAgenda . '/' . $SessionOrderDecision;

                $agendaOrderInSession++;
                $data['agenda_order'] = (int) $agendaOrderInSession;

                SessionDecision::updateOrCreate(
                    [
                        'agenda_id' => $data['agendaId'],
                        'topic_id' => $data['topicId'],
                        'session_id' => $data['sessionId'],
                    ],
                    [
                        'decision' => $data['decision'],
                        'order' => $data['order'],
                        'agenda_order' => $data['agenda_order'],
                        'decisionChoice' => $data['decisionChoice'],
                        // Add other fields you want to update/create here
                    ]
                );
            }

            return response()->json(['message' => 'Decisions saved successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to save form data', 'message' => $e->getMessage()], 500);
        }
    }

    private function isEmptyFormData($data)
    {
        // Check if all fields in $data are null or empty
        return empty($data['agendaId']) &&
            empty($data['topicId']) &&
            empty($data['sessionId']) &&
            empty($data['decision']);
    }
    public function fetchVoiting($session_id, $locale = null)
    {
        $session = Session::findOrFail($session_id);
        $decisions = SessionDecision::where('session_decisions.session_id', $session_id)
            ->join('topics', 'session_decisions.topic_id', '=', 'topics.id') // Join with topics table
            ->join('topics as main_topic', 'main_topic.id', '=', 'topics.main_topic_id')
            ->orderBy(DB::raw('CAST(main_topic.order AS SIGNED)'), 'asc') // Ensure main_topic.order is numeric
            ->orderBy(DB::raw('CAST(topics.order AS SIGNED)'), 'asc')     // Ensure topics.order is numeric
            ->orderBy('session_decisions.agenda_id', 'asc')              // Ascending order of agenda_id
            ->select('session_decisions.*', 'topics.main_topic_id') // Select fields from both tables
            ->get();

        if ($decisions->isEmpty()) {
            // Return a JSON response with a message and 404 status code if there are no decisions
            return response()->json(['message' => 'No decisions available for this session'], 404);
        }
        $topics = Topic::pluck('title', 'id'); // Get a collection of topic titles keyed by topic id


        $decisionData = $decisions->map(function ($decision) use ($topics, $session) {
            $topicTitileFormate = $this->initializeTopicsWithoutDecision($session);

            $users = User::whereIn('id', function ($query) use ($decision) {
                $query->select('user_id')
                    ->from('session_user')
                    ->where('session_id', $decision->session_id)
                    ->union(
                        DB::table('session_emails')
                            ->select('user_id')
                            ->where('session_id', $decision->session_id)
                    );
            })->whereIn('id', function ($query) use ($decision) {
                $query->select('user_id')
                    ->from('session_attendance_invites')
                    ->where('session_id', $decision->session_id)
                    ->where('actual_status', 1);
            })->get();
            $userVotes = $users->map(function ($user) use ($decision) {
                $vote = UserDecisionVote::where('user_id', $user->id)
                    ->where('decision_id', $decision->id)
                    ->first(); // Get the first vote status if it exists

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'vote' => $vote ? $vote->status : null, // Get the vote status if it exists
                ];
            })->toArray();

            return [
                'decision_id' => $decision->id,
                'session_id' => $decision->session_id,
                'decision' => $decision->decision,
                // 'TopicTitle' => $topics->get($decision->topic_id), // Get the topic title
                'TopicTitle' => $topicTitileFormate[$decision->agenda_id],
                'users' => $userVotes,
            ];
        })->toArray();


        $decisions = SessionDecision::where('session_id', $session_id)->get();

        foreach ($decisions as $decision) {
            $decisionApproval = $decision->approval;
        };

        // Return the view with the decision data
        return view('filament.resources.session-departemtn-resource.pages.voiting-list', compact('decisionData', 'decisionApproval', 'locale'));
    }

    public function fetchVoitingSingle($session_id, $locale = null)
    {
        $session = Session::findOrFail($session_id);

        $decisions = SessionDecision::where('session_decisions.session_id', $session_id)
            ->join('topics', 'session_decisions.topic_id', '=', 'topics.id') // Join with topics table
            ->join('topics as main_topic', 'main_topic.id', '=', 'topics.main_topic_id')
            ->orderBy(DB::raw('CAST(main_topic.order AS SIGNED)'), 'asc') // Ensure main_topic.order is numeric
            ->orderBy(DB::raw('CAST(topics.order AS SIGNED)'), 'asc')     // Ensure topics.order is numeric
            ->orderBy('session_decisions.agenda_id', 'asc')              // Ascending order of agenda_id
            ->select('session_decisions.*', 'topics.main_topic_id') // Select fields from both tables
            ->get();

        if ($decisions->isEmpty()) {
            // Return a JSON response with a message and 404 status code if there are no decisions
            return response()->json(['message' => 'No decisions available for this session'], 404);
        }
        $topics = Topic::pluck('title', 'id'); // Get a collection of topic titles keyed by topic id

        $decisionData = $decisions->map(function ($decision) use ($topics, $session) {
            $topicTitileFormate = $this->initializeTopicsWithoutDecision($session);

            $users = User::whereIn('id', function ($query) use ($decision) {
                $query->select('user_id')
                    ->from('session_user')
                    ->where('session_id', $decision->session_id)
                    ->where('user_id', auth()->user()->id)
                    ->union(
                        DB::table('session_emails')
                            ->select('user_id')
                            ->where('session_id', $decision->session_id)
                            ->where('user_id', auth()->user()->id)
                    );
            })->get();

            $userVotes = $users->map(function ($user) use ($decision) {
                $vote = UserDecisionVote::where('user_id', $user->id)
                    ->where('decision_id', $decision->id)
                    ->first(); // Get the first vote status if it exists

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'vote' => $vote ? $vote->status : null, // Get the vote status if it exists
                ];
            })->toArray();


            return [
                'decision_id' => $decision->id,
                'session_id' => $decision->session_id,
                'decision' => $decision->decision,
                // 'TopicTitle' => $topics->get($decision->topic_id), // Get the topic title
                'TopicTitle' => $topicTitileFormate[$decision->agenda_id],
                'users' => $userVotes,
            ];
        })->toArray();

        $decisions = SessionDecision::where('session_id', $session_id)->get();
        // $decisionApproval = [];
        foreach ($decisions as $decision) {
            $decisionApproval = $decision->approval;
        };

        // Return the view with the decision data
        return view('filament.resources.session-departemtn-resource.pages.voiting-list-single', compact('decisionData', 'decisionApproval', 'locale'));
    }


    public function saveVoiting(Request $request)
    {
        // Check if the voteType is 2
        if ($request->voteType == 2) {
            // Handle the voting process for voteType == 2
            $data = $request->all();  // Retrieve all data from the request


            $sessionUsers = User::whereIn('id', function ($query) use ($data) {
                $query->select('user_id')
                    ->from('session_attendance_invites')
                    ->where('session_id', $data['session_id'])
                    ->where('actual_status', 1);
            })->get();


            // Loop through each decision in the request
            foreach ($data['decision'] as $decisionId => $decisionContent) {
                // Find the SessionDecision model by the decision ID
                $decision = SessionDecision::find($decisionId);
                if ($decision) {
                    // Update the decision content
                    $decision->decision = $decisionContent;
                    $decision->save();  // Save the updated decision content
                }

                // Check if there are voting statuses for the current decision
                if (isset($data['voiting'][$decisionId])) {
                    $votingStatuses = $data['voiting'][$decisionId];  // Retrieve voting statuses for the decision

                    // Update or create the UserDecisionVote records for each user
                    foreach ($votingStatuses as $userId => $voitingStatus) {
                        UserDecisionVote::updateOrCreate(
                            ['user_id' => $userId, 'decision_id' => $decisionId],  // Search for the record by user_id and decision_id
                            ['status' => $voitingStatus]  // Update or create the record with the new status
                        );
                    }

                    // Calculate the total number of votes
                    $totalVotes = count($votingStatuses);

                    // Count the number of votes with status 1 (accept)
                    $acceptAll = count(array_filter($votingStatuses, fn($status) => $status == 1));

                    // Count the number of votes with status 2 (refuse)
                    $refuseAll = count(array_filter($votingStatuses, fn($status) => $status == 2));

                    // Determine the decision status based on the voting results
                    if ($acceptAll == $totalVotes) {
                        // If all votes are 'accept', set decision_status to 1
                        $decision->decision_status = 1;
                    } elseif ($refuseAll == $totalVotes) {
                        // If all votes are 'refuse', set decision_status to 2
                        $decision->decision_status = 2;
                    } elseif ($acceptAll > $totalVotes / 2) {
                        // If more than half of the votes are 'accept', set decision_status to 3
                        $decision->decision_status = 3;
                    } elseif ($refuseAll > $totalVotes / 2) {
                        // If more than half of the votes are 'refuse', set decision_status to 4
                        $decision->decision_status = 4;
                    } elseif ($acceptAll == $refuseAll) {
                        // If the number of 'accept' votes equals the number of 'refuse' votes, set decision_status to 5
                        $decision->decision_status = 5;
                    }

                    $decision->save();  // Save the updated decision status
                }
            }

            $appURL = env('APP_URL');

            // Build the URL dynamically
            $url = $appURL . '/admin/session-departemtns/' . $data['session_id'] . '/start';

            Notification::make()
                ->title('برجاء التوقيع على محضر الجلسة')
                ->actions([
                    NotificationsAction::make('view')
                        ->label('عرض الجلسة')
                        ->button()
                        ->url($url, shouldOpenInNewTab: true)
                        ->markAsRead(),
                ])
                ->sendToDatabase($sessionUsers);
        } elseif ($request->voteType == 1) {
            // Handle the voting process for voteType == 1
            $data = $request->all();  // Retrieve all data from the request

            // Loop through each decision in the request
            foreach ($data['decision'] as $decisionId => $decisionContent) {
                // Find the SessionDecision model by the decision ID
                $decision = SessionDecision::find($decisionId);
                if ($decision) {
                    // Update the decision content
                    $decision->decision = $decisionContent;
                    $decision->save();  // Save the updated decision content
                }

                // Check if there are voting statuses for the current decision
                if (isset($data['voiting'][$decisionId])) {
                    $votingStatuses = $data['voiting'][$decisionId];  // Retrieve voting statuses for the decision

                    // Get the ID of the currently authenticated user
                    $authUserId = Auth::id();

                    // Update or create the UserDecisionVote record for the authenticated user
                    if (isset($votingStatuses[$authUserId])) {
                        UserDecisionVote::updateOrCreate(
                            ['user_id' => $authUserId, 'decision_id' => $decisionId],  // Search for the record by user_id and decision_id
                            ['status' => $votingStatuses[$authUserId]]  // Update or create the record with the authenticated user's status
                        );
                    }

                    // Update or create the UserDecisionVote records for each other user
                    foreach ($votingStatuses as $userId => $voitingStatus) {
                        if ($userId !== $authUserId) {  // Skip the authenticated user
                            UserDecisionVote::updateOrCreate(
                                ['user_id' => $userId, 'decision_id' => $decisionId],  // Search for the record by user_id and decision_id
                                ['status' => $voitingStatus]  // Update or create the record with the new status
                            );
                        }
                    }

                    // Calculate the total number of votes
                    $totalVotes = UserDecisionVote::where('decision_id', $decisionId)->count();

                    // Count the number of votes with status 1 (accept)
                    $acceptAll = UserDecisionVote::where('decision_id', $decisionId)->where('status', 1)->count();

                    // Count the number of votes with status 2 (refuse)
                    $refuseAll = UserDecisionVote::where('decision_id', $decisionId)->where('status', 2)->count();

                    // Determine the decision status based on the voting results
                    if ($acceptAll == $totalVotes) {
                        // If all votes are 'accept', set decision_status to 1
                        $decision->decision_status = 1;
                    } elseif ($refuseAll == $totalVotes) {
                        // If all votes are 'refuse', set decision_status to 2
                        $decision->decision_status = 2;
                    } elseif ($acceptAll > $totalVotes / 2) {
                        // If more than half of the votes are 'accept', set decision_status to 3
                        $decision->decision_status = 3;
                    } elseif ($refuseAll > $totalVotes / 2) {
                        // If more than half of the votes are 'refuse', set decision_status to 4
                        $decision->decision_status = 4;
                    } elseif ($acceptAll == $refuseAll) {
                        // If the number of 'accept' votes equals the number of 'refuse' votes, set decision_status to 5
                        $decision->decision_status = 5;
                    }

                    $decision->save();  // Save the updated decision status
                }
            }
        }

        // Return a success response
        return response()->json(['message' => 'Voting data saved successfully']);  // Send a JSON response indicating success
    }

    public function viewRecord(Request $request, $locale = null)
    {
        // Retrieve recordId from the request
        $recordId = $request->input('recordId');
        // Find the session decisions based on session_id
        $sessionDecisions = SessionDecision::where('session_id', $recordId)->get();

        // If no session decisions are found, return an error response
        if ($sessionDecisions->isEmpty()) {
            if ($locale == 'ar') {
                $errorMessage = 'لم يتم العثور على قرارات لهذه الجلسة.';
            } else {
                $errorMessage = 'No decisions found for this session.';
            }
            return response()->json([
                'conditionMet' => false,
                // 'errorMessage' => 'No session decisions found for this session.'
                'errorMessage' => $errorMessage
            ]);
        }

        // Get the IDs of the session decisions
        $decisionIds = $sessionDecisions->pluck('id');

        // Count the number of user decision votes for these decision IDs
        $voteCounts = UserDecisionVote::whereIn('decision_id', $decisionIds)->count();

        // Count the number of invites for the session
        $invitesCounts = SessionAttendanceInvite::where('session_id', $recordId)->where('actual_status', 1)->count();
        $countofTopic = SessionTopic::where('session_id', $recordId)->count();

        // Define the condition based on the vote count and invite count
        $conditionMet = $voteCounts >= ($invitesCounts * $countofTopic);

        // Check if the vote count is less than twice the invite count
        if ($voteCounts < ($invitesCounts * $countofTopic)) {
            if ($locale == 'ar') {
                $errorMessage = 'هناك بعض الأشخاص الذين لم يصوتوا.';
            } else {
                $errorMessage = 'There are some people who have not voted.';
            }
            return response()->json([
                'conditionMet' => false,
                // 'errorMessage' => 'There are some people who have not voted.',
                'errorMessage' => $errorMessage,
                'voteCount' => $voteCounts,
                'invitesCount' => $invitesCounts
            ]);
        }

        return response()->json([
            'conditionMet' => true,
            'errorMessage' => '',
            'voteCount' => $voteCounts,
            'invitesCount' => $invitesCounts,
            'redirectUrl' => route('session-report', ['recordId' => $recordId]) // Add redirect URL to the response
        ]);
    }

    // public function getPages($recordId)
    // {
    //     $host = request()->getSchemeAndHttpHost();
    //     $url = $host . '/councils/public/admin/session-departemtns/session-report/' . $recordId . '';
    //     return redirect()->away($url);
    // }
    public function getPages($recordId)
    {
        $appURL = env('APP_URL');

        // Build the URL dynamically
        $url = $appURL . '/admin/session-departemtns/session-report/' . $recordId . '';

        return redirect()->away($url);
    }

    public function decisionApproval(Request $request, $sessionId)
    {
        // Check for attendance records where 'apply_signiture' is 0
        $unapprovedUsers = SessionAttendanceInvite::where('session_id', $sessionId)
            ->where('actual_status', 1) // Attendance is confirmed
            ->where('apply_signiture', 0) // Signatures are not applied
            ->where('user_id', '!=', auth()->user()->id)
            ->get();

        // If there are unapproved users, return an error
        if ($unapprovedUsers->isNotEmpty()) {
            $userNames = $unapprovedUsers->map(function ($attendance) {
                return \App\Models\User::find($attendance->user_id)->name; // Get user name from users table
            });

            return response()->json([
                'error' => 'لا يمكن إتمام الإجراء. لم يقم المستخدمون التاليون الموافقة أو الرفض بالتوقيع: ' . implode(', ', $userNames->toArray())
            ], 422); // 422 Unprocessable Entity

        }

        // Validation for approval
        $validator = Validator::make($request->all(), [
            'approval' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $approvalDecision = (int) $request->input('approval');
        $sessionDecisions = SessionDecision::where('session_id', $sessionId)->get();
        foreach ($sessionDecisions as $decision) {
            $decisionId = $decision->id;
            if ($request->has('dess') && isset($request->dess[$decisionId])) {
                $decision->update([
                    'approval' => $approvalDecision,
                    'rejected_reason' => $request->rejectReason,
                    'decision_status' => $request->dess[$decisionId],
                    'updated_at' => now()
                ]);
            } else {
                $decision->update([
                    'approval' => $approvalDecision,
                    'rejected_reason' => $request->rejectReason,
                    'updated_at' => now()
                ]);
            }
        }
        $session = Session::find($sessionId);

        $session->update([
            'actual_end_time' => now(),
        ]);

        $appURL = env('APP_URL');

        // Build the URL dynamically
        $url = $appURL . '/admin/session-departemtns/' . $session->id;

        return response()->json(['success' => 'Decision approval has been saved successfully.', 'redirect_url' => $url], 200);
    }


    public function startStopwatch(Request $request)
    {
        $sessionId = $request->input('sessionId');
        $session = Session::find($sessionId);
        $sessionDepartmentName = Department::where('id', $session->department_id)->value('ar_name');

        $sessionInvitations = SessionUser::where('session_id', $sessionId)->pluck('user_id')->toArray();
        $sessionEmailInvitations = SessionEmail::where('session_id', $sessionId)->pluck('user_id')->toArray();

        $sessionUsers = array_merge($sessionInvitations, $sessionEmailInvitations);
        $usersReciveNotification = User::whereIn('id', $sessionUsers)
            ->whereNotIn('id', [$session->created_by]) // don't take the secretary of department
            ->get();

        if ($session) {
            $session->actual_start_time = now();
            $session->save();

            $appURL = env('APP_URL');

            // Build the URL dynamically
            $url = $appURL . '/admin/session-departemtns/' . $sessionId . '/start';

            Notification::make()
                ->title('تم بدأ جلسة مجلس القسم')
                ->body('قسم: ' . $sessionDepartmentName . ' جلسة رقم: ' . $session->code)
                ->actions([
                    NotificationsAction::make('view')
                        ->label('عرض الجلسة')
                        ->button()
                        ->url($url, shouldOpenInNewTab: true)
                        ->markAsRead(),
                ])
                ->sendToDatabase($usersReciveNotification);


            return response()->json(['success' => true, 'message' => 'Start time updated successfully']);
        } else {
            return response()->json(['success' => false, 'message' => 'Session not found'], 404);
        }
    }

    //
    public function downloadPDF($sessionId, $content)
    {
        $session = Session::findOrFail($sessionId);
        $decisionApproval = SessionDecision::where('session_id', $sessionId)->first()->approval;
        $sessionEmailsUser = SessionEmail::where('session_id', $sessionId)->pluck('user_id')->toArray();
        $sessionUserIds = SessionUser::where('session_id', $sessionId)->pluck('user_id')->toArray();

        $sessionUsers = array_merge($sessionUserIds, $sessionEmailsUser);


        $sessionAttendance = SessionAttendanceInvite::where('session_attendance_invites.session_id', $sessionId)
            ->whereIn('user_id', $sessionUsers)
            ->join('users', 'users.id', '=', 'session_attendance_invites.user_id')
            ->join('positions', 'positions.id', '=', 'users.position_id')
            ->select(
                'users.name as userName',
                'users.signature as signature',
                'positions.ar_name as position',
                'session_attendance_invites.actual_status as attendance',
            )
            ->get();

        $topics = $this->initializeDecisions($session, $content);
        $members = $this->initializeMembers($session);

        $data = [
            'decisionApproval' => $decisionApproval,
            'session' => $session,
            'sessionAttendance' => $sessionAttendance,
            'topics' => $topics,
            'arabicOrder' => $this->arabicOrder(),
            'sessionOrder' => 'الجلسة ' . $this->sessionArabicOrdinal($session->order),
            'members' => $members,
        ];

        $code = $session->code;

        // Split the code into parts using "_"
        $parts = explode('_', $code);

        // Assign the parts to variables
        $yearCode = $parts[0]; // Before the first "_"
        $departmentCode = $parts[1]; // Between the first and second "_"
        $lastPart = $parts[2]; // After the second "_"

        $yearName = YearlyCalendar::where('code', $yearCode)->value('name');
        $departmentArName = Department::where('code', $departmentCode)->value('ar_name');

        $newSessionCode = "{$yearName}_{$departmentArName}_{$lastPart}";

        if ($content == "report") {
            // return view('pdf.session-department.report', compact('data'));
            $pdf = PDF::loadView('pdf.session-department.report', ['data' => $data])
                ->setPaper('a4', 'portrait'); // Example paper size and orientation
            return $pdf->stream('Report Session (' . $newSessionCode . ')');
        } elseif ($content == "topics") {
            // return view('pdf.session-department.topics', compact('data'));
            $pdf = PDF::loadView('pdf.session-department.topics', ['data' => $data])
                ->setPaper('a4', 'portrait'); // Example paper size and orientation
            return $pdf->stream('Session topics (' . $newSessionCode . ')');
        } elseif ($content == "coverLetter") {
            // return view('pdf.session-department.agendaCoverLetter', compact('data'));
            $pdf = PDF::loadView('pdf.session-department.agendaCoverLetter', ['data' => $data])
                ->setPaper('a4', 'portrait'); // Example paper size and orientation
            return $pdf->stream('Session topics (' . $newSessionCode . ')');
        }
    }

    public function applySigniture(Request $request)
    {
        // Ensure user and session ID are provided
        $attendance = SessionAttendanceInvite::where('user_id', auth()->user()->id)
            ->where('session_id', $request->session_id)
            ->first();

        if ($attendance) {
            // Update the `apply_signiture` status to 1 (applied)
            $attendance->apply_signiture = $request->apply_signature;
            $attendance->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function arabicOrder(): array
    {
        $ordinals = [
            1 => 'الأول',
            2 => 'الثاني',
            3 => 'الثالث',
            4 => 'الرابع',
            5 => 'الخامس',
            6 => 'السادس',
            7 => 'السابع',
            8 => 'الثامن',
            9 => 'التاسع',
            10 => 'العاشر',
            11 => 'الحادي عشر',
            12 => 'الثاني عشر',
            13 => 'الثالث عشر',
            14 => 'الرابع عشر',
            15 => 'الخامس عشر',
            16 => 'السادس عشر',
            17 => 'السابع عشر',
            18 => 'الثامن عشر',
            19 => 'التاسع عشر',
            20 => 'العشرون',
            21 => 'الحادي والعشرون',
            22 => 'الثاني والعشرون',
            23 => 'الثالث والعشرون',
            24 => 'الرابع والعشرون',
            25 => 'الخامس والعشرون',
            26 => 'السادس والعشرون',
            27 => 'السابع والعشرون',
            28 => 'الثامن والعشرون',
            29 => 'التاسع والعشرون',
            30 => 'الثلاثون',
        ];

        return $ordinals;
    }

    protected function initializeTopics($session)
    {
        // Retrieve topic_agenda_ids ordered by ascending order
        $agendas = SessionTopic::where('session_topics.session_id', $session->id)
            ->join('topics_agendas as agendas', 'agendas.id', '=', 'session_topics.topic_agenda_id')
            ->join('topics as sub_topic', 'sub_topic.id', '=', 'agendas.topic_id') // Corrected the join condition
            ->join('topics as main_topic', 'main_topic.id', '=', 'sub_topic.main_topic_id')
            ->orderByRaw('CAST(main_topic.order AS SIGNED) ASC') // Order by main_topic.order as integer
            ->orderByRaw('CAST(sub_topic.order AS SIGNED) ASC') // Order by sub_topic.order as integer
            ->orderBy('session_topics.topic_agenda_id', 'asc') // Then order by the topic_agenda_id
            ->pluck('session_topics.topic_agenda_id'); // Pluck only the topic_agenda_id

        // Retrieve topic_ids associated with the ordered topic_agenda_ids
        $topics = TopicAgenda::whereIn('topics_agendas.id', $agendas)
            ->join('topics', 'topics.id', '=', 'topics_agendas.topic_id')
            ->orderBy('topics.main_topic_id', 'asc')
            ->orderBy('topics.order', 'asc') // Order by the topic's order first
            ->orderBy('topic_id', 'asc')  // Ascending order of topic_agenda_id
            ->pluck('topics_agendas.topic_id');

        // Retrieve topics ordered by their ids
        $this->topics = Topic::whereIn('id', $topics)
            ->orderBy('main_topic_id', 'asc')
            ->orderBy('order', 'asc')  // Ascending order of topic ids
            ->pluck('title', 'id');

        return $this->topics;
    }
    public function initializeDecisions($session, $content)
    {
        $topicIds = $this->initializeTopics($session)->keys();

        $decisions = SessionDecision::where('session_decisions.session_id', $session->id)
            ->whereIn('session_decisions.topic_id', $topicIds)
            ->join('topics', 'session_decisions.topic_id', '=', 'topics.id') // Join with topics table
            ->join('topics as main_topic', 'main_topic.id', '=', 'topics.main_topic_id')
            ->orderByRaw('CAST(main_topic.order AS SIGNED) ASC') // Order by main_topic.order as integer
            ->orderByRaw('CAST(topics.order AS SIGNED) ASC') // Order by sub_topic.order as integer
            ->orderBy('session_decisions.agenda_id', 'asc')  // Ascending order of agenda_id
            // ->with('topic') // Eager load the topic relationship
            ->select('session_decisions.*', 'topics.main_topic_id', 'topics.title') // Select fields from both tables
            ->get();

        $x = 0;
        $decisionStatusMap = $this->getDecisionStatusMap();
        $theDecisions = $decisions->reduce(function ($carry, $decision) use ($decisionStatusMap, $session, $content, &$x) {
            $x++;
            // Step 1: Fetch the report template for the current decision
            $reportTemplate = $this->getReportTemplate($decision, $content);

            // Step 2: If no report template exists for this decision, skip this iteration and return the carry as is
            if (!$reportTemplate) {
                return $carry;
            }

            // Step 3: Retrieve the main topic title using the main_topic_id of the current decision
            $mainTopicTitle = Topic::where('id', $decision->main_topic_id)
                ->orderBy('order', 'asc') // Smallest order first
                ->value('title');

            // Step 4: Get replacement values for the placeholders in the report template
            $replacements = $this->getDecisionReplacements($decision, $session, $reportTemplate, $x);

            // Step 5: Replace the placeholders in the template with the actual values
            $content = $this->replacePlaceholders($reportTemplate, $replacements);

            // Step 6: Retrieve the title of the topic for the decision
            $topicTitle = $this->gettitleName($decision);

            // Step 7: Set the approval status of the decision (this might be used later)
            $this->decisionApproval = $decision->approval;

            // Step 8: Check if the main topic title already exists in the $carry array.
            // If it doesn't, initialize it with an empty 'details' array.
            if (!isset($carry[$mainTopicTitle])) {
                $carry[$mainTopicTitle] = [
                    'details' => [],  // Initialize the 'details' array to store subtopics
                ];
            }

            // Step 9: Append the current decision's topic details to the 'details' array of the corresponding main topic.
            $carry[$mainTopicTitle]['details'][] = [
                'report_contents' => $content,  // The content of the report (after placeholder replacement)
                'topic_title' => $topicTitle,   // The title of the topic for this decision
            ];

            // Step 10: Return the updated $carry array to be used in the next iteration
            return $carry;
        }, []); // The second argument is an empty array, which acts as the initial value of $carry

        return $theDecisions;
    }
    public function gettitleName($decision)
    {
        $session = Session::findOrFail($decision->session_id);
        $topicFormate = SessionTopic::where('session_id', $decision->session_id)
            ->where('topic_agenda_id', $decision->agenda_id)
            ->value('topic_formate');

        if (!is_null($topicFormate) && $topicFormate != "<p><br></p>") {
            $replacements = $this->getDecisionReplacements($decision, $session, $topicFormate);

            $content = $this->replacePlaceholders($topicFormate, $replacements);

            $topicTitle = $content;
        } else {
            $topicTitle = $decision->topic->title;
        }

        return $topicTitle;
    }

    public function getReportTemplate($decision, $content)
    {
        if ($content === "coverLetter") {
            // Fetch the report template content
            $tempId = SessionTopic::where('session_id', $decision->session_id)
                ->where('topic_agenda_id', $decision->agenda_id)
                ->value('cover_letter_template_content');
        } else {
            // Fetch the report template content
            $tempId = SessionTopic::where('session_id', $decision->session_id)
                ->where('topic_agenda_id', $decision->agenda_id)
                ->value('report_template_content');
        }
        // If the template exists, clean it by decoding HTML entities and removing unnecessary spaces
        if ($tempId) {
            // Decode HTML entities
            $tempId = html_entity_decode($tempId, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            // Remove any extra spaces or non-breaking spaces
            $tempId = preg_replace('/\s+/', ' ', $tempId);
            $tempId = str_replace("\xC2\xA0", ' ', $tempId); // Handle non-breaking spaces directly

            // Trim the result to remove any leading or trailing spaces
            $tempId = trim($tempId);
        }
        return $tempId;
    }


    public function getDecisionReplacements($decision, $session, $reportTemplate, $topicCount = null)
    {
        // Split the code into parts using "_"
        $parts = explode('_', $session->code);

        // Assign the parts to variables
        $yearCode = $parts[0]; // Before the first "_"
        $departmentCode = $parts[1]; // Between the first and second "_"
        // $lastPart = $parts[2]; // After the second "_"
        $sessionOrder = 'الجلسة ' . self::sessionArabicOrdinal($session->order);
        $departmentArName = $decision->session->department->ar_name;
        $facultyArName = $decision->session->department->faculty->ar_name;

        // $newSessionCode = "{$yearName}_{$departmentArName}_{$lastPart}";
        $newSessionCode = "{$yearCode}_{$departmentCode}_{$sessionOrder}";

        $userId = TopicAgenda::where('id', $decision->agenda_id)->value('created_by');
        $topicTitle = Topic::where('id', $decision->topic_id)->value('title');
        $topicIds = is_array($decision->topic_id) ? $decision->topic_id : [$decision->topic_id];
        $username = User::where('id', $userId)->value('name');

        // Fetch content and ensure it is properly formatted as an array
        $topicagendacontentform = AgandesTopicForm::where('agenda_id', $decision->agenda_id)
            ->whereIn('topic_id', $topicIds)
            ->pluck('content')
            ->toArray();

        // Combine all content into a single array of decoded JSON objects
        $decodedContents = [];
        foreach ($topicagendacontentform as $jsonString) {
            // Check if the element is a string and contains JSON
            if (is_string($jsonString)) {
                $decoded = json_decode($jsonString, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $decodedContents = array_merge($decodedContents, $decoded);
                } else {
                    // Log or handle invalid JSON
                    return ['error' => 'Invalid JSON content found.'];
                }
            } elseif (is_array($jsonString)) {
                // If it's already an array, just merge it
                $decodedContents = array_merge($decodedContents, $jsonString);
            } else {
                // Handle the case where $jsonString is neither a string nor an array
                return ['error' => 'Unexpected data type encountered.'];
            }
        }
        // $facultyDean = User::where('id',$decision->session->department->faculty->facultyDean->user_id)->value('name') ?? "";
        $facultyDean = User::where('faculty_id', $decision->session->department->faculty->id)->value('name') ?? "";

        $newDecisionNumber = $decision->session->order . '/' . $topicCount;
        // dd($newDecisionNumber);

        // Extract all placeholders within curly braces
        preg_match_all('/\{(.*?)\}/', $reportTemplate, $matches);

        $placeholders = $matches[1];

        $actualStartDateTime = Carbon::parse($session->actual_start_time);
        // Initialize the replacements array
        $replacements = [
            // '{session_number}' => $session->code,
            '{session_number}' => $session->order,
            '{session_number_as_word}' => $sessionOrder,
            '{department_name}' => $departmentArName,
            '{faculty_name}' => $facultyArName,
            '{name_of_topic}' => $topicTitle ?? '',
            // '{number_of_topic}' => $topicCount,
            '{number_of_topic}' => $decision->agenda_order,
            '{acadimic_year}' => $decision->session->year->name ?? '',
            // '{deescion_number}' => $decision->order ?? '',
            '{deescion_number}' => $newDecisionNumber ?? '',
            '{vote}' => $this->getDecisionStatusMap()[$decision->decision_status] ?? 'حالة غير معروفة',
            '{vote_type}' => $this->getDecisionTypeStatusMap()[$decision->decision_status] ?? 'حالة غير معروفة',
            '{decision}' => $decision->decisionChoice ?? '',
            '{justification}' => $decision->decision ?? '',
            '{uploader}' => $username,
            "{department_session_hijri_date}" => Hijri::DateIndicDigits('d-m-Y', $actualStartDateTime->format('d-m-Y')) ?? "",
            "{department_session_date}" => $actualStartDateTime->format('d-m-Y') ?? "",
            "{session_order}" => 'الجلسة ' . $this->sessionArabicOrdinal($session->order) ?? "",
            "{session_order_as_number}" => $session->order ?? "",
            "{session_department_decision}" => $decision->decisionChoice ?? "",
            // "{session_department_decision_number}" => $decision->order ?? "",
            "{session_department_decision_number}" => $newDecisionNumber ?? '',
            "{session_department_justification}" => $decision->decision ?? "",
            "{faculty_dean}" => $facultyDean ?? "",
            // "{faculty_session_hijri_date}" => "",
            // "{faculty_session_date}" => "",
        ];

        // Check if $decodedContents is an array before looping
        if (is_array($decodedContents)) {

            // Search in the decoded content for each placeholder and add it to the replacements
            foreach ($placeholders as $placeholder) {
                foreach ($placeholders as $placeholder) {
                    foreach ($decodedContents as $formField) {

                        $selectableTypes = ['select', 'checkbox-group', 'radio-group'];

                        if (in_array($formField['type'], $selectableTypes)) {
                            $values = $formField['values'];
                            $selectedLabels = [];

                            foreach ($values as $ty) {
                                if (isset($ty['selected']) && $ty['selected'] === true) {
                                    // Collect selected labels
                                    $selectedLabels[] = $ty['label'] ?? '';
                                }
                            }

                            // Implode selected labels into a single string, separated by commas
                            $formField['value'] = implode(', ', $selectedLabels);

                            // Make sure 'label' is set, if not, use the existing label
                            $formField['label'] = $formField['label'] ?? '';

                            if (isset($formField['label']) && $formField['label'] === $placeholder) {
                                // Set the replacement value with the imploded selected values
                                $replacements['{' . $placeholder . '}'] = $formField['value'] ?? '';
                            }
                        } else {
                            if (isset($formField['label']) && $formField['label'] === $placeholder) {
                                $replacements['{' . $placeholder . '}'] = $formField['value'] ?? '';
                                break;
                            }
                        }
                    }
                }
            }
        } else {
            $replacements['error'] = 'Decoded content is not an array.';
        }

        return $replacements;
    }
    public function sessionArabicOrdinal($number)
    {
        $ordinals = [
            1 => 'الأولى',
            2 => 'الثانية',
            3 => 'الثالثة',
            4 => 'الرابعة',
            5 => 'الخامسة',
            6 => 'السادسة',
            7 => 'السابعة',
            8 => 'الثامنة',
            9 => 'التاسعة',
            10 => 'العاشرة',
            11 => 'الحادية عشر',
            12 => 'الثانية عشر',
            13 => 'الثالثة عشر',
            14 => 'الرابعة عشر',
            15 => 'الخامسة عشر',
            16 => 'السادسة عشر',
            17 => 'السابعة عشر',
            18 => 'الثامنة عشر',
            19 => 'التاسعة عشر',
            20 => 'العشرون',
            21 => 'الحادية والعشرون',
            22 => 'الثانية والعشرون',
            23 => 'الثالثة والعشرون',
            24 => 'الرابعة والعشرون',
            25 => 'الخامسة والعشرون',
            26 => 'السادسة والعشرون',
            27 => 'السابعة والعشرون',
            28 => 'الثامنة والعشرون',
            29 => 'التاسعة والعشرون',
            30 => 'الثلاثون',
            31 => 'الحادية والثلاثون',
            32 => 'الثانية والثلاثون',
            33 => 'الثالثة والثلاثون',
            34 => 'الرابعة والثلاثون',
            35 => 'الخامسة والثلاثون',
            36 => 'السادسة والثلاثون',
            37 => 'السابعة والثلاثون',
            38 => 'الثامنة والثلاثون',
            39 => 'التاسعة والثلاثون',
            40 => 'الأربعون',
            41 => 'الحادية والأربعون',
            42 => 'الثانية والأربعون',
            43 => 'الثالثة والأربعون',
            44 => 'الرابعة والأربعون',
            45 => 'الخامسة والأربعون',
            46 => 'السادسة والأربعون',
            47 => 'السابعة والأربعون',
            48 => 'الثامنة والأربعون',
            49 => 'التاسعة والأربعون',
            50 => 'الخمسون',
            51 => 'الحادية والخمسون',
            52 => 'الثانية والخمسون',
            53 => 'الثالثة والخمسون',
            54 => 'الرابعة والخمسون',
            55 => 'الخامسة والخمسون',
            56 => 'السادسة والخمسون',
            57 => 'السابعة والخمسون',
            58 => 'الثامنة والخمسون',
            59 => 'التاسعة والخمسون',
            60 => 'الستون',
        ];

        return $ordinals[$number] ?? $number;
    }


    public function replacePlaceholders($content, $replacements)
    {
        foreach ($replacements as $key => $value) {
            $content = str_replace($key, $value, $content);
        }
        return $content;
    }
    public function getDecisionStatusMap()
    {
        $map = [
            1 => 'موافقة',
            2 => 'رفض',
            3 => 'موافقة',
            4 => 'رفض',
            5 => 'تساوى',
        ];
        return $map;
    }
    public function getDecisionTypeStatusMap()
    {
        $map = [
            1 => 'بالاجماع',
            2 => 'بالاجماع',
            3 => 'بالاغلبية',
            4 => 'بالاغلبية',
            5 => 'ترك القرار لرئيس القسم',
        ];
        return $map;
    }
    private function initializeMembers($session)
    {
        $users = $this->getSessionUsers(session: $session);
        $positionsName = $this->getPositionsName();
        $attendanceStatuses = $this->getAttendanceStatuses();
        $locale = App::getLocale();
        $invitedMembers = [];
        $members = [];

        foreach ($users as $userId) {
            $actualStatus = SessionAttendanceInvite::where('session_id', $session->id)->where('user_id', $userId)->value('actual_status');
            $user = User::find($userId);
            $positionId = $user->position_id;
            $attendance = SessionAttendanceInvite::where('session_id', $session->id)
                ->where('user_id', $userId)->value('actual_status');
            $user = User::find($userId);
            // Users from SessionEmail should go to invitedMembers
            $positionId = User::where('id', $userId)->value('position_id');
            $attendance = SessionAttendanceInvite::where('session_id', $session->id)
                ->where('user_id', $userId)->first();
            $signature = 'غائب'; // Default message for absent users
            if ($positionId == 3) {
                $signature = $user->signature;
            } elseif ($attendance) {
                if ($attendance->apply_signiture == 1) {
                    // Signature applied
                    $signature = User::find($userId)->signature;
                } elseif ($attendance->apply_signiture == 2) {
                    // Signature rejected
                    $signature = 'رفض المستخدم التوقيع';
                }
            }


            $memberData = [
                'user_id' => $userId,
                'name' => $user->name,
                // 'title' => $positionsName[$positionId][$locale] ?? 'بدون منصب',
                'title' => $positionsName[$positionId]['ar'] ?? 'بدون منصب',
                'position_id' => $positionId,
                'attendance' => $attendanceStatuses[$actualStatus] ?? 'حالة الحضور غير مغروفة',
                'signature' => $signature,
            ];

            if (SessionEmail::where('session_id', $session->id)->pluck('user_id')->contains($userId)) {
                $invitedMembers[] = $memberData;
            } else {
                $members[] = $memberData;
            }
        }

        // Sort the members array based on position_id priority
        usort($members, function ($a, $b) {
            $priority = [5, 4, 3, 2, 1];
            $posA = array_search($a['position_id'], $priority);
            $posB = array_search($b['position_id'], $priority);
            return $posA - $posB;
        });

        // Sort the invitedMembers array based on position_id priority
        usort($invitedMembers, function ($a, $b) {
            $priority = [5, 4, 3, 2, 1];
            $posA = array_search($a['position_id'], $priority);
            $posB = array_search($b['position_id'], $priority);
            return $posA - $posB;
        });

        // Return both arrays as a single structure
        return [
            'members' => $members,
            'invitedMembers' => $invitedMembers,
        ];
    }

    protected function getSessionUsers($session)
    {
        $sessionEmailsUser = SessionEmail::where('session_id', $session->id)->pluck('user_id')->toArray();
        $sessionUserIds = SessionUser::where('session_id', $session->id)->pluck('user_id')->toArray();
        return array_merge($sessionUserIds, $sessionEmailsUser);
    }

    protected function getPositionsName()
    {
        return [
            1 => ['en' => 'Academic Staff', 'ar' => 'عضو هيئة تدريس'],
            2 => ['en' => 'Secretary of the Department Council', 'ar' => 'أمين مجلس القسم'],
            3 => ['en' => 'Head of Department', 'ar' => 'رئيس القسم'],
            4 => ['en' => 'Secretary of the College Council', 'ar' => 'أمين مجلس الكلية'],
            5 => ['en' => 'Dean of the College', 'ar' => 'عميد الكلية'],
            6 => ['en' => 'Vice Rector for Educational Affairs', 'ar' => 'نائب رئيس الجامعة للشؤون التعليمية'],
            7 => ['en' => 'Prex', 'ar' => 'رئيس'],
        ];
    }

    protected function getAttendanceStatuses()
    {
        return [
            1 => 'حاضر',
            2 => 'غائب مع عذر',
            3 => 'غائب'
        ];
    }

    private function initializeTopicsWithoutDecision(Session $session)
    {
        $topicFormate = SessionTopic::where('session_topics.session_id', $session->id)
            ->join('topics_agendas as agenda', 'agenda.id', '=', 'session_topics.topic_agenda_id')
            ->join('topics as sub_topic', 'sub_topic.id', '=', 'agenda.topic_id')
            ->join('topics as main_topic', 'main_topic.id', '=', 'sub_topic.main_topic_id')
            ->select(
                'session_topics.topic_formate',
                'sub_topic.id as topic_id',
                'sub_topic.title as topic_title',
                'main_topic.title as main_topic',
                'agenda.id as agenda_id'
            )
            ->orderBy('main_topic.order', 'asc') // Order by main_topic.id in ascending order
            ->orderBy('agenda.id', 'asc')    // Order by agenda_id in ascending order
            ->orderBy('sub_topic.order', 'asc')  // Finally, order by sub_topic.id in ascending order (topic_id)
            ->get();

        // Sort and map the topics
        $formattedTopics = $topicFormate->sortBy('main_topic')->mapWithKeys(function ($topic) use ($session) {
            if (!is_null($topic->topic_formate) && $topic->topic_formate != "<p><br></p>") {
                $replacements = $this->getTopicReplacements($topic, $session, $topic->topic_formate);
                $content = $this->replacePlaceholders($topic->topic_formate, $replacements);
                $value = $content;
            } else {
                $value = $topic->topic_title;
            }

            // Use agenda_id as the key and $value as the value
            return [$topic->agenda_id => $value];
        })->toArray();

        // dd($formattedTopics);
        return $formattedTopics;
    }

    private function getTopicReplacements($topicData, $session, $reportTemplate)
    {
        // dd($topicData);
        $userId = TopicAgenda::where('id', $topicData->agenda_id)->value('created_by');
        $topicTitle = Topic::where('id', $topicData->topic_id)->value('title');
        $topicIds = is_array($topicData->topic_id) ? $topicData->topic_id : [$topicData->topic_id];
        $username = User::where('id', $userId)->value('name');

        // Fetch content and ensure it is properly formatted as an array
        $topicagendacontentform = AgandesTopicForm::where('agenda_id', $topicData->agenda_id)
            ->whereIn('topic_id', $topicIds)
            ->pluck('content')
            ->toArray();

        // Combine all content into a single array of decoded JSON objects
        $decodedContents = [];
        foreach ($topicagendacontentform as $jsonString) {
            // Check if the element is a string and contains JSON
            if (is_string($jsonString)) {
                $decoded = json_decode($jsonString, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $decodedContents = array_merge($decodedContents, $decoded);
                } else {
                    // Log or handle invalid JSON
                    return ['error' => 'Invalid JSON content found.'];
                }
            } elseif (is_array($jsonString)) {
                // If it's already an array, just merge it
                $decodedContents = array_merge($decodedContents, $jsonString);
            } else {
                // Handle the case where $jsonString is neither a string nor an array
                return ['error' => 'Unexpected data type encountered.'];
            }
        }


        // Extract all placeholders within curly braces
        preg_match_all('/\{(.*?)\}/', $reportTemplate, $matches);

        $placeholders = $matches[1];


        // Initialize the replacements array
        $replacements = [
            '{session_number}' => $session->code,
            '{department_name}' => $session->department->ar_name,
            '{faculty_name}' => $session->department->faculty->ar_name,
            '{name_of_topic}' => $topicTitle ?? '',
            // '{deescion_number}' => $decision->order ?? '',
            // '{vote}' => $this->getDecisionStatusMap()[$decision->decision_status] ?? 'حالة غير معروفة',
            // '{vote_type}' => $this->getDecisionTypeStatusMap()[$decision->decision_status] ?? 'حالة غير معروفة',
            // '{justification}' => $decision->decision ?? '',
            // '{decision}' => $decision->decisionChoice ?? '',
            '{uploader}' => $username,
        ];

        // Check if $decodedContents is an array before looping
        if (is_array($decodedContents)) {

            // Search in the decoded content for each placeholder and add it to the replacements
            foreach ($placeholders as $placeholder) {
                foreach ($placeholders as $placeholder) {
                    foreach ($decodedContents as $formField) {

                        $selectableTypes = ['select', 'checkbox-group', 'radio-group'];

                        if (in_array($formField['type'], $selectableTypes)) {
                            $values = $formField['values'];
                            $selectedLabels = [];

                            foreach ($values as $ty) {
                                if (isset($ty['selected']) && $ty['selected'] === true) {
                                    // Collect selected labels
                                    $selectedLabels[] = $ty['label'] ?? '';
                                }
                            }

                            // Implode selected labels into a single string, separated by commas
                            $formField['value'] = implode(', ', $selectedLabels);

                            // Make sure 'label' is set, if not, use the existing label
                            $formField['label'] = $formField['label'] ?? '';

                            if (isset($formField['label']) && $formField['label'] === $placeholder) {
                                // Set the replacement value with the imploded selected values
                                $replacements['{' . $placeholder . '}'] = $formField['value'] ?? '';
                            }
                        } else {
                            if (isset($formField['label']) && $formField['label'] === $placeholder) {
                                $replacements['{' . $placeholder . '}'] = $formField['value'] ?? '';
                                break;
                            }
                        }
                    }
                }
            }
        } else {
            $replacements['error'] = 'Decoded content is not an array.';
        }

        return $replacements;
    }
}
