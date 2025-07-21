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

    <div class="progress-wrapper" style="width:100%">
        <div id="progress-bar-container">
            <ul class="steps-container">
                <li class="step step01 active">
                    <div class="step-inner">
                        <?= $langData['Step 1'] ?>
                    </div>
                </li>
                <li class="step step02">
                    <div class="step-inner">
                        <?= $langData['Step 2'] ?>
                    </div>
                </li>
                <li class="step step03">
                    <div class="step-inner">
                        <?= $langData['Step 3'] ?>
                    </div>
                </li>
            </ul>
            <div id="line">
                <div id="line-progress"></div>
            </div>
            <div id="progress-content-section">
                <!-- Step 1 -->
                <div class="section-content step1 active">
                    <h2 class="text-lg font-semibold mb-2">
                        <?= $langData['Step 1'] ?>
                    </h2>
                    <p class="text-gray-700">
                        <?= $langData['Step 1 Description'] ?>
                    </p>
                    @if (isset(auth()->user()->faculty_id))
                        <label for="selectedFaculty"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"><?= $langData['Faculty'] ?></label>
                        <select id="selectedFaculty"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option selected value="{{ auth()->user()->faculty_id }}">
                                {{ auth()->user()->faculty->ar_name }}</option>
                        </select>
                        <input type="hidden" id="faculty" value="{{ auth()->user()->faculty_id }}">
                    @else
                        <label for="faculty"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"><?= $langData['Faculty'] ?></label>
                        <select id="faculty"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option selected>Select</option>
                        </select>
                    @endif
                    <label for="department"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"><?= $langData['Departments'] ?></label>
                    <select id="department"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option selected><?= $langData['Choose a Department'] ?></option>
                    </select>
                    <label for="mainTopic" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        <?= $langData['Main Topic'] ?>
                    </label>
                    <select id="mainTopic"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option selected>Select</option>
                    </select>
                    <label for="subTopic"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"><?= $langData['Sub Topic'] ?></label>
                    <select id="subTopic"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option selected><?= $langData['Choose a Sub Topic'] ?></option>
                    </select>
                    <br>
                    <button style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                        id="next1"
                        class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-action fi-ac-btn-action"><?= $langData['Next'] ?></button>
                </div>
                <!-- Step 2 -->
                <div class="section-content step2">
                    <h2 class="text-lg font-semibold mb-2"><?= $langData['Step 2'] ?></h2>
                    <p class="text-gray-700"><?= $langData['Step 2 Description'] ?></p>
                    <div id="form-entries-container">
                        <!-- Form entries will be appended here -->
                    </div>

                    <!-- Hidden template for form entries -->
                    <div id="form-entry-template" class="form-entry" style="display: none;">
                        <label for="mainTopic" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            <?= $langData['Axis title'] ?>: <span class="topic-title-text" style="color: red"></span>
                        </label>
                        <input name="topicTitle1[]" class="topicTitle" type="hidden" value="">
                        <input name="topicIdForm1[]" class="topicIdForm" type="hidden" value="">
                        <div name="fb-editor-edit[]" style="width: 100%"
                            class="bg-white dark:bg-gray-800 fb-editor-edit">
                            <!-- Form builder content will be rendered here -->
                        </div>
                    </div>

                    <br>
                    <button id="prev2"
                        class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-action fi-ac-btn-action"
                        style="--c-400: var(--primary-400); --c-500: var(--primary-500); --c-600: var(--primary-600);"
                        class="mt-4 bg-gray-500 text-white px-4 py-2 rounded"><?= $langData['Previous'] ?></button>
                    <button id="next2"
                        class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-action fi-ac-btn-action"
                        style="--c-400: var(--primary-400); --c-500: var(--primary-500); --c-600: var(--primary-600);"
                        class="mt-4 bg-gray-500 text-white px-4 py-2 rounded"><?= $langData['Next'] ?></button>
                </div>




                <!-- Step 3 -->
                <div class="section-content step3">
                    <h2 class="text-lg font-semibold mb-2"><?= $langData['Step 3'] ?></h2>
                    <p class="text-gray-700"><?= $langData['Step 3 Description'] ?></p>

                    <!-- File Upload Section -->
                    <div class="upload-photos">
                        <label for="photoUpload" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            <?= $langData['Upload Photos'] ?>
                        </label>

                        <!-- Custom File Upload Button -->
                        <div class="file-upload-container">
                            <input type="file" id="photoUpload" name="uploadedPhotos[]" multiple
                                class="file-input hidden" />
                            <button type="button" id="customUploadBtn" class="file-upload-btn">
                                <i class="fa fa-upload"></i> <?= $langData['Choose Files'] ?>
                            </button>
                        </div>

                        <!-- File Previews Section -->
                        <div id="filePreviews" class="file-previews mt-3">
                            <!-- Previews will be appended here -->
                        </div>
                    </div>

                    <br>
                    <button id="prev3"   class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-action fi-ac-btn-action"
                    style="--c-400: var(--primary-400); --c-500: var(--primary-500); --c-600: var(--primary-600); margin:5px"
                    class="mt-4 bg-gray-500 text-white px-4 py-2 rounded"><?= $langData['Previous'] ?>

                    <form id="finalForm" style="display: inline-block;">
                        <!-- Hidden inputs to store form data -->
                        <input type="hidden" name="faculty" id="finalFaculty">
                        <input type="hidden" name="department" id="finalDepartment">
                        <input type="hidden" name="mainTopic" id="finalMainTopic">
                        <input type="hidden" name="subTopic" id="finalSubTopic">
                        <input type="hidden" name="topicTitle" id="finalTopicTitle">
                        <input type="hidden" name="topicId" id="finalTopicId">
                        <input type="hidden" name="formData" id="finalFormData">

                        <button type="submit" id="submitForm"
                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-action fi-ac-btn-action"
                            style="--c-400: var(--primary-400); --c-500: var(--primary-500); --c-600: var(--primary-600);"
                            class="mt-4 bg-gray-500 text-white px-4 py-2 rounded"><?= $langData['Send'] ?></button>

                    </form>
                </div>





                <!-- Dropzone CSS -->




            </div>
        </div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-hijri/2.2.3/moment-hijri.min.js"></script>

    <!-- Pikaday Library -->
    <!-- Load Moment Hijri.js -->
    <script src="https://cdn.jsdelivr.net/npm/moment-hijri@2.2.1/moment-hijri.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/abublihi/datepicker-hijri@v1.1/build/datepicker-hijri.js"></script>


    <script>
        var $langData = {
            'Choose a Faculty': `<?= $langData['Choose a Faculty'] ?>`,
            'Choose a Topic': `<?= $langData['Choose a Topic'] ?>`,
            'Choose a Department': `<?= $langData['Choose a Department'] ?>`,
            'There are no faculties': `<?= $langData['There are no faculties'] ?>`,
            'No departments found': `<?= $langData['No departments found'] ?>`,
            'Choose a Sub Topic': `<?= $langData['Choose a Sub Topic'] ?>`,
            'Success': `<?= $langData['Success'] ?>`,
            'saving data': `<?= $langData['saving data'] ?>`,
            'ok': `<?= $langData['ok'] ?>`,
            'Add another': `<?= $langData['Add another'] ?>`,
            'error': `<?= $langData['error'] ?>`,
        };

        $(".step").click(function() {
            $(this).addClass("active").prevAll().addClass("active");
            $(this).nextAll().removeClass("active");
        });

        // $(".step01").click(function() {
        //     $("#line-progress").css("width", "8%");
        //     $(".step1").addClass("active").siblings().removeClass("active");
        // });

        // $(".step02").click(function() {
        //     $("#line-progress").css("width", "50%");
        //     $(".step2").addClass("active").siblings().removeClass("active");
        // });

        // $(".step03").click(function() {
        //     $("#line-progress").css("width", "100%");
        //     $(".step3").addClass("active").siblings().removeClass("active");
        // });


        $(document).ready(function() {
            // Event listener for Step 1 next button
            $('#next1').on('click', function() {
                var faculty = $('#faculty').val();
                var department = $('#department').val();
                var mainTopic = $('#mainTopic').val();
                var subTopic = $('#subTopic').val();

                $('#finalFaculty').val(faculty);
                $('#finalDepartment').val(department);
                $('#finalMainTopic').val(mainTopic);
                $('#finalSubTopic').val(subTopic);

                if (validateStep1()) {
                    $("#line-progress").css("width", "50%");
                    $(".step2").addClass("active").siblings().removeClass("active");
                    $(".step.step02").addClass("active").prevAll().addClass("active");
                }
            });

            // Event listener for Step 2 next button

            // Event listener for Step 2 previous button
            $('#prev2').on('click', function() {
                // Navigate back to Step 1
                $('.step2').removeClass('active');
                $('.step1').addClass('active');
                $('#line-progress').css('width', '33%');
            });

            // Event listener for Step 3 previous button
            $('#prev3').on('click', function() {
                // Navigate back to Step 2
                $('.step3').removeClass('active');
                $('.step2').addClass('active');
                $('#line-progress').css('width', '66%');
            });



            // Populate select options using AJAX
            function populateSelect(url, selectId, defaultText) {
                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        $(selectId).empty();
                        $(selectId).append($('<option>', {
                            value: '',
                            text: defaultText // Use the passed defaultText directly
                        }));
                        $.each(response, function(index, item) {
                            $(selectId).append($('<option>', {
                                value: item.id,
                                text: item.name || item.title
                            }));
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            }

            // Translation data (assuming $langData is available and contains translations)
            var locale = @json(app()->getLocale());

            var facultiesUrl =
                '{{ route('getFaculites', ['locale' => '__LOCALE__']) }}';
            facultiesUrl = facultiesUrl.replace('__LOCALE__', encodeURIComponent(locale));

            var getFacultiesUrl = facultiesUrl;
            var getTopicUrl = "{{ route('getTopic') }}";

            // Function to populate a select element with data from an AJAX request
            // function populateSelect(url, selectElementId, defaultText) {
            //     $.ajax({
            //         url: url,
            //         type: 'GET', // Assuming a GET request, change if necessary
            //         dataType: 'json',
            //         success: function(response) {
            //             var $select = $(selectElementId);
            //             $select.empty();

            //             // Add default option
            //             $select.append($('<option>', {
            //                 value: '',
            //                 text: defaultText
            //             }));

            //             if (response.length > 0) {
            //                 // Populate with data
            //                 $.each(response, function(index, item) {
            //                     $select.append($('<option>', {
            //                         value: item.id,
            //                         text: item.name
            //                     }));
            //                 });
            //             } else {
            //                 // No data found
            //                 $select.append($('<option>', {
            //                     value: '',
            //                     text: $langData['There are no faculties']
            //                 }));
            //             }
            //         },
            //         error: function(xhr, status, error) {
            //             console.error(error);
            //         }
            //     });
            // }

            // Populate faculty options on page load
            populateSelect(getFacultiesUrl, '#faculty', $langData['Choose a Faculty']);

            // Populate main topics on page load
            populateSelect(getTopicUrl, '#mainTopic', $langData['Choose a Topic']);

            var oneFaculty = document.getElementById('faculty').value;
            if (oneFaculty) {
                var depUrl = '{{ route('getDepartement', ['locale' => '__LOCALE__']) }}';
                depUrl = depUrl.replace('__LOCALE__', encodeURIComponent(locale));

                var facultyId = oneFaculty;
                if (facultyId) {
                    $.ajax({
                        url: depUrl,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            faculty: facultyId,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $('#department').empty(); // Clear the department dropdown

                            // If there are departments, add them
                            if (response.length > 0) {
                                // Append departments directly and select the first one
                                $.each(response, function(index, department) {
                                    $('#department').append($('<option>', {
                                        value: department.id,
                                        text: department.name
                                    }));
                                });

                                // Select the first department automatically
                                $('#department').val(response[0].id).change();
                            } else {
                                // If no departments found, show a message
                                $('#department').append($('<option>', {
                                    value: '',
                                    text: $langData['No departments found']
                                }));
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                } else {
                    $('#department').empty().append($('<option>', {
                        value: '',
                        text: $langData['Choose a Department']
                    }));
                }
            }


            // Event listener for faculty selection change
            $('#faculty').on('change', function() {
                // var depUrl = "{{ route('getDepartement') }}";
                var depUrl =
                    '{{ route('getDepartement', ['locale' => '__LOCALE__']) }}';
                depUrl = depUrl.replace('__LOCALE__', encodeURIComponent(locale));

                var facultyId = $(this).val();
                if (facultyId) {
                    $.ajax({
                        url: depUrl,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            faculty: facultyId,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $('#department').empty();
                            $('#department').append($('<option>', {
                                value: '',
                                text: $langData['Choose a Department']
                            }));

                            if (response.length > 0) {
                                $.each(response, function(index, department) {
                                    $('#department').append($('<option>', {
                                        value: department.id,
                                        text: department.name
                                    }));
                                });
                            } else {
                                $('#department').append($('<option>', {
                                    value: '',
                                    text: $langData['No departments found']
                                }));
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                } else {
                    $('#department').empty().append($('<option>', {
                        value: '',
                        text: $langData['Choose a Department']
                    }));
                }

            });

            // Event listener for main topic selection change
            $('#mainTopic').on('change', function() {
                var subUrl = "{{ route('getSubTopic') }}";
                var topicId = $(this).val();
                if (topicId) {
                    $.ajax({
                        url: subUrl,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            mainTopic: topicId,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $('#subTopic').empty();
                            $('#subTopic').append($('<option>', {
                                value: '',
                                text: $langData['Choose a Sub Topic']
                            }));
                            $.each(response, function(index, subTopic) {
                                $('#subTopic').append($('<option>', {
                                    value: subTopic.id,
                                    text: subTopic.title
                                }));
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                } else {
                    $('#subTopic').empty().append($('<option>', {
                        value: '',
                        text: $langData['Choose a Sub Topic']
                    }));
                }
            });

            $('#subTopic').on('change', function() {
                var subTopicId = $(this).val();
                var axisiform = "{{ route('formBuilderAxsisTopic') }}";

                if (subTopicId) {
                    $.ajax({
                        url: axisiform,
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            subTopicId: subTopicId,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            // Clear previous form entries
                            $('#form-entries-container').empty();

                            // Check if the response is an array
                            if (Array.isArray(response)) {
                                response.forEach(function(item, index) {
                                    // Clone the template
                                    var $clone = $('#form-entry-template').clone()
                                        .removeAttr('id').show();

                                    // Set the values
                                    $clone.find('.topicTitle').val(item.axisTitle);
                                    $clone.find('.topicIdForm').val(item.topicId);
                                    $clone.find('.topic-title-text').text(item
                                        .axisTitle);

                                    // Append the clone to the container
                                    $('#form-entries-container').append($clone);

                                    // Define custom fields, including the Countries dropdown
                                    var fields = [{
                                        type: 'arab-countries-select',
                                        label: 'Country',
                                        className: 'form-control',
                                        name: 'country', // Ensure the select element has a unique name
                                        value: 'SA', // Set a default selected value (can be changed dynamically)
                                        icon: '🌍'
                                    }, {
                                        type: 'text',
                                        label: 'Uploader Name',
                                        className: 'form-control',
                                        name: 'userName',
                                        value: '.', // Set a default selected value (can be changed dynamically)

                                    }, {
                                        type: 'hijri-date-picker',
                                        label: 'Hijri Date',
                                        className: 'form-control',
                                        name: 'hijri_date'
                                    }, ];

                                    // Define custom templates for the custom fields
                                    var templates = {
                                        'arab-countries-select': function(
                                            fieldData) {
                                            const randomNumber = Math.floor(Math
                                                .random() * 10000
                                            ); // Generates a random number between 0 and 9999
                                            const uniqueId =
                                                `${fieldData.name}_${randomNumber}`;

                                            return {
                                                field: `<select id="${uniqueId}" name="${fieldData.name}" class="form-control"><option>Loading countries...</option></select>`,
                                                onRender: function() {
                                                    // Fetch and populate Arab countries only if not already populated
                                                    fetchCountries(uniqueId,
                                                        fieldData.value);
                                                }
                                            };
                                        },
                                        'hijri-date-picker': function(fieldData) {
                                            const uniqueId =
                                                `${fieldData.name}_${Math.floor(Math.random() * 10000)}`;
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
                                                    $('#' + uniqueId +
                                                        '_calender_display'
                                                    ).on('focus',
                                                        function() {
                                                            // Check if the date picker is initialized
                                                            if (!$(this)
                                                                .data(
                                                                    'datepicker'
                                                                )) {
                                                                // Initialize the datepicker on focus
                                                                $(this)
                                                                    .datepicker({
                                                                        format: 'iYYYY/iMM/iDD',
                                                                        autoclose: true, // Close the picker after selection
                                                                        clearBtn: true, // Add a clear button
                                                                        todayHighlight: true // Highlight today's date
                                                                    });
                                                            }
                                                        });

                                                    // Capture the date selection and set it into the input fields
                                                    $('#' + uniqueId +
                                                        '_calender_display'
                                                    ).on('change',
                                                        function() {
                                                            const
                                                                selectedDate =
                                                                $(this)
                                                                .val();
                                                            // Set the value to both the display and hidden input fields
                                                            $('#' + uniqueId +
                                                                    '_calender_hidden'
                                                                )
                                                                .val(
                                                                    selectedDate
                                                                );

                                                            // Trigger the 'keyup' event on the hidden input after setting the value
                                                            $('#' + uniqueId +
                                                                    '_calender_hidden'
                                                                )
                                                                .trigger(
                                                                    'change'
                                                                );


                                                        });
                                                }
                                            };


                                        }
                                    };

                                    // Function to fetch countries and populate the select element
                                    function fetchCountries(elementId, selectedValue) {
                                        var selectElement = document.getElementById(
                                            elementId);
                                        if (selectElement && selectElement.dataset
                                            .fetched !== 'true') {
                                            var appUrl = '{{ env('APP_URL') }}';
                                            var localJsonUrl = appUrl +
                                                '/admin/countries-json';
                                            console.log(localJsonUrl);

                                            // Use fetch to get the data from the generated URL
                                            fetch(localJsonUrl)
                                                .then(response => response.json())
                                                .then(data => {
                                                    // Filter countries to include only Arab countries or those with available translations
                                                    var options = data
                                                        .map(country => {
                                                            var countryName = (
                                                                    country
                                                                    .translations &&
                                                                    country
                                                                    .translations
                                                                    .ara) ?
                                                                country
                                                                .translations
                                                                .ara.common :
                                                                'Unknown Country'; // Fallback for non-Arab countries
                                                            var isSelected =
                                                                countryName ===
                                                                selectedValue ?
                                                                'selected' : '';
                                                            return `<option value="${countryName}" ${isSelected}>${countryName}</option>`;
                                                        })
                                                        .join('');

                                                    // Update the select element with the options
                                                    selectElement.innerHTML =
                                                        options;
                                                    selectElement.dataset.fetched =
                                                        'true'; // Mark as fetched

                                                    // Initialize Select2 after populating options
                                                    $(selectElement).select2({
                                                        placeholder: "Select a country", // Placeholder text when no country is selected
                                                        width: '100%' // Make it responsive (optional)
                                                    });
                                                })
                                                .catch(error => {
                                                    selectElement.innerHTML =
                                                        '<option>Error loading countries</option>';
                                                    console.error(
                                                        'Error fetching local JSON:',
                                                        error);
                                                });
                                        }
                                    }

                                    // Options for the form builder, including custom fields and templates
                                    var options = {
                                        disableFields: ['autocomplete', 'button',
                                            'header', 'hidden'
                                        ],
                                        replaceFields: [{
                                            type: "number",
                                            label: "Number Condition", // Update label here
                                        }],
                                        fields: fields, // Include custom fields
                                        templates: templates, // Include custom templates
                                        typeUserAttrs: {
                                            'arab-countries-select': {
                                                placeholder: {
                                                    label: 'Placeholder',
                                                    value: ''
                                                }
                                            }
                                        },
                                    };
                                    // Initialize dates array
                                    let dates = [];

                                    // Parse the field data if it's a string and collect hijri_date values
                                    if (typeof item.field_data === 'string') {
                                        try {
                                            item.field_data = JSON.parse(item
                                                .field_data); // Parse the string data
                                        } catch (error) {
                                            console.error('Failed to parse field_data:',
                                                error);
                                            item
                                                .field_data = []; // Fallback to an empty array if parsing fails
                                        }
                                    }

                                    // Extract hijri_date values from field_data
                                    item.field_data.forEach(function(field) {
                                        if (field.name === 'hijri_date') {
                                            dates.push(field
                                                .value
                                                ); // Store the value of 'hijri_date' fields
                                        }
                                    });

                                    // Initialize the form builder for the cloned element
                                    $clone.find('.fb-editor-edit').formBuilder(options)
                                        .promise
                                        .then(
                                            function(fb) {
                                                // Delay to ensure form inputs are fully rendered
                                                setTimeout(function() {
                                                        // Check if hijri-input fields are generated
                                                        const hijriInputs = $clone
                                                            .find('.hijri-input');
                                                        console.log(
                                                            'Hijri Inputs count:',
                                                            hijriInputs.length);
                                                        console.log('Dates count:',
                                                            dates.length);

                                                        // Ensure that hijri-input fields are available and match the length of dates array
                                                        if (hijriInputs.length ===
                                                            dates.length) {
                                                            hijriInputs.each(
                                                                function(
                                                                    index) {
                                                                    if (dates[
                                                                            index
                                                                        ]) {
                                                                        $(this)
                                                                            .val(
                                                                                dates[
                                                                                    index
                                                                                ]
                                                                            ); // Set the corresponding value from the dates array
                                                                    }
                                                                });
                                                        } else {
                                                            console.error(
                                                                'Mismatch between hijri-input fields and dates array length'
                                                            );
                                                        }
                                                    },
                                                    1000
                                                    ); // Adjust timeout as necessary (increase if necessary to ensure full rendering)

                                                // Set form data and disable number inputs
                                                fb.actions.setData(item.field_data);
                                                $clone.find(
                                                    '.fb-editor-edit input[type="number"]'
                                                ).prop('disabled', true);
                                            }
                                        );

                                    // Append the cloned form entry to the container
                                    $('#form-entries-container').append($clone);
                                });
                            } else {
                                console.error('Response is not an array:', response);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                }
            });



            $('#customUploadBtn').on('click', function() {
                $('#photoUpload').click();
            });

            $('#photoUpload').on('change', function(e) {
                const files = e.target.files;
                const filePreviewsContainer = $('#filePreviews');
                filePreviewsContainer.empty();

                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    const reader = new FileReader();

                    reader.onload = function(event) {
                        const previewElement = $('<div>', {
                            class: 'file-preview'
                        }).append(
                            $('<img>', {
                                src: event.target.result,
                                alt: 'file preview'
                            }),
                            $('<button>', {
                                class: 'remove-file-btn',
                                text: 'X',
                                'data-file-index': i
                            })
                        );
                        filePreviewsContainer.append(previewElement);
                    };

                    reader.readAsDataURL(file);
                }
            });

            $('#filePreviews').on('click', '.remove-file-btn', function() {
                const fileIndex = $(this).data('file-index');
                const fileInput = $('#photoUpload')[0];
                const filesArray = Array.from(fileInput.files);

                filesArray.splice(fileIndex, 1);
                const newFileList = new DataTransfer();
                filesArray.forEach(file => newFileList.items.add(file));
                fileInput.files = newFileList.files;

                $(this).parent().remove();
            });

            let formDataArray = [];

            $('#next2').on('click', function() {
                formDataArray = []; // Clear previous data

                $('#form-entries-container .form-entry').each(function(index, element) {
                    var topicTitle = $(element).find('.topicTitle').val();
                    var topicId = $(element).find('.topicIdForm').val();
                    var formBuilderInstance = $(element).find('.fb-editor-edit').data(
                        'formBuilder');
                    var formData = formBuilderInstance.actions.getData('json');

                    formDataArray.push({
                        topicTitle: topicTitle,
                        topicId: topicId,
                        formData: formData
                    });
                });

                if (validateStep1()) {
                    $("#line-progress").css("width", "100%");
                    $(".step3").addClass("active").siblings().removeClass("active");
                    $(".step.step03").addClass("active").prevAll().addClass("active");
                }
            });

            $('#finalForm').on('submit', function(e) {
                e.preventDefault();

                var storeAgenda = "{{ route('storeAgenda') }}";
                var formData = new FormData();

                formData.append('faculty', $('#finalFaculty').val());
                formData.append('department', $('#finalDepartment').val());
                formData.append('mainTopic', $('#finalMainTopic').val());
                formData.append('subTopic', $('#finalSubTopic').val());
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                // Stringify formDataArray and append it to formData
                formData.append('formDataArray', JSON.stringify(formDataArray));

                const uploadedFiles = Array.from($('#photoUpload')[0].files);
                uploadedFiles.forEach(function(file) {
                    formData.append('uploadedPhotos[]', file); // Append each file
                });

                // Target the submit button
                var submitButton = $('#submitForm');

                // Disable the submit button
                submitButton.prop('disabled', true).text('تحميل...');

                $.ajax({
                    url: storeAgenda,
                    type: 'POST',
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        // Swal.fire({
                        //     title: $langData['Success'],
                        //     text: $langData['saving data'],
                        //     icon: 'success',
                        //     showCancelButton: true,
                        //     confirmButtonText: $langData['ok'],
                        //     cancelButtonText: $langData['Add another'],
                        //     confirmButtonColor: '#3085d6',
                        //     cancelButtonColor: '#d33'
                        // })
                        Swal.fire({
                            position: 'center',
                            title: $langData['Success'],
                            text: $langData['saving data'],
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500
                        })
                        .then((result) => {
                            // if (result.isConfirmed) {
                                window.history.back();
                            // } else if (result.dismiss === Swal.DismissReason.cancel) {
                            //     location.reload();
                            // }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        var errorMessage = xhr.responseJSON?.message || '';

                        if (xhr.responseJSON?.errors) {
                            var errorMessages = Object.values(xhr.responseJSON.errors).flat()
                                .join('<br>');
                            errorMessage += errorMessages;
                        }

                        // Re-enable the submit button after error
                        submitButton.prop('disabled', false).text($langData['Send']);

                        Swal.fire({
                            title: $langData['error'],
                            html: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#d33'
                        });
                    }
                });
            });


















            // Save form data to local storage when "Next" button is clicked
            // $('#next2').on('click', function() {
            //     var axisId = $('#topicId').val();
            //     var formBuilder = $('#fb-editor-edit').data('formBuilder');
            //     if (formBuilder) {
            //         var formData = formBuilder.actions.getData();
            //         saveFormData(axisId, formData);
            //     }
            //     // Logic to proceed to the next step
            // });





            function validateStep1() {
                let isValid = true;

                if (!$('#faculty').val()) {
                    $('#faculty').css('border', '1px solid red');
                    isValid = false;
                } else {
                    $('#faculty').css('border', '');
                }

                if (!$('#department').val()) {
                    $('#department').css('border', '1px solid red');
                    isValid = false;
                } else {
                    $('#department').css('border', '');
                }

                if (!$('#mainTopic').val()) {
                    $('#mainTopic').css('border', '1px solid red');
                    isValid = false;
                } else {
                    $('#mainTopic').css('border', '');
                }

                if (!$('#subTopic').val()) {
                    $('#subTopic').css('border', '1px solid red');
                    isValid = false;
                } else {
                    $('#subTopic').css('border', '');
                }

                return isValid;
            }
            // Next button for step 1
            // $('#next1').click(function() {
            //     if (validateStep1()) {
            //         $("#line-progress").css("width", "50%");
            //         $(".step2").addClass("active").siblings().removeClass("active");
            //         $(".step.step02").addClass("active").prevAll().addClass("active");
            //     }
            // });
            // Previous button for step 2
            $('#prev2').click(function() {
                $("#line-progress").css("width", "8%");
                $(".step1").addClass("active").siblings().removeClass("active");
                $(".step.step01").addClass("active").siblings().removeClass("active");
            });
            // Next button for step 2
            $('#next2').click(function() {
                $("#line-progress").css("width", "100%");
                $(".step3").addClass("active").siblings().removeClass("active");
                $(".step.step03").addClass("active").prevAll().addClass("active");
            });
            // Previous button for step 3
            $('#prev3').click(function() {
                $("#line-progress").css("width", "50%");
                $(".step2").addClass("active").siblings().removeClass("active");
                $(".step.step02").addClass("active").siblings().removeClass("active");
            });

        });
    </script>

    <!-- Include Flowbite CSS and JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <!-- Override dark mode styles -->
    <style>
        /* Style the custom upload button */
        /* General Styles for Upload Section */
        .file-upload-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-top: 10px;
        }

        .file-upload-btn {
            padding: 12px 20px;
            font-size: 16px;
            background-color: #007BFF;
            /* Primary blue color */
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            transition: background-color 0.3s ease;
        }

        .file-upload-btn i {
            margin-right: 8px;
        }

        /* File Input - Hidden but accessible via the button */
        .file-input {
            display: none;
        }

        /* File Previews Container */
        .file-previews {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .file-preview {
            position: relative;
            width: 120px;
            height: 120px;
            overflow: hidden;
            background-color: #f3f3f3;
            /* Light background */
            border-radius: 10px;
            border: 2px solid #ddd;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .file-preview:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .file-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        /* Remove Button */
        /* Remove Button (X) */
        .remove-file-btn {
            position: absolute;
            top: 1px;
            right: 2px;
            background-color: #ff4d4d;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            opacity: 0.8;
            transition: opacity 0.2s ease, background-color 0.3s ease;
            z-index: 10;
            /* Ensure it's on top of the preview */
        }

        .remove-file-btn:hover {
            opacity: 1;
            background-color: #ff1a1a;
            /* Darker red on hover */
        }

        /* Prevent clipping of the remove button */
        .file-preview {
            position: relative;
            /* Ensure it's positioned relative to its container */
            width: 120px;
            height: 120px;
            overflow: hidden;
            background-color: #f3f3f3;
            /* Light background */
            border-radius: 10px;
            border: 2px solid #ddd;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Make sure images are fully visible and centered */
        .file-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }


        .remove-file-btn:hover {
            opacity: 1;
            background-color: #ff1a1a;
            /* Slightly darker red */
        }

        /* Hover effect for remove button */
        .remove-file-btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 77, 77, 0.3);
        }

        /* Adding some spacing to the whole section */
        .upload-photos {
            max-width: 600px;
            margin: 0 auto;
        }

        /* File Upload Button Hover Effects */
        .file-upload-btn:hover {
            background-color: #0056b3;
            /* Darker blue on hover */
        }

        /* Styling for the form button */
        #submitForm {
            margin-top: 20px;
            padding: 12px 20px;
            background-color: #28a745;
            /* Success green */
            color: white;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #submitForm:hover {
            background-color: #218838;
            /* Darker green on hover */
        }

        /* Customizing the text and appearance */
        .text-lg {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .text-gray-700 {
            color: #4a4a4a;
        }

        .fi-btn {
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        /* Adjusting button sizes */
        .fi-btn-size-md {
            padding: 12px 24px;
            font-size: 16px;
        }

        .form-actions {
            display: none !important;
        }

        .custom-swal-popup {
            border-radius: 15px !important;
        }

        .cb-wrap {
            position: static !important;
            /* or any other positioning value you desire */
        }

        .custom-swal-confirm-btn,
        .custom-swal-cancel-btn {
            border-radius: 15px !important;
        }

        .swal2-popup {
            border-radius: 15px !important;
        }

        .swal2-styled.swal2-confirm,
        .swal2-styled.swal2-cancel {
            border-radius: 15px !important;
        }

        .axies-content {
            display: none;
            /* Ensure this is the only relevant rule */
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

        /* .showDetails {
            display: block;
            width: 100%;
        } */

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

        * {
            margin: 0;
            padding: 0;
        }

        #progress-bar-container .steps-container li .step-inner {
            position: absolute;
            width: 100%;
            bottom: 0;
            font-size: 14px;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            /* Adjust the gap as needed */
        }

        .button-container .fi-btn {
            margin-top: 0;
            /* Remove the margin-top from the buttons */
        }

        #progress-bar-container .steps-container li.active,
        #progress-bar-container .steps-container li:hover {
            color: #444;
        }

        #progress-bar-container .steps-container li::after {
            content: " ";
            display: block;
            width: 6px;
            height: 6px;
            background-color: #777;
            margin: auto;
            border: 7px solid #fff;
            border-radius: 50%;
            margin-top: 47px;
            box-shadow: 0 2px 13px -1px rgba(0, 0, 0, 0.2);
            transition: all ease 0.25s;
        }

        #progress-bar-container li:hover::after {
            background: #555;
        }

        #progress-bar-container li.active::after {
            background: #207893;
        }

        #progress-bar-container #line {
            width: 80%;
            margin: auto;
            background-color: #eee;
            height: 6px;
            position: absolute;
            left: 10%;
            top: 43px;
            z-index: 1;
            border-radius: 50px;
            transition: all ease 0.75s;
        }

        #progress-bar-container #line-progress {
            content: " ";
            width: 8%;
            height: 100%;
            background-color: #207893;
            background: linear-gradient(to right #207893 0%, #2ea3b7 100%);
            position: absolute;
            z-index: 2;
            border-radius: 50px;
            transition: 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.25);
        }

        #progress-content-section {
            position: relative;
            top: 100px;
            width: 100%;
            margin: auto;
            border-radius: 4px;
            background-color: #fff !important;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1) !important;
        }

        #progress-content-section .section-content {
            padding: 30px 40px;
            text-align: center;
        }

        .section-content h2 {
            font-size: 17px;
            text-transform: uppercase;
            color: #333;
            letter-spacing: 1px;
        }

        .section-content.step1 label {
            margin: 8px 0;
        }

        .section-content p {
            font-size: 16px;
            line-height: 1.8rem;
            color: #777;
        }

        .section-content {
            display: none;
            animation: FadeinUp 0.7s ease 1 forwards;
            transform: translateY(15px);
            opacity: 0;
        }

        .section-content.active {
            display: block;
            opacity: 1;
        }

        .progress-wrapper {
            margin: auto;
            /* max-width: 1080px; */
        }

        #progress-bar-container {
            position: relative;
            width: 90%;
            margin: auto;
            height: 100%;
            margin-top: 65px;
        }

        #progress-bar-container ul.steps-container {
            /* padding-top: 15px; */
            z-index: 999;
            position: absolute;
            width: 100%;
            margin-top: -40px;
        }

        #progress-bar-container .steps-container li::before {
            content: " ";
            display: block;
            margin: auto;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid #aaa;
            transition: all ease 0.3s;
        }

        #progress-bar-container .steps-container li.active::before,
        #progress-bar-container .steps-container li:hover::before {
            border: 2px solid #fff;
            background-color: crimson;
        }

        #progress-bar-container .steps-container li {
            list-style: none;
            float: left;
            width: 33%;
            text-align: center;
            color: #aaa;
            text-transform: uppercase;
            font-size: 11px;
            cursor: pointer;
            font-weight: 700;
            transition: all ease 0.2s;
            vertical-align: bottom;
            height: 60px;
            position: relative;
        }

        @keyframes FadeInUp {
            0% {
                transform: translateY(15px);
                opacity: 0;
            }

            100% {
                transform: translateY(0px);
                opacity: 1;
            }
        }

        .formbuilder-icon-autocomplete,
        .formbuilder-icon-button,
        .formbuilder-icon-header,
        .formbuilder-icon-hidden {
            display: none !important;
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
        .form-elements,
        .multiple-wrap {

            display: none !important;
        }

        .delete-confirm,
        .formbuilder-icon-copy,
        .formbuilder-icon-sort-higher,
        .formbuilder-icon-sort-lower,
        .formbuilder-icon-pencil {
            display: none !important;
        }

        #userName-preview {
            pointer-events: none !important;
            user-select: none !important;
            background-color: #e9ecef !important;
            /* Optional: mimic the appearance of a read-only input */
        }

        /* Style to make list items look disabled */
        ul>.ui-sortable-handle {
            display: none !important;

            pointer-events: none !important;
            opacity: 0.5 !important;
        }

        .frmb {
            width: 100% !important;
        }

        .paragraph-field .formbuilder-icon-pencil {
            display: block !important;
        }

        .paragraph-field .formbuilder-icon-pencil.form-elements {
            display: block !important;

        }

        .paragraph-field .form-elements {

            display: block !important;
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
    @if (app()->getLocale() == 'ar')
        <style>
            #progress-bar-container ul.steps-container {
                display: flex;
            }
        </style>
    @endif
</x-filament::page>
