<x-filament::page>

    <?php
    $langData = [];
    // Get the current locale
    $locale = app()->getLocale();
    
    // Define the path to the language file based on the locale
    $langFile = $locale == 'ar' ? __DIR__ . '/../../../lang/ar.json' : __DIR__ . '/../../../lang/en.json';
    
    // Check if the file exists
    if (file_exists($langFile)) {
        // Read the contents of the language file and decode it
        $langData = json_decode(file_get_contents($langFile), true);
        // Now $langData contains the language data from the specified file
    
        // Use $langData here or within this block
    } else {
        // Handle the scenario where the language file doesn't exist
        // For example, you could use default language data or log an error
        echo "Language file for '$locale' not found!";
    }
    ?>

    <div class="card">
        <div class="card-body">
            <label for="first_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                <?= $langData['Title'] ?>
            </label>
            <input type="text" id="first_name"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                placeholder=<?= $langData['Enter the title'] ?> required />
            <br>
            <div id="fb-editor" class="bg-white dark:bg-gray-800">
                <!-- Your form builder content here -->
            </div>
            <br>
            <!-- Add Save Form Button -->

        </div>
        <button id="save-form-button"
            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
            type="button">
            <?= $langData['Save'] ?>
        </button>


    </div>


    <!-- Include necessary scripts -->
    <script src="{{ URL::asset('assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ URL::asset('assets/form-builder/form-builder.min.js') }}"></script>
    <link rel="stylesheet" href="{{ URL::asset('assets/css/sweetalert2.min.css') }}">
    <script src="{{ URL::asset('assets/js/sweetalert2.min.js') }}"></script>
    <!-- Load Moment.js -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <!-- Load Moment Hijri.js -->
    <script src="https://cdn.jsdelivr.net/npm/moment-hijri@2.2.1/moment-hijri.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/abublihi/datepicker-hijri@v1.1/build/datepicker-hijri.js"></script>

    <script>
        jQuery(function($) {
            moment.locale('ar'); // Set the language to Arabic for moment.js

            // Define custom fields including Countries dropdown
            var fields = [{
                    type: 'arab-countries-select',
                    label: 'Country',
                    className: 'form-control',
                    name: 'country', // Ensure the select element has a unique name
                    value: 'SA', // Set a default selected value (can be changed dynamically)
                    icon: '🌍'
                },
                {
                    type: 'text',
                    label: 'Uploader Name',
                    className: 'form-control',
                    name: 'userName',
                    value: '.', // Set a default selected value (can be changed dynamically)

                },
                {
                    type: 'hijri-date-picker',
                    label: 'Hijri Date',
                    className: 'form-control',
                    name: 'hijri_date'

                },

            ];

            // Define custom templates for the custom fields
            var templates = {
                'arab-countries-select': function(fieldData) {
                    const randomNumber = Math.floor(Math.random() *
                        10000); // Generates a random number between 0 and 9999
                    const uniqueId = `${fieldData.name}_${randomNumber}`;

                    return {
                        field: `<select id="${uniqueId}" name="${fieldData.name}" class="form-control"><option>Loading countries...</option></select>`,
                        onRender: function() {
                            // Fetch and populate Arab countries only if not already populated
                            fetchCountries(uniqueId, fieldData.value);
                        }
                    };
                },

                'hijri-date-picker': function(fieldData) {
                    const uniqueId = `${fieldData.name}_${Math.floor(Math.random() * 10000)}`;
                    return {
                        field: `
                            <!-- Text input to display selected date (user interacts with this) -->
                            <input type="text" id="${uniqueId}_calender_display" name="${fieldData.name}" class="form-control hijri-input" placeholder="Select Hijri Date" />

                            <!-- Hidden input field to hold the selected date (this gets submitted) -->
                            <input type="hidden" id="${uniqueId}_calender_hidden" name="${fieldData.name}" />

                            <!-- Datepicker-hijri component -->
                            <datepicker-hijri reference="${uniqueId}_calender_display" 
                                placement="bottom" 
                                date-format="iYYYY/iMM/iDD" 
                                class="form-control hijri-datepicker" 
                                placeholder="Select Hijri Date">
                            </datepicker-hijri>`,
                        onRender: function() {
                            // Initialize Hijri date picker when the input field is focused
                            $('#' + uniqueId + '_calender_display').on('focus', function() {
                                // Check if the date picker is initialized
                                if (!$(this).data('datepicker')) {
                                    // Initialize the datepicker on focus
                                    $(this).datepicker({
                                        format: 'iYYYY/iMM/iDD',
                                        autoclose: true, // Close the picker after selection
                                        clearBtn: true, // Add a clear button
                                        todayHighlight: true // Highlight today's date
                                    });
                                }
                            });

                            // Capture the date selection and set it into the input fields
                            $('#' + uniqueId + '_calender_display').on('change', function() {
                                const selectedDate = $(this).val();
                                // Set the value to both the display and hidden input fields
                                $('#' + uniqueId + '_calender_hidden').val(selectedDate);

                                // Trigger the 'keyup' event on the hidden input after setting the value
                                $('#' + uniqueId + '_calender_hidden').trigger('change');


                            });
                        }
                    };


                }
            };






            // Function to fetch countries and populate the select element
            function fetchCountries(elementId, selectedValue) {
                var selectElement = document.getElementById(elementId);
                if (selectElement && selectElement.dataset.fetched !== 'true') {
                    var appUrl = '{{ env('APP_URL') }}';
                    var localJsonUrl = appUrl + '/admin/countries-json';

                    // Use fetch to get the data from the generated URL
                    fetch(localJsonUrl)
                        .then(response => response.json())
                        .then(data => {
                            // Filter countries to include only Arab countries or those with available translations
                            var options = data
                                .map(country => {
                                    var countryName = (country.translations && country.translations.ara) ?
                                        country.translations.ara.common :
                                        'Unknown Country'; // Fallback for non-Arab countries
                                    var isSelected = countryName === selectedValue ? 'selected' : '';
                                    return `<option value="${countryName}" ${isSelected}>${countryName}</option>`;
                                })
                                .join('');

                            // Update the select element with the options
                            selectElement.innerHTML = options;
                            selectElement.dataset.fetched = 'true'; // Mark as fetched

                            // Initialize Select2 on the populated select element
                            initializeSelect2(`#${elementId}`);
                        })
                        .catch(error => {
                            selectElement.innerHTML = '<option>Error loading countries</option>';
                            console.error('Error fetching local JSON:', error);
                        });
                }
            }

            // Function to initialize Select2
            function initializeSelect2(selector) {
                $(selector).select2({
                    placeholder: 'Search and select a country',
                    allowClear: true,
                    width: '100%' // Adjusts the width to fit the container
                });
            }


            // Options for the form builder, including custom fields and templates
            var options = {
                disableFields: ['autocomplete', 'button', 'header', 'hidden'],
                replaceFields: [{
                    type: "number",
                    label: "Number Condition", // Update label here
                }],
                fields: fields, // Include custom fields
                templates: templates, // Include custom templates
                typeUserDisabledAttrs: {
                    'number': [
                        'required',
                    ]
                },
                typeUserAttrs: {
                    'arab-countries-select': {
                        placeholder: {
                            label: 'Placeholder',
                            value: ''
                        }
                    }
                },
                onSave: function(evt, formData) {
                    // Process the form data here or submit the form
                    console.log('Form Data:', formData);
                    // Example AJAX submission
                    $.ajax({
                        url: '/saveForm', // Your endpoint to handle form submission
                        method: 'POST',
                        data: {
                            formData: formData,
                            // Additional data if necessary
                        },
                        success: function(response) {
                            console.log('Form saved successfully:', response);
                        },
                        error: function(error) {
                            console.error('Error saving form:', error);
                        }
                    });
                }
            };

            // Initialize form builder
            const formBuilder = $(document.getElementById('fb-editor')).formBuilder(options);

            // Variable to keep track of current clicks
            let currentClicks = [];

            // Handle number input value change
            $('#save-form-button').on('click', function() {
                const formData = formBuilder.actions.getData('json');
                saveForm(formData);
            });

            $(document).on('change', '#fb-editor input[type="number"]', function() {
                var newValue = $(this).val();

                // Ensure the new value is a positive integer and not greater than 5
                if ($.isNumeric(newValue) && newValue >= 0 && newValue <= 5) {
                    handleClicks(newValue);
                } else if (newValue > 5) {
                    alert('العدد المسموح 5 او اقل');
                    $(this).val(newValue); // Optionally set the value to 5 if it exceeds the limit
                    handleClicks(0);
                } else {
                    alert('Please enter a valid number.');
                }
            });

            function handleClicks(value) {
                // Remove previous clicks
                currentClicks.forEach(function(element) {
                    $(element).off('click');
                });

                // Clear the current clicks
                currentClicks = [];

                // Click the element the specified number of times
                for (var i = 0; i < value; i++) {
                    setTimeout(function() {
                        var element = $('.formbuilder-icon-text').first();
                        element.click();
                        currentClicks.push(element);
                    }, i * 100); // Delay each click slightly
                }
            }

            function saveForm(form) {
                // Define locale from PHP to JavaScript
                var locale = @json(app()->getLocale());

                // Construct the URL dynamically with session_id and locale
                var url =
                    '{{ URL('save-form-builder', ['locale' => '__LOCALE__']) }}';
                url = url.replace('__LOCALE__', encodeURIComponent(locale));

                var $langData = {
                    'Success': `<?= $langData['Success'] ?>`,
                    'saving data': `<?= $langData['saving data'] ?>`,
                    'ok': `<?= $langData['ok'] ?>`,
                    'Add another': `<?= $langData['Add another'] ?>`,
                };
                $.ajax({
                    type: 'post',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    url: url,
                    data: {
                        'form': form,
                        'name': $("#first_name").val(),
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(data) {
                        Swal.fire({
                            title: $langData['Success'],
                            text: $langData['saving data'],
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonText: $langData['ok'],
                            cancelButtonText: $langData['Add another'],
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.history.back();
                            } else if (result.dismiss === Swal.DismissReason.cancel) {
                                location.reload();
                            }
                        });
                    },
                    error: function(response) {
                        // Remove any existing error message
                        $('#title-error').remove();

                        // Check if response contains validation errors
                        if (response.responseJSON && response.responseJSON.errors && response
                            .responseJSON.errors.name) {
                            // Create a span element for the error message
                            const errorMessage = $('<p>')
                                .attr('id', 'title-error')
                                .addClass(
                                    'fi-fo-field-wrp-error-message text-sm text-danger-600 dark:text-danger-400'
                                )
                                .text(response.responseJSON.errors.name[0])
                                .css('color', 'red'); // Set the text color to red

                            // Insert the error message after the title input field
                            $('#first_name').after(errorMessage);

                            // Set focus on the title input field & apply a danger color to the input field
                            $('#first_name').focus().css({
                                'border-color': 'rgba(var(--danger-600),var(--tw-text-opacity))',
                                'border-style': 'solid',
                                'border-width': '1px'
                            });
                        }
                        if (response.responseJSON && response.responseJSON.errors && response
                            .responseJSON.errors.form) {
                            // Remove any existing error message
                            $('#form-error').remove();

                            // Create a span element for the error message
                            const errorMessage = $('<span>')
                                .attr('id', 'form-error')
                                .addClass(
                                    'fi-fo-field-wrp-error-message text-sm text-danger-600 dark:text-danger-400'
                                )
                                .text(response.responseJSON.errors.form[0])
                                .css('color', 'red'); // Set the text color to red

                            // Insert the error message before the form field or any appropriate place
                            $('#fb-editor').before(errorMessage);
                        }
                    }
                });
            }
        });
    </script>





    <!-- Include Flowbite CSS and JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>

    <!-- Include CSS for Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Include JavaScript for Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Override dark mode styles -->
    <style>
        .form-actions {
            display: none !important;
        }

        /* Dark mode styles */
        .dark .ui-sortable-handle {
            background-color: rgba(var(--gray-700), var(--tw-bg-opacity)) !important;
            --tw-bg-opacity: 1;
            border-color: rgba(var(--gray-600), var(--tw-border-opacity));
            /* Equivalent to dark:bg-gray-700 */
            border-color: #718096;
            /* Equivalent to dark:border-gray-600 */
            color: rgb(255 255 255 / var(--tw-text-opacity)) !important;
            /* Equivalent to dark:text-white */
            /* placeholder-color: #cbd5e0; /* Equivalent to dark:placeholder-gray-400 */
            color: rgb(255 255 255 / var(--tw-text-opacity)) !important;
            /* Equivalent to dark:placeholder-gray-400 */
            outline: 0;
            padding: 0.625rem;
            /* Equivalent to p-2.5 */
            width: 100%;
            /* Equivalent to block w-full */
        }

        .dark .form-field {
            background-color: rgba(var(--gray-700), var(--tw-bg-opacity)) !important;
            --tw-bg-opacity: 1;
            border-color: rgba(var(--gray-600), var(--tw-border-opacity));
            /* Equivalent to dark:bg-gray-700 */
            border-color: #718096;
            /* Equivalent to dark:border-gray-600 */
            color: rgb(255 255 255 / var(--tw-text-opacity)) !important;
            /* Equivalent to dark:text-white */
            /* placeholder-color: #cbd5e0; /* Equivalent to dark:placeholder-gray-400 */
            color: rgb(255 255 255 / var(--tw-text-opacity)) !important;
            /* Equivalent to dark:placeholder-gray-400 */
            outline: 0;
            padding: 0.625rem;
            /* Equivalent to p-2.5 */
            width: 100%;

            /* Equivalent to block w-full */
        }

        .dark .form-elements {
            background-color: rgba(var(--gray-700), var(--tw-bg-opacity)) !important;
            --tw-bg-opacity: 1;
            border-color: rgba(var(--gray-600), var(--tw-border-opacity));
            /* Equivalent to dark:bg-gray-700 */
            border-color: #718096;
            /* Equivalent to dark:border-gray-600 */
            color: rgb(255 255 255 / var(--tw-text-opacity)) !important;
            /* Equivalent to dark:text-white */
            /* placeholder-color: #cbd5e0; /* Equivalent to dark:placeholder-gray-400 */
            color: rgb(255 255 255 / var(--tw-text-opacity)) !important;
            /* Equivalent to dark:placeholder-gray-400 */
            outline: 0;
            padding: 0.625rem;
            /* Equivalent to p-2.5 */
            width: 100%;

            /* Equivalent to block w-full */
        }

        .dark input {
            color: black !important;
        }

        .dark .form-control {
            color: black !important;
        }

        .dark #first_name {
            color: white !important;

        }



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
        .other-wrap,
        .multiple-wrap {
            display: none !important;
        }

        #userName-preview {
            pointer-events: none !important;
            user-select: none !important;
            background-color: #e9ecef !important;
            /* Optional: mimic the appearance of a read-only input */
        }
        
        input[type="radio"] {
            margin: 7px !important;
        }

        input[type="checkbox"] {
            margin: 7px !important;
        }

        .prev-holder {
            margin-top: 7px !important;

        }
    </style>
</x-filament::page>
