@extends('layouts.app')
@section('head')
    <title>{{ __('Example formBuilder') }}</title>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <label for="name">{{ __('Name') }}</label>
            <input type="text" id="name" name="name" class="form-control" />
            <div id="fb-editor"></div>
        </div>
    </div>
@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
    <script src="{{ URL::asset('assets/form-builder/form-builder.min.js') }}"></script>
    <script>
        jQuery(function($) {
            $(document.getElementById('fb-editor')).formBuilder({
                onSave: function(evt, formData) {
                    console.log(formData);
                    saveForm(formData);
                },
            });
        });

        function saveForm(formData) {
            var form = new FormData();
            form.append('form', JSON.stringify(formData)); // Add form data to FormData
            form.append('name', $("#name").val()); // Add the name field
            form.append('_token', "{{ csrf_token() }}"); // Add CSRF token

            // Loop through the form data and append files to FormData
            $('#fb-editor input[type="file"]').each(function() {
                var fileInput = $(this);
                if (fileInput[0].files.length > 0) {
                    // Append the file with the correct name (from formData)
                    form.append(fileInput.attr('name'), fileInput[0].files[0]);
                }
            });

            $.ajax({
                type: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
                url: '{{ URL('save-form-builder') }}',
                data: form,
                processData: false, // Prevent jQuery from processing the FormData
                contentType: false, // Prevent jQuery from setting contentType
                success: function(data) {
                    location.href = "/form-builder";
                    console.log(data);
                },
                error: function(xhr, status, error) {
                    console.error("Error uploading the form:", error);
                }
            });
        }
    </script>
@endsection
