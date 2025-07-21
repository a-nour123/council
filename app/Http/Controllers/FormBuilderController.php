<?php

namespace App\Http\Controllers;

use App\Models\Axis;
use App\Models\FormBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FormBuilderController extends Controller
{
    //
    public function index()
    {
        $forms = FormBuilder::all();
        return view('FormBuilder.index', compact('forms'));
    }

    public function create(Request $request, $locale = null)
    {
         $rules = [
            'name' => 'required|unique:axes,title',
            'form' => [
                'required',
                function ($attribute, $value, $fail) use ($locale) {
                    if (trim($value) === '[]') {
                        //  $fail('The form field must contain valid data.');
                         $message = $locale == 'ar'
                                ? 'يجب أن يحتوي حقل النموذج على بيانات صالحة.'
                                : 'The form field must contain valid data.';
                            $fail($message);
                    }
                },
            ],
        ];

        if ($locale == 'ar') {
            $messages = [
                'name.required' => 'العنوان مطلوب.',
                'name.string' => 'يجب أن يكون العنوان نصاً.',
                'name.unique' => 'العنوان يجب أن يكون فريداً.',
            ];
        } else {
            $messages = [
                'name.required' => __('validation.required', ['attribute' => __('validation.attributes.name')]),
                'name.string' => __('validation.string', ['attribute' => __('validation.attributes.name')]),
                'name.unique' => __('validation.unique', ['attribute' => __('validation.attributes.name')]),
            ];
        }

        // Create the validator
        $validator = Validator::make($request->all(), $rules, $messages);

        // Check for validation failures
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validate the request
        // $validator = Validator::make($request->all(), [
        //     'name' => 'required|unique:axes,title',
        //     'form' => [
        //         'required',
        //         function ($attribute, $value, $fail) {
        //             if (trim($value) === '[]') {
        //                  $fail('The form field must contain valid data.');
        //             }
        //         },
        //     ],
        // ]);


        // If validation fails, return the validation errors
        // if ($validator->fails()) {
        //     return response()->json(['errors' => $validator->errors()], 422);
        // }

        // Proceed with saving the data
        $item = new Axis();
        $item->title = $request->input('name');
        $item->content = $request->input('form');
        $item->save();

        return response()->json(['success' => true]);
    }

    public function edit($id)
    {
        // Retrieve the record using the $id
        $record = Axis::findOrFail($id);

        // Return the custom view with the record data
        return view('filament.resources.axies.pages.edit', compact('record'));
    }

    public function editData(Request $request)
    {
        $formData = FormBuilder::where('id', $request->id)->first();
        return response()->json($formData);
    }



    public function update(Request $request, $locale = null)
    {
        $rules = [
            'name' => 'required|unique:axes,title,' . $request->id, // Allow the current item's title
            'form' => [
                'required',
            ],
        ];

        if ($locale == 'ar') {
            $messages = [
                'name.required' => 'العنوان مطلوب.',
                'name.string' => 'يجب أن يكون العنوان نصاً.',
                'name.unique' => 'العنوان يجب أن يكون فريداً.',
                'form.required' => 'النموذج مطلوب.',
            ];
        } else {
            $messages = [
                'name.required' => __('validation.required', ['attribute' => __('validation.attributes.name')]),
                'name.string' => __('validation.string', ['attribute' => __('validation.attributes.name')]),
                'name.unique' => __('validation.unique', ['attribute' => __('validation.attributes.name')]),
                'form.required' => __('validation.required', ['attribute' => __('validation.attributes.form')]),
            ];
        }

        // Create the validator
        $validator = Validator::make($request->all(), $rules, $messages);

        // Check for validation failures
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        // // Validate the request
        // $validator = Validator::make($request->all(), [
        //     'name' => 'required|unique:axes,title,' . $request->id, // Allow the current item's title
        //     'form' => [
        //         'required',
        //     ],
        // ]);

        // // If validation fails, return the validation errors
        // if ($validator->fails()) {
        //     return response()->json(['errors' => $validator->errors()], 422);
        // }

        // Proceed with saving the data
        $item = Axis::findOrFail($request->id);
        $item->title = $request->name;
        $item->content = $request->form;
        $item->update();

        return response()->json(['success' => true]);
    }



    public function destroy($id)
    {
        $form = FormBuilder::findOrFail($id);
        $form->delete();

        return redirect('form-builder')->with('success', 'Form deleted successfully');
    }
}
