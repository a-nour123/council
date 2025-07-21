<?php

namespace App\Http\Controllers;

use App\Models\AgandesTopicForm;
use App\Models\AgendaImage;
use App\Models\Axis;
use App\Models\CollegeCouncil;
use App\Models\Department;
use App\Models\Department_Council;
use App\Models\Faculty;
use App\Models\FacultyCouncil;
use App\Models\Topic;
use App\Models\TopicAgenda;
use App\Models\TopicAxis;
use App\Models\User;
use App\Models\YearlyCalendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotificationsAction;

class AgendaController extends Controller
{
    public function getFaculites($locale = null)
    {
        $councilDep = Department_Council::where('user_id', auth()->user()->id)->pluck('department_id')->toarray();

        if ($councilDep != Null) {
            $facultyId = Department::where('id', $councilDep['0'])->pluck('faculty_id');
        }

        if ($locale == 'ar') {
            // Fetch Arabic names
            if (auth()->user()->position_id == 1 || auth()->user()->position_id == 2 || auth()->user()->faculty_id != NULL) {
                $faculties = Faculty::select('id', 'ar_name as name')->where('id', $facultyId)->get();
            } else {
                // dd('!');
                $faculties = Faculty::select('id', 'ar_name as name')->get();
            }
        } else {
            // Fetch English names
            if (auth()->user()->position_id == 1 || auth()->user()->position_id == 2) {
                $faculties = Faculty::select('id', 'en_name as name')->where('id', auth()->user()->faculty_id)->get();
            } else {
                $faculties = Faculty::select('id', 'en_name as name')->get();
            }
        }
        return response()->json($faculties);
    }

    public function getDepartement(Request $request, $locale = null)
    {
        $departments = [];

        if ($locale == 'ar') {
            if (auth()->user()->position_id == 1 || auth()->user()->position_id == 2 || auth()->user()->position_id == 3) {
                // $councilDep = Department_Council::where('user_id', auth()->user()->id)->pluck('department_id');

                $departments = Department::where('faculty_id', $request->faculty)->where('id', auth()->user()->department_id)->select('id', 'ar_name as name')->get();
            } else {

                $departments = Department::where('faculty_id', $request->faculty)->select('id', 'ar_name as name')->get();
            }
        } else {
            if (auth()->user()->position_id == 1 || auth()->user()->position_id == 2 || auth()->user()->position_id == 3) {

                // $councilDep = Department_Council::where('user_id', auth()->user()->id)->pluck('department_id');
                $departments = Department::where('faculty_id', $request->faculty)->where('id', auth()->user()->department_id)->select('id', 'en_name as name')->get();
            } else {

                $departments = Department::where('faculty_id', $request->faculty)->select('id', 'en_name as name')->get();
            }
        }
        return response()->json($departments);
    }


    public function getTopic()
    {
        $topics = Topic::whereNull('main_topic_id')->select('id', 'title')->get();
        return response()->json($topics);
    }

    public function getSubTopic(Request $request)
    {
        // Check if the authenticated user is part of the College Council
        $check_user = FacultyCouncil::where('user_id', auth()->user()->id)->latest()->first();

        // Initialize the $subTopic variable as an empty array to avoid issues later
        $subTopic = [];

        // If the user is part of the College Council, fetch all subtopics for the given main_topic_id
        if ($check_user) {
            $subTopic = Topic::where('main_topic_id', $request->mainTopic)  // Filter by main_topic_id
                ->select('id', 'title')                          // Select only id and title fields
                ->get();                                           // Retrieve the results as a collection
        } else {
            // If the user is not part of the College Council, fetch only subtopics where classification_reference is 1 or 2
            $subTopic = Topic::where('main_topic_id', $request->mainTopic)      // Filter by main_topic_id
                ->whereIn('classification_reference', [1, 2])  // Filter by classification_reference values 1 or 2
                ->select('id', 'title')                        // Select only id and title fields
                ->get()                                          // Retrieve the results as a collection
                ->toArray();                                     // Convert the results to an array
        }

        // Return the subtopics as a JSON response
        return response()->json($subTopic);
    }


    public function AgendaTopicFormbuilder(Request $request)
    {
        $subTopicId = $request->query('sub_topic_id');  // Get the sub-topic ID from the request
        // Fetch the TopicAxis records and join with the Axes and Topics tables
        $AgendaTopicFormbuilder = TopicAxis::select('topics_axes.*', 'axes.title as axisTitle', 'topics_axes.id as topicId')
            ->join('axes', 'topics_axes.axis_id', '=', 'axes.id')
            ->join('topics', 'topics_axes.topic_id', '=', 'topics.id')  // Join the topics table
            ->where('topics_axes.topic_id', $subTopicId)
            ->get();
        return response()->json($AgendaTopicFormbuilder);
    }

    public function formBuilderAxsisTopic(Request $request)
    {

        $axsisForm = TopicAxis::where('topic_id', $request->subTopicId)
            ->join('axes', 'topics_axes.axis_id', '=', 'axes.id')
            ->join('topics', 'topics_axes.topic_id', '=', 'topics.id')
            ->select('topics_axes.field_data', 'topics_axes.axis_id', 'axes.title as axisTitle', 'topics.id as topicId')
            ->get();
        return response()->json($axsisForm);
    }

    public function store(Request $request)
    {
        // Decode and update the form data JSON strings
        $formDataArray = $request->formDataArray;
        $authenticatedUserName = auth()->user()->name;

        $formDataArray = is_string($formDataArray) ? json_decode($formDataArray, true) : $formDataArray;

        // Update the form data if the name is 'userName'
        foreach ($formDataArray as &$data) {
            $formData = json_decode($data['formData'], true);

            if (is_array($formData)) {
                foreach ($formData as &$field) {
                    if (isset($field['name']) && $field['name'] === 'userName') {
                        $field['value'] = $authenticatedUserName;
                    }
                }

                // Re-encode the updated form data back to JSON
                $data['formData'] = json_encode($formData);
            }
        }

        // Custom validation logic
        $validationErrors = [];
        foreach ($formDataArray as $data) {
            $formData = json_decode($data['formData'], true);
            if (is_array($formData)) {
                foreach ($formData as $field) {
                    $selectableTypes = ['select', 'checkbox-group', 'radio-group'];
                    if (in_array($field['type'], $selectableTypes)) {
                        $values = $field['values'];
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
                    } else {
                        if (isset($field['required']) && $field['required'] && (empty($field['value']) || $field['value'] === null)) {
                            $fieldName = $field['label'] ?? $field['name'];
                            $validationErrors[] = "حقل '{$fieldName}' مطلوب ويجب ان يحتوي على قيمة";
                        }
                    }
                }
            }
        }

        // If there are validation errors, return them
        if (!empty($validationErrors)) {
            return response()->json(['errors' => $validationErrors], 422);
        }

        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'faculty' => 'required|string',
            'department' => 'required|integer',
            'mainTopic' => 'required|integer',
            'subTopic' => 'required|integer',
            'uploadedPhotos.*' => 'file|max:10240', // 10MB max size, without restricting file types
            'formDataArray.*.formData' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (trim($value) === '[]') {
                        $fail('The form field must contain data.');
                    }
                },
            ],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Fetch the last order number
        $agendaLastOrder = (int) TopicAgenda::where('department_id', $request->department)
            ->where('faculty_id', $request->faculty)
            ->orderBy('order', 'desc')
            ->pluck('order')
            ->first() ?? 1;

        $data['order'] = $agendaLastOrder += 1;
        // Fetch the title of the topic
        $topicTitle = Topic::where('id', $request->subTopic)->pluck('title')->first();
        $EscalationAndClassification = Topic::where('id', $request->subTopic)->latest()->first();
        // Fetch the department name
        $departmentName = Department::where('id', $request->department)->pluck('code')->first();

        // Create the name by merging the order, topic title, and department name
        $name = $data['order'] . ' : ' . $topicTitle . ' / ' . $departmentName;

        // Generate code
        $department_id = $request->department;
        $departmentCode = Department::where('id', $department_id)->value('code');
        $latestTopicAgenda = TopicAgenda::where('code', 'LIKE', $departmentCode . '_%')->latest('id')->first();

        if ($latestTopicAgenda) {
            $latestCode = $latestTopicAgenda->code;
            $lastNumber = (int) substr($latestCode, strlen($departmentCode) + 1);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        $newCode = $departmentCode . '_' . $newNumber;

        $currentAcadimicYearId = YearlyCalendar::where('status', 1)->value('id'); // currrent active acadimic year
        // // Check if the authenticated user is part of the College Council
        // $check_user = FacultyCouncil::where('user_id', auth()->user()->id)->latest()->first();

        // // Determine the status based on whether the user is part of the College Council
        // $status = $check_user ? 1 : 0;

        // Create TopicAgenda
        $topicsAgenda = TopicAgenda::create([
            'code' => $newCode,
            'faculty_id' => $request->faculty,
            'department_id' => $request->department,
            'topic_id' => $request->subTopic,
            'yearly_calendar_id' => $currentAcadimicYearId,
            'created_by' => auth()->user()->id,
            'classification_reference' => $EscalationAndClassification->classification_reference,
            'escalation_authority' => $EscalationAndClassification->escalation_authority,
            'status' => 0,  // Set status based on $check_user
            'order' => $data['order'],
            'name' => $name,
        ]);

        // Loop through formDataArray and create AgandesTopicForm entries
        foreach ($formDataArray as $data) {
            AgandesTopicForm::create([
                'topic_id' => $data['topicId'],
                'agenda_id' => $topicsAgenda->id,
                'content' => $data['formData'],
            ]);
        }
        if ($request->hasFile('uploadedPhotos')) {
            $photos = $request->file('uploadedPhotos');
            if (is_array($photos)) {
                foreach ($photos as $photo) {
                    $filePath = $photo->store('agenda_images', 'public'); // Save the file and get the path
                    $fileName = $photo->getClientOriginalName(); // Get the original file name

                    AgendaImage::create([
                        'agenda_id' => $topicsAgenda->id,
                        'file_path' => $filePath, // Save the file path if needed
                        'file_name' => $fileName, // Save the file name in a separate column
                    ]);
                }
            } else {
                return response()->json(['error' => 'Uploaded photos must be an array'], 422);
            }
        }

        $agendaId = TopicAgenda::latest('id')->value('id');

        $DepartmentName = Department::where('id', $request->department)->value('ar_name');

        $headOfDepartment = Department_Council::where('department_id', $request->department)
            ->where('position_id', 3) // head of department position
            ->value('user_id');

        $appURL = env('APP_URL');
        // Build the URL dynamically
        $url = $appURL . '/admin/submit-topics/' . $agendaId . '/agenda-details';

        // Sending notification to the head of department
        if ($headOfDepartment != auth()->user()->id) {
            Notification::make()
                ->title('تم رفع طلب جديد')
                ->body('قسم' . ': ' . $DepartmentName . '<br> رافع الطلب: ' . auth()->user()->name)
                ->actions([
                    NotificationsAction::make('view')
                        ->label('عرض الطلب')
                        ->button()
                        ->url($url, shouldOpenInNewTab: true)
                        ->markAsRead(),
                ])
                ->sendToDatabase(User::where('id', $headOfDepartment)->get());
        }

        return response()->json(['message' => 'Form submitted successfully']);
    }



    public function update(Request $request)
    {
        // Decode the form data JSON string into an array
        foreach ($request->formDataArray as $data) {
            $formDataArray = json_decode($data, true); // $data is a JSON string, no need to access it as an array first

            // Custom validation logic
            $validationErrors = [];
            foreach ($formDataArray as $field) {
                if (isset($field['required']) && $field['required'] && (empty($field['value']) || $field['value'] === null)) {
                    $fieldName = $field['label'] ?? $field['name'];
                    $validationErrors[] = "حقل '{$fieldName}' مطلوب ويجب ان يحتوي على قيمة";
                }
            }

            // If there are validation errors, return them
            if (!empty($validationErrors)) {
                return response()->json(['errors' => $validationErrors], 422);
            }
        }

        $validator = Validator::make($request->all(), [
            'AgendaId' => 'required',
            'faculty' => 'required',
            'department' => 'required',
            'mainTopic' => 'required',
            'subTopic' => 'required',
            'uploadedPhotos.*' => 'file|max:10240', // 10MB max size, without restricting file types
            // 'notes' => ['nullable', 'required_if:status,3'], // Make 'notes' required only if 'status' is 3
            'notes' => ['nullable', 'required_if:status,2'], // Make 'notes' required only if 'status' is 3

        ], [
            // 'notes.required_if' => 'Please provide notes when the status is "Rejected with notes".',
            'notes.required_if' => 'Please provide rejection reason when the status is "Rejected".',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        // Find the existing TopicAgenda by AgendaId
        $topicsAgenda = TopicAgenda::find($request->AgendaId);

        if (!$topicsAgenda) {
            return response()->json(['message' => 'Agenda not found'], 404);
        }

        // Fetch the title of the topic
        $topicTitle = Topic::where('id', $request->subTopic)->pluck('title')->first();

        // Fetch the department name
        $departmentName = Department::where('id', $request->department)->pluck('code')->first();

        // Create the name by merging the order, topic title, and department name
        $name = $topicsAgenda->order . ' : ' . $topicTitle . ' / ' . $departmentName;

        // Update the TopicAgenda entry
        $topicsAgenda->update([
            'status' => $request->status,
            'note' => $request->notes,
            'faculty_id' => $request->faculty,
            'department_id' => $request->department,
            'topic_id' => (int) $request->subTopic,
            'name' => $name, // Add the name column here

        ]);

        $agendaIdForm = (int) $request->AgendaId;
        $topicId = (int) $request->subTopic;

        // Delete existing records that match the given conditions
        $deleteAgendaExist = AgandesTopicForm::where('agenda_id', $agendaIdForm)
            ->get();

        foreach ($deleteAgendaExist as $existingRecord) {
            $existingRecord->delete();
        }

        // Loop through the formDataArray and create or update records
        foreach ($request->formDataArray as $data) {
            // Debug: Check the original $data before decoding
            $data = json_decode($data, true);
            // Decode 'formData' if it's a JSON string
            if (is_string($data['formData'])) {
                $data['formData'] = json_decode($data['formData'], true); // Decode to an array
            }


            // Ensure other fields are set as integers
            $data['AgendaIdForm'] = (int) $request->AgendaId;
            $data['topicId'] = (int) $request->subTopic;
            $data['agendatopicIdForm'] = isset($data['agendatopicIdForm']) ? (int) $data['agendatopicIdForm'] : null;

            // Create or update records
            $agandesTopicForm = AgandesTopicForm::updateOrCreate(
                [
                    'id' => $data['agendatopicIdForm'],
                    'agenda_id' => $data['AgendaIdForm'],
                ],
                [
                    'topic_id' => $data['topicId'],
                    'content' => $data['formData'],
                ]
            );
        }

        // Ensure $request->existingPhotos is an array
        $existingPhotos = $request->existingPhotos ?? [];

        AgendaImage::where('agenda_id', $request->AgendaId)
            ->whereNotIn('id', $existingPhotos)
            ->delete();

        if ($request->hasFile('uploadedPhotos')) {
            $photos = $request->file('uploadedPhotos');
            if (is_array($photos)) {
                foreach ($photos as $photo) {
                    $filePath = $photo->store('agenda_images', 'public');
                    $fileName = $photo->getClientOriginalName(); // Get the original file name
                    AgendaImage::create([
                        'agenda_id' => $request->AgendaId,
                        'file_path' => $filePath,
                        'file_name' => $fileName, // Save the file name in a separate column
                    ]);
                }
            } else {
                return response()->json(['error' => 'Uploaded photos must be an array'], 422);
            }
        }

        $agendaUploader = TopicAgenda::where('id', $request->AgendaId)->value('created_by');

        if (auth()->user()->id != $agendaUploader) {
            if ($request->status == 1 || $request->status == 2 || $request->status == 3) {        // if status is accepted
                if ($request->status == 1) {
                    $notificationTitle = 'تم الموافقة على طلبك';
                    $notificationBody = null;
                }
                // if status is reject or rejectd with reason
                else if ($request->status == 2 || $request->status == 3) {
                    $notificationTitle = 'تم رفض طلبك';
                    $notificationBody = 'سبب الرفض: ' . $request->notes ?? null;
                }

                // sending notification for which create the
                Notification::make()
                    ->title($notificationTitle)
                    ->body('كود الطلب: ' . $topicsAgenda->code . '<br>' . $notificationBody)
                    ->sendToDatabase(User::where('id', $agendaUploader)->get());
            }
        }
        return response()->json(['message' => 'Form updated successfully']);
    }
    public function updateStatusAgenda(Request $request)
    {
        $topicsAgenda = TopicAgenda::find($request->AgendaId);

        $updates = 0;
        if ($request->status == 1) {
            $updates = 1;
        } else if ($request->status == 2) {
            $updates = 2;
        }

        $topicsAgenda->update([
            'status' => $request->status,
            'updates' => $updates,
            'note' => $request->notes,
        ]);

        return response()->json(['message' => 'Form updated successfully']);
    }
}
