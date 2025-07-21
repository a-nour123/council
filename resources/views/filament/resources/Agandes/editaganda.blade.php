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
            <ul class="steps-container flex justify-between">
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
            </ul>
            <div id="line">
                <div id="line-progress"></div>
            </div>
            <div id="progress-content-section">
                <div class="section-content step1 active">
                    <h2 class="text-lg font-semibold mb-2">
                        <?= $langData['Step 1'] ?>
                    </h2>
                    <p class="text-gray-700">
                        <?= $langData['Step 1 Description'] ?>
                    </p>
                    <label for="faculty" {{-- @if (!auth()->user()->hasRole('Super Admin') || !auth()->user()->hasRole('System Admin')) style="display: none;" @endif --}}
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"><?= $langData['Faculty'] ?></label>
                    <select id="faculty" {{-- @if (!auth()->user()->hasRole('Super Admin') || !auth()->user()->hasRole('System Admin')) style="display: none;" @endif --}}
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <option selected value="{{ $agenda->departement->faculty_id }}">
                            {{ $agenda->departement->faculty->ar_name }}</option>
                    </select>
                    {{-- <select id="faculty"
                        @if (auth()->user()->position_id == 3 && auth()->user()->id != $created_by) class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            disabled
                        @else
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" @endif>
                        <option selected>Select</option>
                    </select> --}}

                    <label for="department"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"><?= $langData['Departments'] ?></label>
                    <select id="department"
                        @if (auth()->user()->position_id == 3 && auth()->user()->id != $created_by) class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                            disabled
                        @else
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" @endif>
                        {{-- <option selected><?= $langData['Choose a Department'] ?></option> --}}
                        <option selected value="{{ $departmentId }}">{{ $agenda->departement->ar_name }}</option>
                    </select>
                    <label for="mainTopic" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        <?= $langData['Main Topic'] ?>
                    </label>
                    <select id="mainTopic"
                        @if (auth()->user()->position_id == 3 && auth()->user()->id != $created_by) class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                        disabled
                    @else
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" @endif>
                        <option selected><?= $langData['Choose a Topic'] ?></option>
                    </select>
                    <label for="subTopic"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"><?= $langData['Sub Topic'] ?></label>
                    <select id="subTopic"
                        @if (auth()->user()->position_id == 3 && auth()->user()->id != $created_by) class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                            disabled
                        @else
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" @endif>
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
                    </p>
                    <div id="form-entries-container">
                        <!-- Form entries will be appended here -->
                    </div>

                    <!-- Hidden template for form entries -->
                    <div id="form-entry-template" class="form-entry" style="display: none;">
                        <label for="mainTopic" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                            <?= $langData['Axie'] ?>: <span class="topic-title-text"></span>
                        </label>
                        <input name="topicTitle1[]" class="AgendaIdForm" type="hidden" value="">
                        <input name="topicIdForm1[]" class="topicIdForm" type="hidden" value="">
                        <input name="agendatopicIdForm1[]" class="agendatopicIdForm" type="hidden" value="">
                        <div name="fb-editor-edit[]" style="width: 100%"
                            class="bg-white dark:bg-gray-800 fb-editor-edit @if (auth()->user()->position_id == 3 && auth()->user()->id != $created_by) disabled @endif">
                            <!-- Form builder content will be rendered here -->
                        </div>

                    </div>

                    <br>
                    <button id="prev2"
                        class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-action fi-ac-btn-action"
                        style="--c-400: var(--primary-400); --c-500: var(--primary-500); --c-600: var(--primary-600);"
                        class="mt-4 bg-gray-500 text-white px-4 py-2 rounded"><?= $langData['Previous'] ?></button>

                    <form id="finalForm" style="display: inline-block;">
                        <input type="hidden" name="faculty" id="finalFaculty">
                        <input type="hidden" name="department" id="finalDepartment">
                        <input type="hidden" name="mainTopic" id="finalMainTopic">
                        <input type="hidden" name="subTopic" id="finalSubTopic">
                        <input type="hidden" name="AgendaIdForm" id="finalAgendaIdForm">
                        <input type="hidden" name="agendatopicIdForm" id="finalagendatopicIdForm">
                        <input type="hidden" name="topicId" id="finalTopicId">
                        <input type="hidden" name="formData" id="finalFormData">
                        <input type="hidden" name="AgendaId" id="AgendaId" value="<?php echo $agendaId; ?>">

                        <div class="button-container">
                            <button type="submit" id="submitForm"
                                class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-action fi-ac-btn-action"
                                style="--c-400: var(--primary-400); --c-500: var(--primary-500); --c-600: var(--primary-600);"
                                class="mt-4 bg-gray-500 text-white px-4 py-2 rounded"><?= $langData['Submit'] ?></button>
                        </div>


                    </form>
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
                        <h1 for="photoUpload" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                        style="margin-top: 36px;
                            font-size: 24px;
                            color: #40408a;">
                            <?= $langData['Photos'] ?>
                            </h1>
                        <!-- Existing Photos (From Server) -->
                        <div class="file-previews mt-3" id="filePreviewsContainer">

                            @foreach ($this->photos as $photo)
                                <div class="file-preview" data-photo-id="{{ $photo->id }}">
                                    <!-- Check if the file is an image -->
                                    @if (in_array(pathinfo($photo->file_path, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']))
                                        <a href="{{ asset('storage/' . $photo->file_path) }}"
                                            download="{{ $photo->file_name }}">
                                            <img src="{{ asset('storage/' . $photo->file_path) }}"
                                                alt="Uploaded Photo" class="w-32 h-32 object-cover">
                                        </a>
                                    @else
                                        <!-- For non-image files, show a file icon with a download link -->
                                        <a href="{{ asset('storage/' . $photo->file_path) }}"
                                            download="{{ $photo->file_name }}">
                                            <button
                                                class="w-32 h-32 flex justify-center items-center bg-gray-300 text-black rounded-md">
                                                <span class="material-icons">{{ $photo->file_name }}</span>
                                            </button>
                                        </a>
                                    @endif

                                    <button class="remove-file-btn" data-file-index="0"
                                        data-photo-id="{{ $photo->id }}">X</button>
                                </div>
                            @endforeach

                        </div>

                    </div>

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
            $(".step").click(function() {
                $(this).addClass("active").prevAll().addClass("active");
                $(this).nextAll().removeClass("active");
            });




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
                        $("#line-progress").css("width", "100%");
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
                function populateSelect(url, selectId, defaultText, selectedValue) {
                    $.ajax({
                        url: url,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            $(selectId).empty();
                            $(selectId).append($('<option>', {
                                value: '',
                                text: defaultText
                            }));
                            $.each(response, function(index, item) {
                                $(selectId).append($('<option>', {
                                    value: item.id,
                                    text: item.name || item.title
                                }));
                            });
                            if (selectedValue) {
                                $(selectId).val(selectedValue).trigger('change');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                        }
                    });
                }

                // Function to set selected value for dropdowns
                function setSelectedValue(selectId, value) {
                    $(selectId).val(value).trigger('change');
                }

                // Translation data (assuming $langData is available and contains translations)
                var locale = @json(app()->getLocale());
                // Get initial values from backend
                var initialFacultyId = "{{ $faculity }}";
                var initialMainTopicId = "{{ $mainTopic }}";
                var initialSubTopicId = "{{ $subTopic }}";
                var initialDepartmentId = "{{ $departmentId }}";

                var facultiesUrl =
                    '{{ route('getFaculites', ['locale' => '__LOCALE__']) }}';
                facultiesUrl = facultiesUrl.replace('__LOCALE__', encodeURIComponent(locale));

                var getFacultiesUrl = facultiesUrl;
                var getTopicUrl = "{{ route('getTopic') }}";

                var $langData = {
                    'Choose a Faculty': `<?= $langData['Choose a Faculty'] ?>`,
                    'Choose a Topic': `<?= $langData['Choose a Topic'] ?>`,
                    'Choose a Department': `<?= $langData['Choose a Department'] ?>`
                };

                populateSelect(getFacultiesUrl, '#faculty', $langData['Choose a Faculty'], initialFacultyId);

                // Populate main topics on page load
                populateSelect(getTopicUrl, '#mainTopic', $langData['Choose a Topic'], initialMainTopicId);

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
                                $.each(response, function(index, department) {
                                    $('#department').append($('<option>', {
                                        value: department.id,
                                        text: department.name
                                    }));
                                });
                                if (initialDepartmentId) {
                                    setSelectedValue('#department', initialDepartmentId);
                                    initialDepartmentId =
                                        null; // Clear after setting to prevent overwriting on future changes
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error(error);
                            }
                        });
                    } else {
                        $('#department').empty().append($('<option>', {
                            value: '',
                            text: 'Choose a Department'
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
                                    text: 'Choose a Sub Topic'
                                }));
                                $.each(response, function(index, subTopic) {
                                    $('#subTopic').append($('<option>', {
                                        value: subTopic.id,
                                        text: subTopic.title
                                    }));
                                });
                                if (initialSubTopicId) {
                                    setSelectedValue('#subTopic', initialSubTopicId);
                                    initialSubTopicId =
                                        null; // Clear after setting to prevent overwriting on future changes
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error(error);
                            }
                        });
                    } else {
                        $('#subTopic').empty().append($('<option>', {
                            value: '',
                            text: 'Choose a Sub Topic'
                        }));
                    }
                });

                // Variable to keep track of whether it's the first change event
                let isFirstChange = true;

                $('#subTopic').on('change', function() {
                    // Get the selected sub-topic value
                    var selectedSubTopic = $(this).val();

                    // Clear the container
                    $('#form-entries-container').empty();

                    if (isFirstChange) {
                        // First change event logic
                        // Embedding PHP data into JavaScript variable
                        var AgendaTopicFormbuilderData = @json($AgendaTopicFormbuilder->toArray());

                        // Check if the response is an array
                        if (Array.isArray(AgendaTopicFormbuilderData)) {
                            AgendaTopicFormbuilderData.forEach(function(item, index) {
                                // Clone the template
                                var $clone = $('#form-entry-template').clone()
                                    .removeAttr('id').show();

                                // Set the values
                                $clone.find('.AgendaIdForm').val(item.agenda_id);
                                $clone.find('.topicIdForm').val(item.topic_id);
                                $clone.find('.agendatopicIdForm').val(item.id);

                                var i = 0;

                                $clone.find('.topic-title-text').text(1 +
                                    i++); // Update the text of the cloned element

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
                                    value: '', // Set a default selected value (can be changed dynamically)

                                }, {
                                    type: 'hijri-date-picker',
                                    label: 'Hijri Date',
                                    className: 'form-control',
                                    name: 'hijri_date'
                                }, ];

                                // Define custom templates for the custom fields
                                var templates = {
                                    'arab-countries-select': function(fieldData) {
                                        const randomNumber = Math.floor(Math.random() *
                                            10000
                                        ); // Generates a random number between 0 and 9999
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
                                    var selectElement = document.getElementById(elementId);
                                    if (selectElement && selectElement.dataset.fetched !== 'true') {
                                        var appUrl = '{{ env('APP_URL') }}';
                                        var localJsonUrl = appUrl + '/admin/countries-json';
                                        console.log(localJsonUrl);

                                        // Use fetch to get the data from the generated URL
                                        fetch(localJsonUrl)
                                            .then(response => response.json())
                                            .then(data => {
                                                // Filter countries to include only Arab countries or those with available translations
                                                var options = data
                                                    .map(country => {
                                                        var countryName = (country
                                                                .translations && country
                                                                .translations.ara) ?
                                                            country.translations.ara.common :
                                                            'Unknown Country'; // Fallback for non-Arab countries
                                                        var isSelected = countryName ===
                                                            selectedValue ? 'selected' : '';
                                                        return `<option value="${countryName}" ${isSelected}>${countryName}</option>`;
                                                    })
                                                    .join('');

                                                // Update the select element with the options
                                                selectElement.innerHTML = options;
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
                                                console.error('Error fetching local JSON:', error);
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
                                if (typeof item.content === 'string') {
                                    try {
                                        item.content = JSON.parse(item
                                            .content); // Parse the string data
                                    } catch (error) {
                                        console.error('Failed to parse field_data:',
                                            error);
                                        item
                                            .content = []; // Fallback to an empty array if parsing fails
                                    }
                                }

                                // Extract hijri_date values from field_data
                                item.content.forEach(function(field) {
                                    if (field.name === 'hijri_date') {
                                        dates.push(field
                                            .value); // Store the value of 'hijri_date' fields
                                    }
                                });
                                // Initialize the form builder for the cloned element
                                $clone.find('.fb-editor-edit').formBuilder(options).promise
                                    .then(function(fb) {
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
                                        fb.actions.setData(item.content);
                                        $clone.find('.fb-editor-edit input[type="number"]').prop(
                                            'disabled', true);

                                    });
                            });
                        }

                        // Set isFirstChange to false after the first change event
                        isFirstChange = false;
                    } else {
                        // Second change event logic
                        getAgendaTopicFormbuilder(selectedSubTopic);
                    }
                });

                // Define the function to handle the AJAX request
                function getAgendaTopicFormbuilder(subTopicId) {
                    var Agendaform = "{{ route('AgendaTopicFormbuilder') }}";

                    $.ajax({
                        url: Agendaform, // The URL to your route
                        type: 'GET',
                        data: {
                            sub_topic_id: subTopicId
                        }, // Send the selected sub-topic ID to the server
                        success: function(response) {
                            // Handle the response data
                            var AgendaTopicFormbuilderData = response;
                            $('#form-entries-container').empty(); // Clear the container

                            if (Array.isArray(AgendaTopicFormbuilderData)) {
                                AgendaTopicFormbuilderData.forEach(function(item, index) {
                                    // Clone the template
                                    var $clone = $('#form-entry-template').clone()
                                        .removeAttr('id').show();

                                    // Set the values
                                    $clone.find('.AgendaIdForm').val(item.agenda_id);
                                    $clone.find('.agendatopicIdForm').val(item.id);
                                    // Set the values
                                    $clone.find('.topicTitle').val(item.axisTitle);
                                    $clone.find('.topicIdForm').val(item.topicId);
                                    $clone.find('.topic-title-text').text(item
                                        .axisTitle);

                                    // Append the clone to the container
                                    $('#form-entries-container').append($clone);

                                    // Initialize the form builder for the cloned element
                                    $clone.find('.fb-editor-edit').formBuilder().promise
                                        .then(function(fb) {
                                            fb.actions.setData(item.field_data);
                                        });
                                });
                            }
                        },
                        error: function(xhr) {
                            console.error('Failed to fetch agenda topic form builder data:', xhr
                                .responseText);
                        }
                    });
                }




                // // Remove the functionality from 'next2' click and add validation to 'finalForm' submit
                // $('#next2').on('click', function() {
                //     if (validateStep1()) {
                //         $("#line-progress").css("width", "100%");
                //         // Additional actions related to step progress can go here
                //     }
                // });
                // Trigger file input when custom upload button is clicked
                $('#customUploadBtn').on('click', function() {
                    $('#photoUpload').click();
                });

                // Handle file removal from preview
                $('#filePreviewsContainer').on('click', '.remove-file-btn', function() {
                    const fileIndex = $(this).data('file-index');
                    const fileInput = $('#photoUpload')[0];
                    const filesArray = Array.from(fileInput.files);

                    // Remove the selected file from the file array
                    filesArray.splice(fileIndex, 1);

                    // Update the file input's file list after removal
                    const newFileList = new DataTransfer();
                    filesArray.forEach(file => newFileList.items.add(file));
                    fileInput.files = newFileList.files;

                    // Remove the preview from the DOM
                    $(this).parent().remove();

                    // Re-gather form data (including updated subTopic)
                    gatherFormData();
                });

                // Handle new file selection and preview
                $('#photoUpload').on('change', function(e) {
                    const files = e.target.files;
                    const filePreviewsContainer = $(
                        '#filePreviewsContainer'); // Preview container for new uploads

                    // Iterate through the selected files and create previews
                    for (let i = 0; i < files.length; i++) {
                        const file = files[i];
                        const reader = new FileReader();

                        reader.onload = function(event) {
                            const previewElement = $('<div>', {
                                class: 'file-preview'
                            }).append(
                                $('<img>', {
                                    src: event.target.result,
                                    alt: 'file preview',
                                    class: 'w-32 h-32 object-cover'
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

                    // Re-gather form data (including updated subTopic)
                    gatherFormData();
                });


                // Handle form submission
                $('#finalForm').on('submit', function(e) {
                    e.preventDefault(); // Prevent the form from submitting by default

                    // Create FormData object
                    const formData = new FormData();

                    // Append form data from inputs
                    formData.append('faculty', $('#finalFaculty').val());
                    formData.append('department', $('#finalDepartment').val());
                    formData.append('mainTopic', $('#finalMainTopic').val());
                    formData.append('subTopic', $('#finalSubTopic').val());
                    formData.append('status', 0); // Default status
                    formData.append('notes', $('#notes').val());
                    formData.append('AgendaId', $('#AgendaId').val());

                    // Gather form data from the FormBuilder instance (if any)
                    gatherFormData().forEach((item) => {
                        formData.append('formDataArray[]', JSON.stringify(item));
                    });

                    // Add uploaded photos to the formData
                    const uploadedFiles = $('#photoUpload')[0].files;
                    for (let i = 0; i < uploadedFiles.length; i++) {
                        formData.append('uploadedPhotos[]', uploadedFiles[i]);
                    }

                    // Define the existingPhotos array
                    const existingPhotos = []; // Initialize the array for existing photo IDs

                    // Add existing photos to the array (if any)
                    $('#filePreviewsContainer .file-preview').each(function() {
                        const photoId = $(this).data('photo-id');
                        existingPhotos.push(photoId); // Add each photo's ID to the existingPhotos array
                    });

                    // Add existing photos to the formData (their IDs)
                    existingPhotos.forEach(function(photoId) {
                        formData.append('existingPhotos[]', photoId);
                    });

                    // Add CSRF token
                    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                    const updateAgendaUrl = "{{ route('updateAgenda') }}"; // Backend URL to update agenda

                    // Target the submit button
                    var submitButton = $('#submitForm');

                    // Disable the submit button
                    submitButton.prop('disabled', true).text('تحميل...');

                    // Make the AJAX request
                    $.ajax({
                        url: updateAgendaUrl,
                        type: 'POST',
                        dataType: 'json',
                        processData: false, // Prevent jQuery from processing the data
                        contentType: false, // Let the browser set the correct content type
                        data: formData,
                        success: function(response) {
                            Swal.fire({
                                title: 'Success',
                                text: 'Saving data...',
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 1500
                            })
                            .then((result) => {
                                window.history.back();
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                            var errorMessage = xhr.responseJSON.message ||
                                'An error occurred while submitting the form.';
                            if (xhr.responseJSON.errors) {
                                var errorMessages = Object.values(xhr.responseJSON.errors).flat()
                                    .join('<br>');
                                errorMessage += '<br><br><strong>Validation Errors:</strong><br>' +
                                    errorMessages;
                            }

                            // Re-enable the submit button after error
                            submitButton.prop('disabled', false).text($langData['Send']);

                            Swal.fire({
                                title: 'Failed!',
                                html: errorMessage,
                                icon: 'error',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#d33'
                            });
                        }
                    });
                });



                // Function to gather data from form entries
                function gatherFormData() {
                    let formDataArray = []; // Clear previous data

                    $('#form-entries-container .form-entry').each(function(index, element) {
                        var AgendaIdForm = $(element).find('.AgendaIdForm').val();
                        var agendatopicIdForm = $(element).find('.agendatopicIdForm').val();
                        var topicId = $(element).find('.topicIdForm').val();
                        var formBuilderInstance = $(element).find('.fb-editor-edit').data('formBuilder');
                        var formData = formBuilderInstance.actions.getData('json');

                        // Capture the latest subTopic value (in case it changes)
                        var subTopic = $('#finalSubTopic').val();

                        formDataArray.push({
                            AgendaIdForm: AgendaIdForm,
                            topicId: topicId,
                            agendatopicIdForm: agendatopicIdForm,
                            subTopic: subTopic, // Add subTopic here to make sure it reflects the latest value
                            formData: formData
                        });
                    });

                    return formDataArray; // Return the array
                }



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
            document.getElementById('finalstatus').addEventListener('change', function() {
                var notesSection = document.getElementById('notesSection');
                // if (this.value === '3') {
                if (this.value === '2') {
                    notesSection.style.display = '';
                } else {
                    notesSection.style.display = 'none';
                }
            });
        </script>


        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @if (auth()->user()->position_id == 3)
                    let containers = document.querySelectorAll('.form-entry');
                    containers.forEach(function(container) {
                        let inputs = container.querySelectorAll('input, textarea, select, button');
                        inputs.forEach(function(input) {
                            input.disabled = true;
                        });
                    });
                @endif
            });
        </script>

        <!-- Include Flowbite CSS and JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

        <!-- Override dark mode styles -->
        <style>
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
                background: #f3f3f3;
                border-radius: 4px;
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

            .disabled {
                pointer-events: none;
                opacity: 1;
                background-color: #d8dbe4;
                color: #000000;
                cursor: not-allowed;
                font-weight: 400;
            }

            .disabled input,
            .disabled textarea,
            .disabled select,
            .disabled button {
                opacity: 1;
                background-color: #d8dbe4;
                color: #000000;
                cursor: not-allowed;
                font-weight: 400;
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

            select:disabled {
                opacity: 1;
                background-color: #d8dbe4;
                color: #000000;
                cursor: not-allowed;
                font-weight: 400;
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
