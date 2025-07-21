<?php

namespace App\Http\Controllers;

use App\Models\AgandesTopicForm;
use App\Models\Axis;
use App\Models\ClassificationDecision;
use App\Models\ControlReport;
use App\Models\ControlReportFaculty;
use App\Models\SessionDecision;
use App\Models\SessionTopic;
use App\Models\Topic;
use App\Models\TopicAxis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TopicController extends Controller
{
    public function getTopics()
    {
        $topics = Topic::whereNull('main_topic_id')->get();

        return response()->json($topics);
    }
    public function getaxies()
    {
        $axies = Axis::get();
        return response()->json($axies);
    }
    public function createAxiesTopicForm(Request $request, $locale = null)
    {
        // $currentLocale = $locale;
        if ($request->maintopic == "Choose a Main Topic" || $request->maintopic == "اختر التصنيف الرئيسي") {

            $rules = [
                'title' => 'required|string|unique:topics',
                'order' => [
                    'required',
                    'integer',
                    'min:1', // Ensures the order is at least 1
                    Rule::unique('topics')->where(function ($query) {
                        $query->whereNull('main_topic_id'); // Check only main topics
                    }),
                ],
            ];

            if ($locale == 'ar') {
                $messages = [
                    'title.required' => 'العنوان مطلوب.',
                    'title.string' => 'يجب أن يكون العنوان نصاً.',
                    'title.unique' => 'العنوان يجب أن يكون فريداً.',

                    'order.required' => 'الترتيب مطلوب.',
                    'order.string' => 'يجب أن يكون الترتيب نصاً.',
                    'order.unique' => 'الترتيب يجب أن يكون فريداً.',
                    'order.min' => 'الترتيب يجب أن يكون اكبر من او يساوى 1.',
                ];
            } else {
                $messages = [
                    'title.required' => __('validation.required', ['attribute' => __('validation.attributes.title')]),
                    'title.string' => __('validation.string', ['attribute' => __('validation.attributes.title')]),
                    'title.unique' => __('validation.unique', ['attribute' => __('validation.attributes.title')]),

                    'order.required' => __('validation.required', ['attribute' => __('validation.attributes.order')]),
                    'order.string' => __('validation.string', ['attribute' => __('validation.attributes.order')]),
                    'order.unique' => __('validation.unique', ['attribute' => __('validation.attributes.order')]),
                ];
            }



            // Validate the request
            $validator = Validator::make($request->all(), $rules, $messages);

            // Handle validation failure
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422);
            }

            // If validation fails, return the validation errors
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $title = $request->input('title');
            $latestRecord = Topic::latest('id')->first();
            $latestCode = $latestRecord->code ?? 'tpc_0';
            // Extract the number part from the latest code
            $latestNumber = intval(preg_replace('/[^0-9]+/', '', $latestCode));
            // Increment the number
            $newNumber = $latestNumber + 1;
            // Generate the new code
            $newCode = 'tpc_' . $newNumber;

            $latestOrder = intval($latestRecord->order ?? '0');
            $neworder = $latestOrder + 1;
            $topic = new Topic();
            $topic->title = $title;
            $topic->code = $newCode;
            // $topic->order = $neworder;
            $topic->order = $request->order;
            // $topic->classification_reference = "$request->input('ClassificationReference')";
            // $topic->escalation_authority = $request->input('EscalationAuthority');

            $topic->save();
            return redirect()->back()->with('success', 'Form submitted successfully!');
        } else {
            $rules = [
                'title' => [
                    'required',
                    'string',
                    Rule::unique('topics')->where(function ($query) use ($request) {
                        return $query->where('main_topic_id', $request->maintopic);
                    }),
                ],
                'form' => [
                    'required',
                    function ($attribute, $value, $fail) use ($locale) {
                        $decodedValue = json_decode($value, true);
                        $isInvalid = $decodedValue === ['Choose an axie' => null] || $decodedValue === ['اختر المحور' => null];

                        if ($isInvalid) {
                            $message = $locale == 'ar'
                                ? 'يجب تحديد حقل النموذج.'
                                : 'The form field must be Selected.';
                            $fail($message);
                        }
                    },
                ],
                'order' => [
                    'required',
                    'integer',
                    'min:1', // Ensures the order is at least 1
                    Rule::unique('topics')->where(function ($query) use ($request) {
                        $query->whereNotNull('main_topic_id')->where('main_topic_id', $request->maintopic); // Check only main topics
                    }),
                ],
                'ClassificationReference' => 'required',
                'decisions' => 'required',
                'EscalationAuthority' => 'required', // Ensure it's an integer
                // Custom validation for specific combination of classification_reference and escalation_authority
                'ClassificationReference' => [
                    function ($attribute, $value, $fail) use ($request, $locale) {
                        // Check if both conditions match
                        if ((int) $request->input('ClassificationReference') === 3 && (int) $request->input('EscalationAuthority') === 1) {
                            // Set the error message based on the locale (either 'ar' or 'en')
                            $message = $locale === 'ar'
                                ? 'جهة التصعيد يجب ان تكون كلية بناء على مرجع التصنيف كلية' // Arabic message
                                : 'The escalation authority must be College based on the Classification Reference College'; // English message

                            // Trigger the validation failure with the message
                            $fail($message);
                        }
                    }
                ],

            ];

            $messages = $locale == 'ar' ? [
                'title.required' => 'العنوان مطلوب.',
                'title.string' => 'يجب أن يكون العنوان نصاً.',
                'title.unique' => 'العنوان يجب أن يكون فريداً.',
                'form.required' => 'حقل النموذج مطلوب.',
                'classification_reference.required' => 'المرجع التصنيفي مطلوب.',
                'classification_reference.integer' => 'المرجع التصنيفي يجب أن يكون رقمًا.',
                'escalation_authority.required' => 'جهة التصعيد مطلوبة.',
                'decisions.required' => 'القرار مطلوب',
                'escalation_authority.integer' => 'جهة التصعيد يجب أن تكون رقمًا.',
                'escalation_authority_college.required' => 'جهة التصعيد يجب ان تكون كلية',
                'order.required' => 'الترتيب مطلوب.',
                'order.string' => 'يجب أن يكون الترتيب نصاً.',
                'order.unique' => 'الترتيب يجب أن يكون فريداً.',
                'order.min' => 'الترتيب يجب أن يكون اكبر من او يساوى 1.',
            ] : [
                'title.required' => __('validation.required', ['attribute' => __('validation.attributes.title')]),
                'title.string' => __('validation.string', ['attribute' => __('validation.attributes.title')]),
                'title.unique' => __('validation.unique', ['attribute' => __('validation.attributes.title')]),
                'form.required' => 'The form field is required.',
                'classification_reference.required' => 'The classification reference is required.',
                'classification_reference.integer' => 'The classification reference must be an integer.',
                'escalation_authority.required' => 'The escalation authority is required.',
                'decisions.required' => 'The decisions is required.',
                'escalation_authority.integer' => 'The escalation authority must be an integer.',
                'escalation_authority_college.required' => 'Escalation authority must be College.',
                'order.required' => __('validation.required', ['attribute' => __('validation.attributes.order')]),
                'order.string' => __('validation.string', ['attribute' => __('validation.attributes.order')]),
                'order.unique' => __('validation.unique', ['attribute' => __('validation.attributes.order')]),
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $title = $request->input('title');
            $formData = $request->input('form'); // JSON string
            $maintopic = $request->input('maintopic');
            // dd($formData);
            // Decode JSON string
            $decodedFormData = json_decode($formData, true);

            // Retrieve the latest record
            $latestRecord = Topic::latest('id')->first();
            $latestCode = $latestRecord->code ?? 'tpc_0';

            // Extract the number part from the latest code
            $latestNumber = intval(preg_replace('/[^0-9]+/', '', $latestCode));

            // Increment the number
            $newNumber = $latestNumber + 1;

            // Generate the new code
            $newCode = 'tpc_' . $newNumber;

            $latestOrder = intval($latestRecord->order ?? '0');
            $newOrder = $latestOrder + 1;
            $decisions = implode(',', $request->input('decisions'));

            // Save the new topic
            $topic = new Topic();
            $topic->title = $title;
            $topic->code = $newCode;
            // $topic->order = $newOrder;
            $topic->order = $request->order;
            $topic->main_topic_id = $maintopic;
            $topic->classification_reference = $request->input('ClassificationReference');
            $topic->escalation_authority = $request->input('EscalationAuthority');
            $topic->decisions = $decisions; // Save the comma-separated decisions string
            $topic->save();
            $topicId = $topic->id;
            // Assuming you have a pivot table named axis_topic and a model Axis
            if (isset($decodedFormData) && !empty($decodedFormData)) {
                foreach ($decodedFormData as $axisId => $content) {
                    // Check if $content is not empty
                    if (!empty($content)) {
                        foreach ($content as &$field) {

                            // Update the form data if the name is 'userName'
                            if (isset($field['name']) && $field['name'] === 'userName') {
                                $field['value'] = '.';
                            }

                            if (isset($field['label'])) {
                                // Remove &nbsp; and <br> from the label
                                $field['label'] = preg_replace('/&nbsp;|<br\s*\/?>/', '', $field['label']);
                                // Remove any extra spaces around the label
                                $field['label'] = trim($field['label']);
                            }
                        }

                        // Find the Axis model
                        $axis = Axis::find($axisId);
                        if ($axis) {
                            // Attach or insert into pivot table
                            $topic->axes()->attach($axisId, ['field_data' => json_encode($content)]);
                        }
                    }
                }
            }



            // Save the report to the database
            ControlReport::create([
                'topic_id' => $topicId,
                'content' => "<p><br></p>"
            ]);

            return response()->json(['success' => 'Form submitted successfully!', 'topicId' => $topic->id], 200);
        }
    }


    public function destroyAxisTopic(Request $request)
    {
        // Retrieve axis id and topic id from the request
        $axisId = $request->input('axis_id');
        $topicId = $request->input('topic_id');

        // Find the axis to delete
        $axis = TopicAxis::where('axis_id', $axisId)
            ->where('topic_id', $topicId)
            ->first();

        // Check if the axis exists
        if (!$axis) {
            return response()->json(['message' => 'Axis not found'], 404);
        }

        // Check if this is the only axis for the given topic_id
        $axisCount = TopicAxis::where('topic_id', $topicId)->count();

        if ($axisCount <= 1) {
            // If it's the only axis for the topic, prevent deletion
            return response()->json(['message' => 'Cannot delete this axis, it is the last axis for this topic.'], 400);
        }

        // Delete the axis
        $axis->delete();

        return response()->json(['message' => 'Axis deleted successfully'], 200);
    }



    public function updateTopicAxessingle(Request $request)
    {
        // Retrieve axis_id, topic_id, and content from the request
        $axisId = $request->input('axisid');
        $topicId = $request->input('topicId');
        $content = $request->input('fbData');

        // Find the TopicAxis record based on axis_id and topic_id
        $axis = TopicAxis::where('axis_id', $axisId)->where('topic_id', $topicId)->first();

        if ($axis) {
            // Update the field_data with the form builder content
            $axis->field_data = $content;

            // Save the updated record
            $axis->save();

            return response()->json(['message' => 'Axis updated successfully'], 200);
        } else {
            return response()->json(['message' => 'Axis not found'], 404);
        }
    }


    public function UpdateFormbuilderTopic(Request $request, $locale = null)
    {

        // Start a transaction
        DB::beginTransaction();

        try {
            if ($request->maintopic == null) {
                // Define validation rules for when maintopic is null
                $rules = [
                    'title' => [
                        'required',
                        'string',
                        Rule::unique('topics')->ignore($request->topicId)
                    ],
                    'order' => [
                        'required',
                        'integer',
                        'min:1', // Ensures the order is at least 1
                        Rule::unique('topics')->where(function ($query) {
                            $query->whereNull('main_topic_id'); // Check only main topics
                        })->ignore($request->topicId),
                    ],
                ];

                // Define custom messages based on the locale
                $messages = ($locale == 'ar') ? [
                    'title.required' => 'العنوان مطلوب.',
                    'title.string' => 'يجب أن يكون العنوان نصاً.',
                    'title.unique' => 'العنوان يجب أن يكون فريداً.',

                    'order.required' => 'الترتيب مطلوب.',
                    'order.string' => 'يجب أن يكون الترتيب نصاً.',
                    'order.unique' => 'الترتيب يجب أن يكون فريداً.',
                    'order.min' => 'الترتيب يجب أن يكون اكبر من او يساوى 1.',
                ] : [
                    'title.required' => __('validation.required', ['attribute' => __('validation.attributes.title')]),
                    'title.string' => __('validation.string', ['attribute' => __('validation.attributes.title')]),
                    'title.unique' => __('validation.unique', ['attribute' => __('validation.attributes.title')]),

                    'order.required' => __('validation.required', ['attribute' => __('validation.attributes.order')]),
                    'order.string' => __('validation.string', ['attribute' => __('validation.attributes.order')]),
                    'order.unique' => __('validation.unique', ['attribute' => __('validation.attributes.order')]),
                ];

                // Validate the request
                $validator = Validator::make($request->all(), $rules, $messages);

                // Handle validation failure
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }

                // Update topic
                $title = $request->input('title');
                $topic = Topic::where('id', $request->topicId)->first();
                $topic->title = $title;
                $topic->order = $request->order;
                $topic->classification_reference = null;
                $topic->escalation_authority = null;
                $topic->main_topic_id = null; // Set main_topic_id to null
                $topic->save();

                // Delete related axis topics
                TopicAxis::where('topic_id', $request->topicId)->delete();
            } else {
                // Define validation rules for when maintopic is present
                $rules = [
                    'title' => [
                        'required',
                        'string',
                        Rule::unique('topics')->ignore($request->topicId)->where(function ($query) use ($request) {
                            return $query->where('main_topic_id', $request->maintopic);
                        }),
                    ],
                    'order' => [
                        'required',
                        'integer',
                        'min:1', // Ensures the order is at least 1
                        Rule::unique('topics')->where(function ($query) use ($request) {
                            $query->whereNotNull('main_topic_id')->where('main_topic_id', $request->maintopic); // Check only main topics
                        })->ignore($request->topicId),
                    ],
                    // 'contentInputDepartment' => [
                    //     'required',
                    //     function ($attribute, $value, $fail) use ($locale) {
                    //         $defaultContent = '<p><br></p>';
                    //         if (trim($value) === $defaultContent) {
                    //             $message = $locale == 'ar'
                    //                 ? 'محتوى الإدخال للقسم مطلوب.'
                    //                 : 'Content for department is required.';
                    //             $fail($message);
                    //         }
                    //     },
                    // ],
                    // 'contentInputFaculty' => [
                    //     'required',
                    //     function ($attribute, $value, $fail) use ($locale) {
                    //         $defaultContent = '<p><br></p>';
                    //         if (trim($value) === $defaultContent) {
                    //             $message = $locale == 'ar'
                    //                 ? 'محتوى الإدخال لكلية مطلوب.'
                    //                 : 'Content for faculty is required.';
                    //             $fail($message);
                    //         }
                    //     },
                    // ],
                    'ClassificationReference' => 'required',
                    'decisions' => 'required',
                    'EscalationAuthority' => 'required',
                    'ClassificationReference' => [
                        function ($attribute, $value, $fail) use ($request, $locale) {
                            if ((int) $request->input('ClassificationReference') === 3 && (int) $request->input('EscalationAuthority') === 1) {
                                $message = $locale === 'ar'
                                    ? 'جهة التصعيد يجب ان تكون كلية بناء على مرجع التصنيف كلية'
                                    : 'The escalation authority must be College based on the Classification Reference College';
                                $fail($message);
                            }
                        }
                    ],
                ];

                $messages = $locale == 'ar' ? [
                    'title.required' => 'العنوان مطلوب.',
                    'title.string' => 'يجب أن يكون العنوان نصاً.',
                    'title.unique' => 'العنوان يجب أن يكون فريداً.',
                    'classification_reference.required' => 'المرجع التصنيفي مطلوب.',
                    'classification_reference.integer' => 'المرجع التصنيفي يجب أن يكون رقمًا.',
                    'escalation_authority.required' => 'جهة التصعيد مطلوبة.',
                    'escalation_authority.integer' => 'جهة التصعيد يجب أن تكون رقمًا.',
                    'decisions.required' => 'القرار مطلوب.',
                    'escalation_authority_college.required' => 'جهة التصعيد يجب ان تكون كلية',
                    // 'contentInputDepartment.required' => 'محتوى الإدخال للقسم مطلوب.',
                    // 'contentInputFaculty.required' => 'محتوى الإدخال لكلية مطلوب.',
                    'order.required' => 'الترتيب مطلوب.',
                    'order.string' => 'يجب أن يكون الترتيب نصاً.',
                    'order.unique' => 'الترتيب يجب أن يكون فريداً.',
                    'order.min' => 'الترتيب يجب أن يكون اكبر من او يساوى 1.',
                ] : [
                    'title.required' => __('validation.required', ['attribute' => __('validation.attributes.title')]),
                    'title.string' => __('validation.string', ['attribute' => __('validation.attributes.title')]),
                    'title.unique' => __('validation.unique', ['attribute' => __('validation.attributes.title')]),
                    'classification_reference.required' => 'The classification reference is required.',
                    'classification_reference.integer' => 'The classification reference must be an integer.',
                    'escalation_authority.required' => 'The escalation authority is required.',
                    'escalation_authority.integer' => 'The escalation authority must be an integer.',
                    'escalation_authority_college.required' => 'Escalation authority must be College.',
                    'decisions.required' => 'The decisions is required.',
                    // 'contentInputDepartment.required' => 'Content for department is required.',
                    // 'contentInputFaculty.required' => 'Content for faculty is required.',
                    'order.required' => __('validation.required', ['attribute' => __('validation.attributes.order')]),
                    'order.string' => __('validation.string', ['attribute' => __('validation.attributes.order')]),
                    'order.unique' => __('validation.unique', ['attribute' => __('validation.attributes.order')]),
                ];

                $validator = Validator::make($request->all(), $rules, $messages);

                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }


                $decisions = implode(',', $request->input('decisions'));
                // Proceed with saving the topic and other related data
                $title = $request->input('title');
                $formData = $request->input('form'); // JSON string
                $maintopic = $request->input('maintopic');
                $order = $request->order;

                $decodedFormData = json_decode($formData, true);
                $topic = Topic::findOrFail($request->topicId);
                $topic->title = $title;
                $topic->order = $order;
                $topic->classification_reference = $request->input('classificationReference');
                $topic->escalation_authority = $request->input('EscalationAuthority');
                $topic->decisions = $decisions;
                $topic->main_topic_id = $maintopic;
                $topic->save();

                // Assuming you have a pivot table named `axis_topic` and a model `Axis`
                if (!empty($decodedFormData)) {
                    foreach ($decodedFormData as $axisId => $content) {
                        if (!empty($content)) {
                            foreach ($content as &$field) {
                                if (isset($field['name']) && $field['name'] === 'userName') {
                                    $field['value'] = '.';
                                }
                                if (isset($field['label'])) {
                                    $field['label'] = trim(preg_replace('/&nbsp;|<br\s*\/?>/', '', $field['label']));
                                }
                            }

                            $axis = Axis::find($axisId);
                            if ($axis) {
                                $topic->axes()->attach($axisId, ['field_data' => json_encode($content)]);
                            }
                        }
                    }
                }
                // check for the last topic in agenda amd can change the topic report if not approval yet
                $agendaIds = AgandesTopicForm::where('topic_id', $request->input('topicId'))
                    ->pluck('agenda_id');

                if ($agendaIds->isNotEmpty()) {
                    $sessionTopics = SessionTopic::whereIn('topic_agenda_id', $agendaIds)
                        ->get();

                    foreach ($sessionTopics as $sessionTopic) {
                        $sessionDecision = SessionDecision::where('session_id', $sessionTopic->session_id)
                            ->where('topic_id', $request->input('topicId'))
                            ->whereIn('agenda_id', $agendaIds)
                            ->latest()
                            ->first();

                        if (!$sessionDecision || !$sessionDecision->approval) {
                            $sessionTopic->update([
                                'report_template_content' => $request->input('contentInputDepartment'),
                                'cover_letter_template_content' => $request->input('contentInputFaculty'),
                            ]);
                        }
                    }
                }



                // Save or update reports in the database
                ControlReport::updateOrCreate(
                    ['topic_id' => $request->input('topicId')],
                    [
                        'content' => $request->input('contentInputDepartment'),
                        'topic_formate' => $request->input('contentDepartmentTopic'),
                    ]
                );

                ControlReportFaculty::updateOrCreate(
                    ['topic_id' => $request->input('topicId')],
                    [
                        'content' => $request->input('contentInputFaculty'),
                        'topic_formate' => $request->input('contentFacultyTopic'),
                    ]
                );
            }

            // Commit the transaction
            DB::commit();

            return response()->json(['success' => 'Form submitted successfully!', 'topicId' => $topic->id], 200);
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function getPagesReport($recordId)
    {
        $appURL = env('APP_URL');

        // Build the URL dynamically
        $url = $appURL . '/admin/topics/' . $recordId . '/edit';

        return redirect()->away($url);
    }
    public function getCoversReport($recordId)
    {
        $appURL = env('APP_URL');

        // Build the URL dynamically
        $url = $appURL . '/admin/topics/' . $recordId . '/CoverLeter';

        return redirect()->away($url);
    }
    public function fetchClassificationDecisions()
    {
        $decisions = ClassificationDecision::all();
        return response()->json($decisions);
    }

    public function topicFormate(Request $request, $topic_id, $type)
    {
        if ($type == 'department') {
            ControlReport::where('topic_id', $topic_id)->update([
                'topic_formate' => $request->content,
            ]);
            return response()->json(['message' => 'Topic formate saved successfully'], 200);
        } else if ($type == 'faculty') {
            ControlReportFaculty::where('topic_id', $topic_id)->update([
                'topic_formate' => $request->content,
            ]);
            return response()->json(['message' => 'Topic formate saved successfully'], 200);
        } else {
            return response()->json(['message' => 'Unkonwn type'], 404);
        }
    }
}
