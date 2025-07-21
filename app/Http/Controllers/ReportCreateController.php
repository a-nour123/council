<?php

namespace App\Http\Controllers;

use App\Models\ControlReport;
use App\Models\CoverLetterReport;
use App\Models\TopicAxis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportCreateController extends Controller
{
    // public function store(Request $request)
    // {
    //       $request->validate([
    //         'content' => 'required|string', // Validate the content field
    //         'name' => 'required|string', // Validate the content field
    //     ]);

    //     // Save the report to the database
    //     ControlReport::create([
    //         'content' => $request->input('content'),
    //         'name' => $request->input('name'),
    //         'topic_id' => $request->input('topic'),
    //     ]);

    //     return redirect()->back()->with('success', 'Content saved successfully!');
    // }
    public function update(Request $request, $id)
    {
        // Validate the request
        $request->validate([
             'content' => 'required|string',
        ]);

        // Find the report by ID
        $report = ControlReport::findOrFail($id);
         // Update the report
         $report->content = $request->input('content');
        $report->topic_id = $request->input('topicId');
        $report->save();

        // Return a response
        return response()->json(['message' => 'Report updated successfully']);
    }

    public function getTopicFieldData(Request $request)
    {
        $topicId = $request->id;

        // Fetch all field_data from the topics_axes table where topic_id matches the request ID
        $fieldDataCollection = TopicAxis::where('topic_id', $topicId)->pluck('field_data');

        $labels = [];
        // $excludedLabels = ["Checkbox Group", "Select"];
        $excludedLabels = [""];

        // Loop through each field_data entry and extract the 'label' values
        foreach ($fieldDataCollection as $fieldData) {
            $fieldDataArray = json_decode($fieldData, true);

            // Check if decoding was successful and the data is an array
            if (is_array($fieldDataArray)) {
                foreach ($fieldDataArray as $field) {
                    if (isset($field['label']) && !empty($field['label'])) {
                        // Trim any HTML entities or extra whitespace
                        $label = trim(strip_tags($field['label']));

                        // Skip the labels that are in the excluded list
                        if (!in_array($label, $excludedLabels) && !in_array($label, $labels)) {
                            $labels[] = $label;
                        }
                    }
                }
            }
        }

        // Return the labels as a JSON response
        return response()->json(['labels' => $labels]);
    }
    public function storeCoverLetters(Request $request, $locale = null){

         // Start a transaction
        DB::beginTransaction();

        try {
            // Define validation rules for content field
            $rules = [
                // 'contentInput' => [
                //     'required',
                //     function ($attribute, $value, $fail) {
                //         $defaultContent = '<p><br></p>';
                //         if (trim($value) === $defaultContent) {
                //             $fail(__('محتوى الإدخال مطلوب لخطاب التغطية'));
                //         }
                //     },
                // ],
            ];

            // Define custom messages based on the locale
            $messages = ($locale == 'ar') ? [
                'contentInput.required' => 'محتوى الإدخال مطلوب لخطاب التغطية',
            ] : [
                'contentInput.required' => __('validation.required', ['attribute' => 'Content for Cover Letter']),
            ];

            // Validate the request
            $validator = Validator::make($request->all(), $rules, $messages);

            // Check for validation failures
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Update or create the CoverLetterReport record
            CoverLetterReport::updateOrCreate(
                ['topic_id' => $request->input('topicId')],
                ['content' => $request->input('contentInput')]
            );

            // Commit the transaction
            DB::commit();

            return response()->json(['success' => 'Form submitted successfully!'], 200);
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }



}
