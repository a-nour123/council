<?php
// Assuming this script is running in a Laravel application context
use Illuminate\Support\Facades\App;

// Initialize an empty array to hold the language data
$langData = [];

// Define the path to the language file based on the locale
$langFile = $locale == 'ar' ? __DIR__ . '/../../../lang/ar.json' : __DIR__ . '/../../../lang/en.json';

// Check if the file exists
if (file_exists($langFile)) {
    // Read the contents of the language file and decode it
    $langData = json_decode(file_get_contents($langFile), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        // Handle JSON decoding error
        echo 'Error decoding JSON from language file.';
    }
} else {
    // Handle the scenario where the language file doesn't exist
    echo "Language file for '$locale' not found!";
}

// Use $langData here or within this block

?>

<form id="form-submit">
    @csrf
    <div id="form-content">
        <div id="form-entry-template" class="form-entry" style="display: none;">
            <div class="form-builder-container">
                <label for="title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                    <?= $langData['Attachements'] ?>:
                    {{-- <span style="color:red" class="title" id="topicTitle"></span> --}}
                </label>
                <div class="file-previews-container">
                    <!-- This is where the file previews will be appended -->
                </div>

                <input type="hidden" class="agendaId" name="agendaId[]">
                <input type="hidden" class="topicId" name="topicId[]">
                <input type="hidden" class="sessionId" name="sessionId[]">
                <label for="title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                    <?= $langData['Topic Title'] ?>:
                    <span style="color:red" class="title" id="topicTitle"></span>
                </label>

                <div name="fb-editor-edit[]" style="width: 100%" class="bg-white dark:bg-gray-800 fb-editor-edit">
                    <!-- Form builder content will be rendered here -->
                </div>

                <label for="decision" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                    <?= $langData['Choose a recommendation'] ?>:
                </label>
                <div class="decisionChoices">
                    <!-- Radio buttons for decisions will be inserted here -->
                </div> <!-- Radio buttons for decisions will be inserted here -->

                <label for="decision" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                    <?= $langData['justification'] ?>:
                </label>

                <textarea name="decision[]" rows="4"
                    class="decision bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                    placeholder="<?= $langData['Enter decision notes here'] ?>."></textarea>
            </div>
        </div>
        <div id="form-entries-container"></div>
        @if (in_array(auth()->user()->position_id, [4, 5]))
            <button type="submit" id="submit-form" class="btn btn-primary"><?= $langData['Submit'] ?></button>
        @endif
    </div>
</form>

<script>
    $(document).ready(function() {
        function hideModaldecision() {
            const modal = document.getElementById('decision-modal');
            modal.classList.add('hidden');
            modal.style.display = ''; // Correct way to reset the display style
            modal.classList.remove('flex');
        }

        $('#closeform').click(function() {
            hideModaldecision();
        });
        $('#submit-form').click(function(event) {
            event.preventDefault(); // Prevent default form submission
            var $langData = {
                'Success': `<?= $langData['Success'] ?>`,
                'saving data': `<?= $langData['saving data'] ?>`,
                'ok': `<?= $langData['ok'] ?>`,
                'Add another': `<?= $langData['Add another'] ?>`,
                'Decision is required': `<?= $langData['Decision is required'] ?>`,
            };
            if (selectData()) { // Collect form data into formDataArray and validate
                // Perform AJAX submit
                $.ajax({
                    url: "{{ route('saveFacultyDecision') }}",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        formData: formDataArray,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            position: 'center',
                            title: $langData['Success'],
                            text: $langData['saving data'],
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            hideModaldecision();
                        });
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            for (let key in errors) {
                                if (errors.hasOwnProperty(key)) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: errors[key][0],
                                    });
                                }
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Something went wrong!',
                            });
                        }
                    }
                });
            }
        });

        let formDataArray = [];

        function selectData() {
            formDataArray = []; // Clear previous data
            let valid = true;

            $('.form-entry').each(function(index, element) {
                var agendaId = $(element).find('.agendaId').val();
                var topicId = $(element).find('.topicId').val();
                var sessionId = $(element).find('.sessionId').val();
                var decision = $(element).find('.decision').val();
                // Get the selected decision from radio buttons inside .decisionChoices
                var selectedDecisionChoice = $(element).find('.decision-radio:checked').val();

                // Clear previous validation errors
                $(element).find('.invalid-feedback').remove();
                $(element).find('.decision').removeClass('is-invalid');
                $(element).find('.decisionChoices').removeClass('is-invalid');

                // Validation for both decision and selected decision choice
                if (index > 0) {
                    // if (!decision || !selectedDecisionChoice) {
                    if (!selectedDecisionChoice) {
                        valid = false;
                        $(element).find('.decision').addClass('is-invalid');
                        if (!$(element).find('.invalid-feedback').length) {
                            $(element).find('.decision').after(
                                '<div class="invalid-feedback" style="color: red;">' +
                                'القرار مطلوب' + '</div>'
                            );
                        }
                    } else {
                        $(element).find('.decision').removeClass('is-invalid');
                        $(element).find('.invalid-feedback').remove();
                    }
                }

                formDataArray.push({
                    agendaId: agendaId,
                    topicId: topicId,
                    sessionId: sessionId,
                    decision: decision,
                    decisionChoice: selectedDecisionChoice, // Added decisionChoice
                });
            });

            return valid;
        }
    });
</script>




<style>
    .decision {
        margin-top: 5px;
        margin-bottom: 5px;
    }

    .form-builder-container {
        margin-top: 5px;
        margin-bottom: 5px;
    }

    .formbuilder-icon-autocomplete,
    .formbuilder-icon-button,
    .formbuilder-icon-header,
    .formbuilder-icon-hidden {
        display: none !important;
    }

    .required-wrap,
    .description-wrap,
    .value-wrap,
    .subtype-wrap,
    .min-wrap,
    .max-wrap,
    .step-wrap,
    .rows-wrap,
    .toggle-wrap,
    .inline-wrap,
    .className-wrap,
    .name-wrap,
    .access-wrap,
    .other-wrap {
        display: none !important;
    }

    .delete-confirm,
    .formbuilder-icon-copy,
    .formbuilder-icon-sort-higher,
    .formbuilder-icon-sort-lower .formbuilder-icon-pencil {
        display: none !important;
    }

    /* Style to make list items look disabled */
    ul>.ui-sortable-handle {
        pointer-events: none !important;
        opacity: 0.5 !important;
    }

    .pull-right {
        display: none !important;
    }

    .frmb.stage-wrap.pull-left.ui-sortable {
        width: 100%;

    }

    .sort-button.sort-button-lower.btn.formbuilder-icon-sort-lower {
        display: none !important;
    }

    .sort-button.sort-button-lower.btn.formbuilder-icon-sort-lower {
        display: none !important;
    }

    .toggle-form.btn.formbuilder-icon-pencil {
        display: none !important;
    }

    .pull-left>li {
        pointer-events: none !important;
        /* Disables pointer events */
        opacity: 1 !important;
        /* Sets opacity to 50% */
    }

    #decision-modal {
        justify-content: center;
        /* Center content horizontally */
        align-items: center;
        /* Center content vertically */
        margin-top: 4%;
        /* Adjust margin if needed */
        /* Optional: Add maximum height and overflow handling */
        max-height: 100vh;
        overflow-y: auto;

    }

    #decision-modal .modal-header,
    #decision-modal .modal-footer {
        position: sticky;
        top: 0;
        background-color: white;
        z-index: 10;
    }

    #decision-modal .modal-body {
        flex-grow: 1;
        overflow-y: auto;
        /* Adds scrolling to modal content */
        padding: 15px;
        max-height: 60vh;
        /* Set the body section height */
    }

    .file-preview {
        display: inline-block;
        margin: 10px;
        text-align: center;
        max-width: 100%;
        width: 150px;
        /* Set a maximum width for each image */
        height: auto;
        overflow: hidden;
    }

    .file-preview img {
        width: 60%;
        height: auto;
        object-fit: cover;
        /* Ensure the image covers the area of its container without distortion */
        border-radius: 8px;
        /* Optional: add rounded corners to the image */
    }

    .file-preview p {
        margin-top: 5px;
        font-size: 12px;
        color: #555;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
        max-width: 100%;
    }
</style>
