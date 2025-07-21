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
        <input type="hidden" id="recordId" value="<?php echo $record->id; ?>">
        <div class="card-body">
            <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                <?= $langData['Title'] ?>
            </label>
            <input type="text" id="name" value="<?php echo $record->title; ?>"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                placeholder="<?= $langData['Enter the title'] ?>" required />
            <br>
            <div id="fb-editor" class="bg-white dark:bg-gray-800">
                <!-- Your form builder content here -->
            </div>
            <br>
            <!-- Add Save Form Button -->

        </div>
        <button id="save-form-button" onclick="saveForm()"
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
    <script src="https://cdn.jsdelivr.net/npm/moment-hijri@2.2.1/moment-hijri.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/abublihi/datepicker-hijri@v1.1/build/datepicker-hijri.js"></script>
    <script>
        // Define custom fields including Countries dropdown
        var fields = [{
                type: 'arab-countries-select',
                label: 'Country',
                className: 'form-control',
                name: 'country',
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

                        // Initialize Select2 after populating the options
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


        // Initialize FormBuilder
        var fbEditor = document.getElementById('fb-editor');
        var options = {
            disableFields: ['autocomplete', 'button', 'header', 'hidden'],
            replaceFields: [{
                type: "number",
                label: "Number Condition", // Updated label
            }],
            fields: fields, // Include custom fields
            templates: templates, // Include custom templates
            typeUserDisabledAttrs: {
                'number': ['required']
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
                saveForm(formData);
            }
        };

        var formBuilder = $(fbEditor).formBuilder(options);

        $(function() {
            var id = $('#recordId').val();
            var edit = "{{ route('GetFormBuilderEdit') }}";

            // Ensure moment.js is using the Arabic locale for Hijri date formatting
            moment.locale('ar'); // Set locale to Arabic for correct formatting

            $.ajax({
                type: 'get',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
                url: edit,
                data: {
                    'id': id,
                },
                success: function(data) {
                    console.log(data);  // Log the entire response
                    
                    // Parse the content if it's a string
                    let content = Array.isArray(data.content) ? data.content : JSON.parse(data.content);

                    let dates = [];
                    content.forEach(function(field) {
                        if (field.name === 'hijri_date') {
                            dates.push(field.value);
                        }
                    });

                    setTimeout(function() {
                        console.log($('.hijri-input').length);

                        $('.hijri-input').each(function(index) {
                            if (dates[index]) {
                                $(this).val(dates[index]);
                            }
                        });
                    }, 500);

                    formBuilder.actions.setData(content);
                }

            });
        });




        // Fetch Hijri Date and set it in the field
        function fetchDateHigri(elementId, selectedValue) {
            console.log('Element ID:', elementId, 'Selected Value:', selectedValue);

            var selectElement = document.getElementById(elementId);
            if (selectElement && selectElement.dataset.fetched !== 'true') {
                var hijriDate = selectedValue; // Hijri date value from the database
                if (hijriDate) {
                    // Find the input element for the Hijri date picker
                    var hijriDateInput = document.querySelector(`#${elementId}`);
                    if (hijriDateInput) {
                        hijriDateInput.value = hijriDate; // Set the formatted value in the input field
                        console.log('Hijri Date set:', hijriDate);
                    }
                }
                selectElement.dataset.fetched = 'true'; // Mark this field as fetched
            } else {
                console.log('Hijri date already fetched for:', elementId);
            }
        }




        // Save Form Data
        function saveForm(formData) {
            var id = $('#recordId').val();
            var formContent = formBuilder.actions.getData();

            var locale = @json(app()->getLocale());
            var url = '{{ URL('update-form', ['locale' => '__LOCALE__']) }}';
            url = url.replace('__LOCALE__', encodeURIComponent(locale));

            $.ajax({
                type: 'post',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token')
                },
                url: url,
                data: {
                    'form': formContent,
                    'name': $("#name").val(),
                    'id': id,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(response) {
                    Swal.fire({
                        title: 'Success',
                        text: 'Data saved successfully',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3085d6'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.history.back();
                        }
                    });
                },
                error: function(response) {
                    // Handle errors here
                }
            });
        }
    </script>



    <!-- Include Flowbite CSS and JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <!-- Include CSS for Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Include JavaScript for Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>




    <style>
        .form-actions {
            display: none !important;
        }

        /* Custom styles for the SweetAlert2 modal and buttons */
        .custom-swal-popup {
            border-radius: 15px !important;
            /* Change the color as needed */
        }

        .custom-swal-button {
            border-radius: 15px !important;

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

        :is(.dark .dark\:bg-gray-700) {

            background-color: rgba(var(--gray-700), var(--tw-bg-opacity));
            */
        }

        .dark #name {
            color: white !important;

        }

        .formbuilder-icon-autocomplete,
        .formbuilder-icon-button,
        .formbuilder-icon-header,
        .formbuilder-icon-hidden {
            display: none !important;
        }

        /*.required-wrap, */
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
