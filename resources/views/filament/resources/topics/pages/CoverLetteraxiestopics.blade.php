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
            <div class="form-group" style="display: flex; gap: 10px;">
                <div style="flex: 1;">
                    <label for="first_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        <?= $langData['Title'] ?>
                    </label>
                    <input type="text" id="first_name" value="<?php echo $topicTitle; ?>" disabled
                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        placeholder="Enter the title" required />
                    <input type="hidden" id="topicIdMain" value="<?php echo $topicId; ?>">

                </div>
                <div style="flex: 1;">
                    <label for="large" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        <?= $langData['Main topic'] ?>
                    </label>
                    <select id="large" disabled
                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        style="height: 42px;">
                        <option value="" selected><?= $langData['Choose a Main Topic'] ?></option>
                        @foreach ($maintopics as $mainTopic)
                            <option value="{{ $mainTopic->id }}" @if (isset($mainTopic) && $mainTopic->id == $topic_id_main) selected @endif>
                                {{ $mainTopic->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <br>


            <input type="hidden" value="<?php echo $axisDatacount; ?>">



            @if (!$axisData->isEmpty())
                <input type="hidden" value="{{ $this->existingContent }}" id="existreportContent">
                <div class="container my-4 mx-auto col-span-full" id="template_Report">
                    <h2 class="text-lg font-semibold mb-4 text-gray-800">انشاء خطاب التغطية </h2>

                    <form id="report-form" method="POST" action="{{ route('reportsCoverlLetters.store') }}"
                        class="bg-white p-6 rounded-lg shadow-lg border border-gray-200">
                        @csrf
                        <div class="container mx-auto p-4">
                            <!-- Custom buttons container -->
                            <div id="custom-buttons" class="mb-6">
                                <div class="bg-gray-50 rounded-lg shadow-md p-4 border border-gray-200">
                                    <h2 class="text-lg font-semibold mb-4 text-gray-800">مفاتيح مساعدة</h2>
                                    <div class="flex flex-wrap gap-4">
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{name_of_topic}">
                                            اسم الموضوع
                                        </button>
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{number_of_topic}">
                                            رقم الموضوع
                                        </button>
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{acadimic_year}">
                                            العام الجامعي
                                        </button>
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{department_name}">
                                            القسم
                                        </button>
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{faculty_name}">
                                            الكلية
                                        </button>
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{session_number}">
                                            رقم الجلسة
                                        </button>
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{session_number_as_word}">
                                            رقم الجلسة كتابة
                                        </button>
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{decision}">
                                            القرار
                                        </button>
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{deescion_number}">
                                            رقم القرار
                                        </button>
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{justification}">
                                            المبرر
                                        </button>
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{vote}">
                                            التصويت
                                        </button>
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{vote_type}">
                                            طبيعة التصويت
                                        </button>
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{uploader}">
                                            صاحب الطلب
                                        </button>
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{department_session_hijri_date}">
                                            تاريخ جلسة مجلس القسم بالهجرى
                                        </button>
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{department_session_date}">
                                            تاريخ جلسة مجلس القسم بالميلادي
                                        </button>
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{session_order}">
                                            رقم جلسة مجلس القسم كتابة
                                        </button>
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{session_order_as_number}">
                                            رقم جلسة مجلس القسم رقما
                                        </button>
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{session_department_decision}">
                                            قرار جلسة مجلس القسم المرتبطة بالموضوع
                                        </button>
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{session_department_decision_number}">
                                            رقم قرار جلسة مجلس القسم المرتبطة بالموضوع
                                        </button>
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{session_department_justification}">
                                            مبررات قرار مجلس القسم
                                        </button>
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{faculty_session_hijri_date}">
                                            تاريخ جلسة مجلس الكلية بالهجرى
                                        </button>
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{faculty_session_date}">
                                            تاريخ جلسة مجلس الكلية بالميلادي
                                        </button>
                                        <button type="button"
                                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                            data-value="{faculty_dean}">
                                            عميد الكلية
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="editor-container" class="bg-gray-50 rounded-lg shadow-md border border-gray-200"
                            style="height: 400px;"></div>

                        <input type="hidden" name="content" id="content">


                    </form>
                </div>
            @endif


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

    <script src="{{ URL::asset('assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ URL::asset('assets/form-builder/form-builder.min.js') }}"></script>
    <link rel="stylesheet" href="{{ URL::asset('assets/css/sweetalert2.min.css') }}">
    <script src="{{ URL::asset('assets/js/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/quill.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/flowbite.min.js') }}"></script>
    <link rel="stylesheet" href="{{ URL::asset('assets/css/quill.snow.css') }}">

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
        };

        $(document).ready(function() {






            $('#save-button').click(function() {
                var topicId = $('#topicIdMain').val();
                var contentInput = (typeof quill !== 'undefined' && quill.root) ? quill.root.innerHTML ||
                    '' : '';

                var locale = @json(app()->getLocale());

                // Construct the URL dynamically with session_id and locale
                var url =
                    '{{ route('reportsCoverlLetters.store', ['locale' => '__LOCALE__']) }}';
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
                        'topicId': topicId, // Include the topicId in the request data
                        'contentInput': contentInput, // Set contentInput to the value
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(data) {
                        Swal.fire({
                            title: $langData['Success'],
                            text: $langData['saving data'],
                            icon: 'success',
                            showConfirmButton: false, // Remove the confirm button
                            timer: 2000, // Set the alert to automatically close after 2 seconds (2000 ms)
                            timerProgressBar: true, // Display the timer progress bar
                        }).then(() => {
                            // Fallback redirection if topicId is not provided
                            var appUrl =
                                '{{ env('APP_URL') }}'; // Inject the APP_URL from .env into JS
                            var url = appUrl +
                                '/admin/topics'; // Construct the fallback URL

                            // Now perform the redirection to the fallback URL
                            window.location.href =
                                url; // Use the correct variable (url)
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
                                    firstError =
                                        false; // Set flag to false after the first error
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
            });

            var quill = new Quill('#editor-container', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{
                            'font': []
                        }, {
                            'size': []
                        }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{
                            'color': []
                        }, {
                            'background': []
                        }],
                        [{
                            'script': 'super'
                        }, {
                            'script': 'sub'
                        }],
                        [{
                            'header': '1'
                        }, {
                            'header': '2'
                        }, 'blockquote', 'code-block'],
                        [{
                            'list': 'ordered'
                        }, {
                            'list': 'bullet'
                        }, {
                            'indent': '-1'
                        }, {
                            'indent': '+1'
                        }],
                        [{
                            'direction': 'rtl'
                        }, {
                            'align': []
                        }],
                        ['clean'],
                        ['code-block'],
                        [{
                            'table': 'insert'
                        }, {
                            'table': 'delete'
                        }, {
                            'table': 'merge'
                        }]
                    ]
                },
                // placeholder: 'Compose an epic...',
            });

            var existingContent = $('#existreportContent').val();
            // Set the content in the Quill editor
            quill.root.innerHTML = existingContent;
            var topicId = $('#topicIdMain').val();
            if (topicId) {
                $.ajax({
                    url: '{{ route('getTopicFieldData') }}', // Route to the controller method
                    method: 'GET',
                    data: {
                        id: topicId
                    },
                    success: function(response) {
                        var labels = response.labels; // Get the labels from the response
                        var buttonContainer = $('#custom-buttons').find('div.flex');

                        // Fetch existing buttons
                        var existingButtons = buttonContainer.find('button').clone();

                        // Clear existing buttons
                        buttonContainer.empty();

                        // Append existing buttons first
                        buttonContainer.append(existingButtons);

                        // Generate and append new buttons based on the labels
                        if (labels && labels.length > 0) {
                            labels.forEach(function(label) {
                                var button = $('<button/>', {
                                    type: 'button',
                                    class: 'fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action',
                                    style: '--c-400:var(--primary-400); --c-500:var(--primary-500); --c-600:var(--primary-600);',
                                    text: label,
                                    'data-value': `{${label}}` // Wrap the label with curly braces
                                });

                                buttonContainer.append(button);
                            });
                        }

                        // Bind click event handler to the newly created buttons
                        $('#custom-buttons button').on('click', function() {
                            var value = $(this).data('value');
                            var range = quill.getSelection();
                            if (range) {
                                quill.insertText(range.index, value);
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching data:', error);
                    }
                });
            } else {
                $('#custom-buttons').find('div.flex')
                    .empty(); // Clear the buttons if no topic is selected
            }
        });
    </script>





    <!-- Include Flowbite CSS and JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>


    <!-- Override dark mode styles -->
    <style>
        .form-actions {
            display: none !important;
        }

        .custom-swal-popup {
            border-radius: 15px !important;
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
