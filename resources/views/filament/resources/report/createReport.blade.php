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

    <div class="container my-4 mx-auto max-w-3xl p-4">
        <form id="report-form" method="POST" action="{{ route('reports.store') }}"
            class="bg-white p-6 rounded-lg shadow-lg border border-gray-200">
            @csrf
            <div class="container mx-auto p-4">
                <!-- Topic Selection -->
                <div class="form-group mb-4">
                    <label for="topic" class="block text-gray-700">{{ __('Select Topic') }}</label>
                    <select name="topic" id="topic"
                        class="form-control mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
                        <option value="">{{ __('Select a Topic') }}</option>
                        @foreach ($topics as $id => $title)
                            <option value="{{ $id }}">{{ $title }}</option>
                        @endforeach
                    </select>
                </div>

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
                                data-value="{uploader}">
                                صاحب الطلب
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-6">
                <label for="name" class="block text-lg font-semibold mb-2 text-gray-800">الاسم:</label>
                <input type="text" id="name" name="name"
                    class="w-full p-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="<?= $langData['Enter Name'] ?>">
            </div>


            <div id="editor-container" class="bg-gray-50 rounded-lg shadow-md border border-gray-200"
                style="height: 400px;"></div>

            <input type="hidden" name="content" id="content">

            <button type="submit"
                style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action">
                <?= $langData['Save'] ?>
            </button>
        </form>
    </div>

    {{-- <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&family=Open+Sans:wght@400;700&family=Lora:wght@400;700&display=swap"
        rel="stylesheet"> --}}
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

            $('#topic').change(function() {
                var topicId = $(this).val();

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


            $('#report-form').on('submit', function(event) {
                event.preventDefault(); // Prevent the default form submission

                var $form = $(this);
                var contentInput = $('#content');
                contentInput.val(quill.root.innerHTML); // Set content value

                $.ajax({
                    url: $form.attr('action'), // URL for form submission
                    method: 'POST',
                    data: $form.serialize(),
                    success: function(response) {
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
                    error: function(xhr) {
                        Swal.fire({
                            title: $langData['Error'],
                            text: $langData['something went wrong'],
                            icon: 'error',
                            confirmButtonText: $langData['ok'],
                            confirmButtonColor: '#3085d6'
                        });
                    }
                });
            });
        });
    </script>



</x-filament::page>
