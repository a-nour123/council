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
            <div style="flex: 1; margin-bottom: 20px;">
                <label for="topicType" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                    <?= $langData['Classifications type'] ?>
                </label>
                <select id="topicType"
                    class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                    style="height: 42px;">
                    <option selected><?= $langData['Select'] ?></option>
                    <option value="main"><?= $langData['Main Topic'] ?></option>
                    <option value="sub"><?= $langData['Sub Topic'] ?></option>
                </select>
            </div>
            <div class="form-group" style="display: flex; gap: 10px;">
                <div style="flex: 1;">
                    <label for="first_name" id="topicTitle"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        <?= $langData['Title'] ?>
                    </label>
                    <input type="text" id="first_name"
                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        placeholder="<?= $langData['Enter the title'] ?>" required />
                </div>

                <div style="flex: 1;" id="topicOrderSection">
                    <label for="topic_order" id="topicOrder"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        <?= $langData['Order'] ?>
                    </label>
                    <input type="number" id="topic_order"
                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        placeholder="<?= $langData['Enter the order of topic'] ?>" required />
                </div>

                <div style="flex: 1;" id="mainTopicSection">
                    <label for="large" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        <?= $langData['Related Main Topic'] ?>
                    </label>
                    <select id="large"
                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        style="height: 42px;">
                        <option selected>Select</option>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label for="classification_reference"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        <?= $langData['Classification Reference'] ?>
                    </label>
                    <select id="classification_reference"
                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        style="height: 42px;">
                        <option value="">Select</option>
                        <option value="1">مشترك</option>
                        <option value="2">قسم</option>
                        <option value="3">كلية</option>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label for="escalation_authority"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        <?= $langData['Escalation authority'] ?>
                    </label>
                    <select id="escalation_authority"
                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        style="height: 42px;">
                        <option value="">Select</option>
                        <option value="1">قسم</option>
                        <option value="2">كلية</option>
                    </select>
                </div>
                <div style="flex: 1;">
                    <label for="decisions" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        <?= $langData['decisions'] ?>
                    </label>
                    <select id="decisions"
                        class="select2 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        multiple>
                        <!-- Options will be dynamically loaded here by Ajax -->
                    </select>
                </div>

            </div>
            <br>
            <div id="formaxies" class="col-span-full" style="display: none;">
                <!--[if BLOCK]><![endif]-->
                <section x-data="{ isCollapsed: false }"
                    @collapse-section.window="if ($event.detail.id == $el.id) isCollapsed = true"
                    @expand="isCollapsed = false"
                    @open-section.window="if ($event.detail.id == $el.id) isCollapsed = false"
                    @toggle-section.window="if ($event.detail.id == $el.id) isCollapsed = ! isCollapsed"
                    :class="isCollapsed && 'fi-collapsed'"
                    class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <header @click="isCollapsed = ! isCollapsed"
                        class="fi-section-header flex flex-col gap-3 overflow-hidden sm:flex-row sm:items-center cursor-pointer px-6 py-4">
                        <div class="grid flex-1 gap-y-1">
                            <p class="fi-section-header-description text-sm text-gray-500 dark:text-gray-400">
                                <?= $langData['Topic Axes'] ?>
                            </p>
                        </div>
                        <button
                            class="relative flex items-center justify-center rounded-lg outline-none transition duration-75 focus-visible:ring-2 disabled:pointer-events-none disabled:opacity-70 -m-2 h-9 w-9 fi-color-gray text-gray-400 hover:text-gray-500 focus-visible:ring-primary-600 dark:text-gray-500 dark:hover:text-gray-400 dark:focus-visible:ring-primary-500 rotate-180"
                            type="button" @click.stop="isCollapsed = ! isCollapsed"
                            :class="{ 'rotate-180': !isCollapsed }">
                            <svg class="fi-icon-btn-icon h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </header>

                    <div :aria-expanded="(!isCollapsed).toString()"
                        :class="{ 'invisible h-0 overflow-hidden border-none': isCollapsed }"
                        class="fi-section-content-ctn border-t border-gray-200 dark:border-white/10 section-parent"
                        aria-expanded="true" id="axixrepeat_0">
                        <div class="repeater-item" data-id="1">
                            <div class="fi-section-content p-6">
                                <div class="grid grid-cols-1 gap-6">
                                    <div class="col-span-1">
                                        <div class="fi-fo-field-wrp">
                                            <div class="grid gap-y-2">
                                                <div x-data="{}" class="fi-fo-repeater grid gap-y-4">
                                                    <ul>
                                                        <div class="grid grid-cols-1 gap-4"
                                                            wire:end.stop="mountFormComponentAction('data.topicAxes', 'reorder', { items: $event.target.sortable.toArray() })"
                                                            x-sortable="x-sortable"
                                                            data-sortable-animation-duration="300">
                                                            <li wire:key="data.topicAxes.bc86c51e-0a74-434e-814e-c2cc47e27ff8"
                                                                x-data="{ isCollapsed: false }" @expand="isCollapsed = false"
                                                                @repeater-expand.window="$event.detail === 'data.topicAxes' && (isCollapsed = false)"
                                                                @repeater-collapse.window="$event.detail === 'data.topicAxes' && (isCollapsed = true)"
                                                                x-sortable-item="bc86c51e-0a74-434e-814e-c2cc47e27ff8"
                                                                class="fi-fo-repeater-item rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10"
                                                                :class="{ 'fi-collapsed overflow-hidden': isCollapsed }">
                                                                <div
                                                                    class="fi-fo-repeater-item-header flex items-center gap-x-3 overflow-hidden px-4 py-3">
                                                                    <ul class="ms-auto flex items-center gap-x-3">
                                                                        <li>
                                                                            <button
                                                                                class="remove-button relative flex items-center justify-center rounded-lg outline-none transition duration-75 focus-visible:ring-2 disabled:pointer-events-none disabled:opacity-70 -m-1.5 h-8 w-8 text-custom-500 hover:text-custom-600 focus-visible:ring-custom-600 dark:text-custom-400 dark:hover:text-custom-300 dark:focus-visible:ring-custom-500"
                                                                                title="حذف" type="button">
                                                                                <span class="sr-only">حذف</span>
                                                                                <svg class="fi-icon-btn-icon h-5 w-5"
                                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                                    viewBox="0 0 20 20"
                                                                                    fill="currentColor"
                                                                                    aria-hidden="true">
                                                                                    <path fill-rule="evenodd"
                                                                                        d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z"
                                                                                        clip-rule="evenodd"></path>
                                                                                </svg>
                                                                            </button>
                                                                        </li>
                                                                    </ul>
                                                                </div>

                                                                <div x-show="!isCollapsed"
                                                                    class="fi-fo-repeater-item-content border-t border-gray-100 p-4 dark:border-white/10">
                                                                    <div class="grid grid-cols-1 gap-6">
                                                                        <div class="col-span-full">
                                                                            <section x-data="{ isCollapsed: false }"
                                                                                class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                                                                                <div class="fi-section-content-ctn">
                                                                                    <div
                                                                                        class="fi-section-content p-6">
                                                                                        <div
                                                                                            class="grid grid-cols-1 gap-6">
                                                                                            <div class="col-span-full"
                                                                                                wire:key="data.topicAxes.bc86c51e-0a74-434e-814e-c2cc47e27ff8.axis_id.Filament\Forms\Components\Select">
                                                                                                <div data-field-wrapper=""
                                                                                                    class="fi-fo-field-wrp">
                                                                                                    <div
                                                                                                        class="grid gap-y-2">
                                                                                                        <div
                                                                                                            class="flex items-center justify-between gap-x-3">
                                                                                                            <label
                                                                                                                class="fi-fo-field-wrp-label inline-flex items-center gap-x-3"
                                                                                                                for="data.topicAxes.bc86c51e-0a74-434e-814e-c2cc47e27ff8.axis_id">
                                                                                                                <span
                                                                                                                    class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                                                                                                    <?= $langData['Axis title'] ?>
                                                                                                                    <sup
                                                                                                                        class="text-danger-600 dark:text-danger-400 font-medium">*</sup>
                                                                                                                </span>
                                                                                                            </label>
                                                                                                        </div>
                                                                                                        <div
                                                                                                            class="grid gap-y-2">
                                                                                                            <div
                                                                                                                class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white focus-within:ring-2 dark:bg-white/5 ring-gray-950/10 focus-within:ring-primary-600 dark:ring-white/20 dark:focus-within:ring-primary-500 fi-fo-select">
                                                                                                                <select
                                                                                                                    id="dynamicselect_0"
                                                                                                                    class="dynamic-select bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                                                                                                    style="height: 42px;">
                                                                                                                    <option
                                                                                                                        selected>
                                                                                                                        Select
                                                                                                                    </option>
                                                                                                                </select>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                        <div class="axies-content"
                                                                                                            id="axiescontent_0">
                                                                                                            <div
                                                                                                                class="fi-input-wrp flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white focus-within:ring-2 dark:bg-white/5 ring-gray-950/10 focus-within:ring-primary-600 dark:ring-white/20 dark:focus-within:ring-primary-500 fi-fo-select">
                                                                                                                <div style="width: 100%"
                                                                                                                    class="bg-white dark:bg-gray-800 fb-editor"
                                                                                                                    id="fbeditor_0">
                                                                                                                    <!-- Your form builder content here -->
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </section>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </div>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="container" style="text-align: center;">
                        <button id="generateDivBtn"
                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600); margin-bottom:8px"
                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                            type="button" fdprocessedid="ot6l9g">
                            <?= $langData['Generate Div'] ?>
                        </button>
                    </div>
                </section>
            </div>




            <br>
            <!-- Add Save Form Button -->
        </div>
        <button id="save-button"
            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
            type="button">
            <?= $langData['Save'] ?>
        </button>
    </div>



    <!-- Include necessary scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
    <script src="{{ URL::asset('assets/form-builder/form-builder.min.js') }}"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <!-- Load Moment.js -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <!-- Load Moment Hijri.js -->
    <script src="https://cdn.jsdelivr.net/npm/moment-hijri@2.2.1/moment-hijri.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/abublihi/datepicker-hijri@v1.1/build/datepicker-hijri.js"></script>
    <script>
        $(document).ready(function() {
            $(".form-group").hide();

            $("#topicType").change(function(e) {
                e.preventDefault();

                // Check the selected value
                if ($(this).val() === 'main') {
                    // Show the form group
                    $('.form-group').slideDown();

                    // Set the title for main topic
                    $("#topicTitle").text(`<?= $langData['Main Topic Title'] ?>`);

                    // Hide the main topic section
                    $("#mainTopicSection").hide();
                    $("#formaxies").hide();

                    // Remove 'required' from the #large select field
                    $("#large").prop('required', false);
                } else if ($(this).val() === 'sub') {
                    // Show the form group
                    $('.form-group').slideDown();

                    // Set the title for sub topic
                    $("#topicTitle").text(`<?= $langData['Sub Topic Title'] ?>`);

                    // Show the main topic section
                    $("#mainTopicSection").show();
                    $("#formaxies").show();

                    // Make the #large select field required
                    $("#large").prop('required', true);
                } else {
                    // Hide the form group if no valid option is selected
                    $('.form-group').slideUp();
                }
            });


            $.ajax({
                url: '<?php echo url('/topics'); ?>', // Dynamically generated URL
                method: 'GET',
                success: function(response) {
                    var $select = $('#large');
                    $select.empty(); // Clear existing options
                    $select.append(`<option selected><?= $langData['Choose a Main Topic'] ?></option>`);
                    response.forEach(function(topic) {
                        $select.append('<option value="' + topic.id + '">' + topic.title +
                            '</option>');
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Failed to fetch topics:', error);
                }
            });
            // Handle change event of select
            $('#large').change(function() {
                var selectedValue = $(this).val();
                if (selectedValue !== "Select") {
                    // Slide up elements
                    $('.col-span-full').slideDown();
                } else {
                    // Slide up elements with class col-span-full
                    $('.col-span-full').slideUp();

                    // Hide the content of elements with IDs starting with axiescontent_0 within .section-parent
                    $('.section-parent [id^="axiescontent_"]').hide();
                }
                // Reset the value of the select
            });

        });

        $(document).ready(function() {
            var counter = 1;

            // Click event for the button
            $('#generateDivBtn').off('click').on('click', function() {
                var lastDiv = $('.section-parent').last();
                var clonedDiv = lastDiv.clone();

                // Update IDs in the cloned div
                clonedDiv.attr('id', 'axixrepeat_' + counter);
                clonedDiv.find('select').attr('id', 'dynamicselect_' + counter);
                clonedDiv.find('.axies-content').attr('id', 'axiescontent_' + counter);
                clonedDiv.find('.fb-editor').attr('id', 'fbeditor_' + counter);
                clonedDiv.find('.remove-button').attr('id', 'removebutton_' + counter);

                // Reset select and content display
                clonedDiv.find('select').val('Select');
                clonedDiv.find('.axies-content').hide();

                clonedDiv.insertAfter(lastDiv);
                counter++;
            });

            $(document).on('click', '.remove-button', function(e) {
                // Check if there is more than one section-parent
                if ($('.section-parent').length > 1) {
                    $(this).closest('.fi-section-content-ctn').remove();
                } else {
                    e.preventDefault(); // Prevent default action if needed
                    return false; // Ensure no further actions are taken
                }
            });



            $(document).on('change', 'select[id^="dynamicselect_"]', function() {
                var selectedValue = $(this).val();
                var parentDiv = $(this).closest('.section-parent');
                var queryAxisDiv = parentDiv.find('.axies-content');
                var fbEditorId = parentDiv.find('.fb-editor').attr('id');

                console.log("Selected Value: ", selectedValue);
                console.log("Parent Div: ", parentDiv);
                console.log("Query Axis Div: ", queryAxisDiv);
                console.log("FB Editor ID: ", fbEditorId);

                // Gather and alert all relevant IDs
                var parentDivId = parentDiv.attr('id');
                var dynamicSelectId = $(this).attr('id');
                var axiesContentId = queryAxisDiv.attr('id');

                if (selectedValue === "Select") {
                    queryAxisDiv.slideUp(); // Slide up the actual jQuery object

                } else {
                    queryAxisDiv.slideDown(); // Slide down the actual jQuery object
                }

                // Fetch form data and populate the fb-editor
                fetchFormData(selectedValue, fbEditorId, axiesContentId);
            });


            // Define a global variable to hold the form data
            var formData;

            function fetchFormData(selectedValue, fbEditorId, axiesContentId) {
                var editUrl = "{{ route('GetFormBuilderEdit') }}";

                $.ajax({
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                    url: editUrl,
                    data: {
                        'id': selectedValue,
                    },
                    success: function(data) {
                        let dates = [];
                        try {
                            // Check if data.content is a string before parsing
                            var content = (typeof data.content === 'string') ? JSON.parse(data
                                .content) : data.content;

                            content.forEach(function(field) {
                                if (field.name === 'hijri_date') {
                                    console.log("Adding date: ", field.value); // Debugging line
                                    dates.push(field.value);
                                }
                            });

                            setTimeout(function() {
                                // Debugging line to check the number of .hijri-input elements
                                console.log('Number of hijri-input elements:', $('.hijri-input')
                                    .length);

                                $('.hijri-input').each(function(index) {
                                    // Debugging line to check the index and corresponding date value
                                    console.log('Index:', index, 'Date:', dates[index]);

                                    if (dates[index]) {
                                        $(this).val(dates[
                                            index
                                        ]); // Set the corresponding value from the dates array
                                    }
                                });
                            }, 1000); // Increase timeout duration if necessary
                        } catch (e) {
                            console.error('Error parsing content:', e);
                            alert('Failed to parse form data.');
                        }


                        // Store the fetched form data
                        var formData = data.content;
                        var fbEditor = $('#' + fbEditorId);
                        fbEditor.empty(); // Empty the div

                        // Define custom fields, including the Countries dropdown
                        var fields = [{
                                type: 'arab-countries-select',
                                label: 'Country',
                                className: 'form-control',
                                name: 'country',
                                value: 'SA', // Set a default selected value
                                icon: '🌍'
                            },
                            {
                                type: 'text',
                                label: 'Uploader Name',
                                className: 'form-control',
                                name: 'userName',
                                value: '.', // Set a default selected value
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
                                    10000); // Generates a random number
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
                                        $('#' + uniqueId + '_calender_display').on('focus',
                                            function() {
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
                                        $('#' + uniqueId + '_calender_display').on('change',
                                            function() {
                                                const selectedDate = $(this).val();
                                                // Set the value to both the display and hidden input fields
                                                $('#' + uniqueId + '_calender_hidden')
                                                    .val(selectedDate);

                                                // Trigger the 'keyup' event on the hidden input after setting the value
                                                $('#' + uniqueId + '_calender_hidden')
                                                    .trigger('change');


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
                                        // Filter countries to include only Arab countries
                                        var options = data.map(country => {
                                            var countryName = (country.translations &&
                                                    country.translations.ara) ? country
                                                .translations.ara.common :
                                                'Unknown Country'; // Fallback for non-Arab countries
                                            var isSelected = countryName === selectedValue ?
                                                'selected' : '';
                                            return `<option value="${countryName}" ${isSelected}>${countryName}</option>`;
                                        }).join('');

                                        // Update the select element with the options
                                        selectElement.innerHTML = options;
                                        selectElement.dataset.fetched = 'true'; // Mark as fetched

                                        // Initialize Select2 for the search functionality
                                        $(selectElement).select2({
                                            placeholder: 'Search for a country...',
                                            allowClear: true
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
                            disableFields: ['autocomplete', 'button', 'header', 'hidden'],
                            replaceFields: [{
                                type: "number",
                                label: "Number Condition",
                            }],
                            fields: fields, // Include custom fields
                            templates: templates, // Include custom templates
                            typeUserDisabledAttrs: {
                                'number': ['required', ],
                            },
                            typeUserAttrs: {
                                'arab-countries-select': {
                                    placeholder: {
                                        label: 'Placeholder',
                                        value: ''
                                    }
                                }
                            },
                        };

                        $('#' + fbEditorId).formBuilder(options).promise.then(function(fb) {
                            // Once the form builder is initialized, set the data
                            fb.actions.setData(formData);
                            let currentClicks = [];

                            $(document).on('change', '#' + fbEditorId + ' input[type="number"]',
                                function() {
                                    var newValue = $(this).val();
                                    // Ensure the new value is a positive integer and not greater than 5
                                    if ($.isNumeric(newValue) && newValue >= 0 &&
                                        newValue <= 5) {
                                        handleClicks(newValue);
                                    } else if (newValue > 5) {
                                        alert('العدد المسموح 5 او اقل');
                                        $(this).val(
                                            5
                                        ); // Set the value to 5 if it exceeds the limit
                                        handleClicks(5);
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
                                        var element = $('.formbuilder-icon-text')
                                            .first();
                                        element.click();
                                        currentClicks.push(element);
                                    }, i * 100); // Delay each click slightly
                                }
                            }
                        });

                    },
                    error: function(xhr, status, error) {
                        alert("Failed to fetch form data.");
                        console.error('Failed to fetch form data:', error);
                    }
                });
            }



            // Assuming that form builders are initialized somewhere
            $('[id^="fbeditor_"]').each(function(index, element) {
                var fbEditorId = $(this).attr('id');
                $(this).formBuilder();
            });

            $('#save-button').click(function() {
                var formData = getFormData();
                var mainTopic = $('#large').val(); // Get the value of the selected axis ID
                saveForm(formData);
            });

            function getFormData() {
                var formData = {};
                $('[id^="fbeditor_"]').each(function(index, element) {
                    var fbEditorId = $(this).attr('id');
                    var repeaterIndex = fbEditorId.split('_')[1]; // Extract the repeater index from the ID
                    var dynamicSelectId = 'dynamicselect_' +
                        repeaterIndex; // Corresponding dynamic select ID
                    var dynamicSelectValue = $('#' + dynamicSelectId)
                        .val(); // Get the value of the dynamic select element
                    var fbEditor = $('#' + fbEditorId);

                    if (fbEditor.length > 0) {
                        var formBuilderInstance = fbEditor.data(
                            'formBuilder'); // Retrieve the formBuilder instance
                        if (formBuilderInstance) {
                            var data = formBuilderInstance.actions.getData();
                            // Check if data is empty or null, and set formData accordingly
                            formData[dynamicSelectValue] = (data && data.length > 0) ? data : null;
                        }
                    }
                });
                return formData;
            }

            function saveForm(formData) {
                var title = $('#first_name').val(); // Get the value of the title input field
                var maintopic = $('#large').val(); // Get the value of the country select field
                var ClassificationReference = $('#classification_reference').val();
                var EscalationAuthority = $('#escalation_authority').val();
                var decisions = $('#decisions').val();
                var topicOrder = $("#topic_order").val();

                // Define locale from PHP to JavaScript
                var locale = @json(app()->getLocale());

                // Construct the URL dynamically with session_id and locale
                var url =
                    '{{ route('createAxiesTopicForm', ['locale' => '__LOCALE__']) }}';
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
                    // url: '{{ URL('create-form-builder') }}',
                    url: url,

                    data: {
                        'title': title, // Include the title in the request data
                        'maintopic': maintopic, // Include the country in the request data
                        ClassificationReference: ClassificationReference,
                        EscalationAuthority: EscalationAuthority,
                        decisions: decisions,
                        'order': topicOrder,
                        'form': JSON.stringify(formData), // Convert form data to JSON string
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(data) {
                        Swal.fire({
                            title: $langData['Success'],
                            text: $langData['saving data'],
                            icon: 'success',
                            timer: 1000, // Auto-dismiss after 1 second
                            timerProgressBar: true,
                            didClose: () => {
                                if (data.topicId) {
                                    // Redirect to the route with the topic ID
                                    var redirectUrl =
                                        '{{ route('getPagesReportEdit', ['recordId' => '__TOPIC_ID__']) }}';
                                    redirectUrl = redirectUrl.replace('__TOPIC_ID__',
                                        encodeURIComponent(data.topicId));
                                    window.location.href = redirectUrl;
                                } else {
                                    // Fallback redirection if topicId is not provided
                                    var appUrl =
                                        '{{ env('APP_URL') }}'; // Inject the APP_URL from .env into JS
                                    var url = appUrl +
                                        '/admin/topics'; // Construct the fallback URL

                                    // Now perform the redirection to the fallback URL
                                    window.location.href =
                                        url; // Use the correct variable (url)

                                }
                            }
                        });
                    },

                    error: function(response) {
                        // Determine if language is RTL
                        var isRtl = @json(app()->getLocale()) === 'ar';

                        // Check if response contains validation errors
                        if (response.responseJSON && response.responseJSON.errors) {
                            const errors = response.responseJSON.errors;

                            // Construct the error message with icons and styling (only show the first error)
                            let errorMessage = '<ul style="padding-left: 10px; margin: 0;">';
                            let firstError = true; // Flag to track if it's the first error
                            for (const field in errors) {
                                if (firstError) {
                                    errorMessage += `<li style="display: flex; align-items: center; margin-bottom: 8px;">
                                    <i class="fas fa-exclamation-circle" style="color: #d9534f; margin-right: 8px;"></i>
                                    <span>${errors[field][0]}</span>
                                 </li>`;
                                    firstError = false; // Set flag to false after the first error
                                    break; // Stop after the first error message
                                }
                            }
                            errorMessage += '</ul>';

                            // Display the error messages in a SweetAlert2 toast
                            Swal.fire({
                                title: '<span style="color: #721c24; font-weight: bold;">Validation Error</span>',
                                html: errorMessage,
                                timer: 4000, // Adjust timer as needed
                                timerProgressBar: true,
                                toast: true,
                                position: isRtl ? 'top-start' :
                                'top-end', // Dynamically set position
                                showConfirmButton: false,
                                customClass: {
                                    popup: 'swal-toast-custom', // Custom class for additional styling
                                    title: 'swal-toast-title',
                                    content: 'swal-toast-content'
                                },
                                didOpen: (toast) => {
                                    toast.classList.add('animated',
                                        'fadeInDown'); // Add animation
                                }
                            });
                        }
                    }




                });
            }

            // Initial load to fetch select options
            fetchSelectOptions();

            function fetchSelectOptions() {
                var fetchSelectOptionsUrl = "{{ route('getaxies') }}";
                $.ajax({
                    url: fetchSelectOptionsUrl, // Use the dynamically generated URL
                    method: 'GET',
                    success: function(response) {
                        var $select = $('#dynamicselect_0');
                        $select.empty(); // Clear existing options
                        $select.append(`<option selected><?= $langData['Choose an axie'] ?></option>`);
                        response.forEach(function(axies) {
                            $select.append('<option value="' + axies.id + '">' + axies.title +
                                '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Failed to fetch options:', error);
                    }
                });
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            // Initially hide the classification_reference and escalation_authority fields
            $('#classification_reference').parent().hide();
            $('#escalation_authority').parent().hide();
            $('#decisions').parent().hide();

            // Monitor changes to the 'large' dropdown
            $('#large').change(function() {
                // Get the selected value of 'large'
                const selectedValue = $(this).val();

                // Check if a valid option is selected (not null or empty)
                if (selectedValue && selectedValue !== "Select" && selectedValue !==
                    "اختر التصنيف الرئيسي") {
                    // Show the classification_reference and escalation_authority fields
                    $('#classification_reference').parent().show();
                    $('#escalation_authority').parent().show();
                    $('#decisions').parent().show();

                } else {
                    // Hide and reset the classification_reference and escalation_authority fields if large is null
                    $('#classification_reference').parent().hide().find('select').val('');
                    $('#escalation_authority').parent().hide().find('select').val('');
                    $('#decisions').parent().hide().find('select').val('');

                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Get the route URL for fetching the classification decisions
            let url = '{{ route('fetchClassificationDecisions') }}';

            $.ajax({
                url: url,
                method: 'GET',
                success: function(data) {
                    const selectElement = $('#decisions');
                    selectElement.empty(); // Clear any existing options

                    // Populate select with options fetched from the server
                    data.forEach(function(decision) {
                        selectElement.append(new Option(decision.name, decision.id));
                    });

                    // Initialize Select2 after options are loaded
                    selectElement.select2({
                        placeholder: "Select options",
                        width: '100%' // Ensure the width is full
                    });
                },
                error: function(error) {
                    console.error('Error fetching classification decisions:', error);
                }
            });
        });
    </script>
    <script>
        $('#decisions').select2({
            placeholder: "Select options",
            width: '100%' // Ensure the width is full
        });
    </script>


    <!-- Include Toastr CSS and JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- Include Flowbite CSS and JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>


    <!-- Override dark mode styles -->
    <style>
        .form-actions {
            display: none !important;
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

        /* Custom styling for SweetAlert2 toast */
        .swal-toast-custom {
            background-color: #fff !important;
            /* White background for a cleaner look */
            color: #721c24 !important;
            /* Dark red text */
            border-left: 6px solid #dc3545;
            /* Red border for emphasis */
            border-radius: 12px;
            /* Rounded corners */
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            /* Soft shadow */
            font-size: 0.95rem;
            padding: 10px;
            min-width: 300px;
            /* Minimum width to avoid small toasts */
        }

        .swal-toast-title {
            font-weight: bold;
            color: #721c24;
            font-size: 16px;
        }

        .swal-toast-content {
            padding-top: 10px;
        }

        .swal-toast-content div {
            display: flex;
            align-items: center;
            color: #721c24;
            font-size: 14px;
        }

        .swal-toast-content .fa-times {
            color: #fff;
            /* White icon */
            background-color: #dc3545;
            /* Red background for the icon */
            border-radius: 50%;
            padding: 8px;
        }

        .swal-toast-content span {
            margin-left: 12px;
            font-weight: 500;
            line-height: 1.4;
        }

        /* Animation for smooth appearance */
        .swal-toast-custom {
            animation: toastInUp 0.3s ease-out;
            /* Animation to slide in */
        }

        @keyframes toastInUp {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
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
        .multiple-wrap {

            display: none !important;
        }

        #userName-preview {
            pointer-events: none !important;
            user-select: none !important;
            background-color: #e9ecef !important;
            /* Optional: mimic the appearance of a read-only input */
        }

        /* Styling the Select2 container */
        .select2-container {
            border-radius: 0.375rem;
            /* Tailwind rounded-lg */
            border: 1px solid #D1D5DB;
            /* Tailwind border-gray-300 */
        }

        /* Styling the selected option */

        /* Hover effect for selected items */
        .select2-selection--multiple {
            background-color: #F3F4F6;
            /* Tailwind bg-gray-100 */
            border: 1px solid #D1D5DB;
            /* Tailwind border-gray-300 */
        }

        /* Style the selected options with blue text color */
        .select2-selection__choice {
            background-color: #3B82F6;
            /* Tailwind blue-500 */
            border-color: #2563EB;
            /* Tailwind blue-600 */
            color: white;
            /* White text for selected options */
            border-radius: 0.375rem;
            /* Tailwind rounded-lg */
            padding: 0.375rem 1rem;
            /* Tailwind p-2 */
            margin-right: 0.25rem;
            /* Tailwind mr-1 */
            font-size: 0.875rem;
            /* Tailwind text-sm */
        }

        /* Make selected text color blue */
        .select2-selection__choice {
            color: #2563EB;
            /* Tailwind blue-600 for text color */
        }

        /* Hover effect for the selected options */
        .select2-selection__choice:hover {
            background-color: #2563EB;
            /* Tailwind blue-600 */
            border-color: #1D4ED8;
            /* Tailwind blue-700 */
        }

        /* Customizing the dropdown */
        .select2-dropdown {
            border-radius: 0.375rem;
            /* Tailwind rounded-lg */
            border: 1px solid #D1D5DB;
            /* Tailwind border-gray-300 */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            /* Tailwind shadow-lg */
        }

        /* Style the individual items in the dropdown */
        .select2-results__option {
            padding: 0.625rem 1rem;
            /* Tailwind p-2.5 */
            font-size: 0.875rem;
            /* Tailwind text-sm */
            color: #1F2937;
            /* Tailwind text-gray-900 */
        }

        /* Hover effect on dropdown options */
        .select2-results__option--highlighted {
            background-color: #EFF6FF;
            /* Tailwind bg-blue-100 */
            color: #2563EB;
            /* Tailwind blue-600 */
        }

        /* Focused state for the dropdown */
        .select2-container--default .select2-selection--multiple:focus-within {
            border-color: #2563EB;
            /* Tailwind blue-600 */
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
            /* Tailwind blue-500 */
        }

        /* Scrollbar styling for the dropdown */
        .select2-dropdown {
            max-height: 300px;
            /* Set maximum height */
            overflow-y: auto;
            /* Allow scrolling */
        }

        .select2-selection__choice {
            color: #2563EB;
            /* Tailwind blue-600 for text color */
        }

        .select2-selection__choice:hover {
            background-color: #2563EB;
            /* Tailwind blue-600 */
            border-color: #1D4ED8;
            /* Tailwind blue-700 */
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

        /* .ui-sortable {
            min-height: 558px !important;
        } */
    </style>
</x-filament::page>
