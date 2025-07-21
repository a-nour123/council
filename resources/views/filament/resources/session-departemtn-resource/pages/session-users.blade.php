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
    {{-- @if (isset($this->sessionDecisionApproval) && $this->sessionDecisionApproval !== null)
        @if (auth()->user()->position_id == 2)
            <div id="end-session">
                <button id="endSessionBtn" @if ($record->actual_end_time) hidden @endif class="btn"
                    onclick="endSession()"><?= $langData['End Session'] ?></button>
            </div>
        @endif
    @endif --}}



    <div id="stopwatch-container" style="display: none">
        <div id="stopwatch-frame">
            <div id="stopwatch">00:00:00:000</div>
        </div>
        @if (auth()->user()->position_id == 2)
            <div id="controls">
                <button @if ($record->actual_end_time || $record->actual_start_time) hidden @endif class="btn1"
                    onclick="startStopwatch()"><?= $langData['Start'] ?></button>
                <button @if ($record->actual_end_time || !$record->actual_start_time) hidden @endif class="btn4"
                    onclick="completeStopwatch()"><?= $langData['Complete'] ?></button> <!-- New Complete button -->
                <button @if ($record->actual_end_time) hidden @endif class="btn2"
                    onclick="stopStopwatch()"><?= $langData['Stop'] ?></button>
                <button @if ($record->actual_end_time) hidden @endif class="btn3"
                    onclick="resetStopwatch()"><?= $langData['Reset'] ?></button>
                {{-- <button @if ($record->actual_end_time || !$record->actual_start_time) hidden @endif class="btn4"
                    onclick="completeStopwatch()"><?= $langData['Start'] ?></button> <!-- New Complete button --> --}}
            </div>
        @endif
    </div>


    <input type="hidden" id="recordId" value="<?php echo $record->id; ?>">
    <input type="hidden" id="sessionCode" value="<?php echo $record->code; ?>"> {{-- use it for save time in local storge --}}

    <hr>
    <!-- Toggle Button -->

    <div class="flex items-center justify-between mb-4 p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md">
        <div class="flex items-center text-sm font-medium text-gray-700 dark:text-gray-300">

            <?= $langData['Session Code'] ?>:
            <span class="ml-2 text-blue-700 dark:text-blue-400 font-semibold">
                <?php echo $record->code; ?>
            </span>
        </div>
        <button id="open" data-modal-target="authentication-modal"
            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
            type="button" fdprocessedid="x0wj37" type="button">
            @if ($record->actual_end_time !== null || !in_array(auth()->user()->position_id, [2, 3]))
                <?= $langData['ViewAttandence'] ?>
            @else
                <?= $langData['Take_Attandence'] ?>
            @endif
        </button>
        <button id="toggle-form-builder"
            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
            type="button">
            @if ($record->actual_end_time !== null || !in_array(auth()->user()->position_id, [2, 3]))
                <?= $langData['ViewDecisions'] ?>
            @else
                <?= $langData['Add Decision'] ?>
            @endif
        </button>

        @if ($record->decision_by == 1)
            <button id="voiting" data-modal-target="voiting-modal"
                style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                type="button" fdprocessedid="x0wj37" type="button">

                @if ($record->actual_end_time)
                    <?= $langData['Vote'] ?>
                @else
                    <?= $langData['Voting time'] ?>
                @endif
            </button>
        @else
            <button id="voitingSingleUser" data-modal-target="voitingSingleUser-modal"
                style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                type="button" fdprocessedid="x0wj37" type="button">
                @if ($record->actual_end_time)
                    <?= $langData['Vote'] ?>
                @else
                    <?= $langData['Voting time'] ?>
                @endif
            </button>
            @if ($record->created_by == auth()->user()->id)
                <button id="voiting" data-modal-target="voiting-modal"
                    style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                    class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                    type="button" fdprocessedid="x0wj37" type="button">
                    @if ($record->actual_end_time)
                        <?= $langData['Vote'] ?>
                    @else
                        <?= $langData['Voters voting'] ?>
                    @endif
                </button>
            @endif
        @endif

        <button id="viewrecorcd"
            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
            type="button">
            <?= $langData['View the Report'] ?>
        </button>

    </div>


    <div id="decision-modal" style="margin-top: 4%;" tabindex="-1" aria-hidden="true"
        class="hidden fixed inset-0 z-50 flex justify-center items-center overflow-y-auto overflow-x-hidden">
        <!-- Modal Background -->
        <div id="modal-content"
            class="relative w-full max-w-4xl p-6 bg-white dark:bg-gray-800 rounded-xl shadow-xl border-2 border-gray-300 dark:border-gray-600">
            <!-- Modal header -->
            <div class="flex items-center justify-between border-b pb-4 mb-4 dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    <?= $langData['Add Decisions'] ?>
                </h3>
                <div class="flex items-center gap-2">
                    <!-- Maximize Button -->
                    <button id="maximize-btn" type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white">
                        <svg id="maximize-icon" class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 18 18">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M1 1v16h16M1 1h4m0 0v4m0-4 10 10m-10 0h4m0 0v4m0-4 10-10" />
                        </svg>
                        <svg id="minimize-icon" class="w-4 h-4 hidden" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M1 1v16h16M1 1h4m0 0v4m0-4 10 10m-10 0h4m0 0v4m0-4 10-10" />
                        </svg>
                    </button>
                    <!-- Close Button -->
                    <button id="closeform" type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-hide="authentication-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close</span>
                    </button>
                </div>
            </div>
            <!-- Modal body -->
            <div id="modalBody">
                <div class="modal-body" id="form-container">
                    <!-- The form content will be loaded here -->
                </div>
            </div>
        </div>
    </div>




    <!-- Modal -->
    <div id="authentication-modal" style="margin-top: 4%;" tabindex="-1" aria-hidden="true"
        class="hidden fixed inset-0 z-50 flex justify-center items-center overflow-y-auto overflow-x-hidden">

        <!-- Modal Background -->
        <div
            class="relative w-full max-w-4xl p-6 bg-white dark:bg-gray-800 rounded-xl shadow-xl border-2 border-gray-300 dark:border-gray-600">
            <form id="attendanceForm" action="{{ route('saveAttendance') }}" method="POST">
                @csrf
                <input type="hidden" name="session_id" id="sessionId" value="{{ $record->id }}">
                <!-- Modal header -->
                <!-- Modal header -->
                <div class="flex items-center justify-between border-b pb-4 mb-4 dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        <?= $langData['Take_Attandence'] ?>
                    </h3>
                    <div class="flex items-center">
                        <!-- Maximize Icon -->
                        <button id="maximize" type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white mr-2">
                            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <!-- Maximize icon with arrows -->
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 8V4m0 0h4M4 4l5 5m11-5h-4m4 0v4m0-4l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5h-4m4 0v-4m0 4l-5-5" />
                            </svg>
                            <span class="sr-only">Maximize</span>
                        </button>
                        <!-- Close Icon -->
                        <button id="close" type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">Close</span>
                        </button>
                    </div>
                </div>
                <!-- Modal body -->
                <div id="modalBody">
                    <div id="takeAttendance">
                        <!-- Dynamic content goes here -->
                    </div>
                </div>
                <!-- Modal footer -->
                @if (is_null($record->actual_end_time) || $record->actual_end_time === '')
                    @if (in_array(auth()->user()->position_id, [2, 3]))
                        <div class="flex justify-end">
                            <button type="submit"
                                style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                type="button" fdprocessedid="x0wj37">
                                <?= $langData['Save'] ?>
                            </button>
                        </div>
                    @endif
                @endif
            </form>
        </div>
    </div>


    <!-- Voting Modal -->
    <div id="voiting-modal" style="margin-top: 4%;" tabindex="-1" aria-hidden="true"
        class="hidden fixed inset-0 z-50 flex justify-center items-center overflow-y-auto overflow-x-hidden">

        <!-- Modal Background -->
        <div id="modalContent"
            class="relative w-full max-w-4xl p-6 bg-white dark:bg-gray-800 rounded-xl shadow-xl border-2 border-gray-300 dark:border-gray-600">
            <form id="voitingForm" action="{{ route('saveVoiting') }}" method="POST">
                @csrf
                <input type="hidden" name="session_id" id="sessionId" value="{{ $record->id }}">
                <!-- Modal header -->
                <div class="flex items-center justify-between border-b pb-4 mb-4 dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        <?= $langData['Voting'] ?>
                    </h3>
                    <div class="flex items-center">
                        <button id="maximizeVoiting" type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 8V6a2 2 0 0 1 2-2h2M8 20H6a2 2 0 0 1-2-2v-2m14 4h2a2 2 0 0 0 2-2v-2m-4-8h2a2 2 0 0 1 2 2v2" />
                            </svg>
                            <span class="sr-only">Maximize</span>
                        </button>
                        <button id="closeVoiting" type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">Close</span>
                        </button>
                    </div>
                </div>
                <!-- Modal body -->
                <div id="modalBody" class="overflow-auto max-h-96">
                    <div id="takeVoiting">
                        <!-- Dynamic content goes here -->
                    </div>
                </div>
                <!-- Modal footer -->
                @if (is_null($record->actual_end_time) || $record->actual_end_time === '')

                    @if ($record->decision_by == 1 && in_array(auth()->user()->position_id, [2, 3]))
                        <div class="flex justify-end">
                            <button type="submit"
                                style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                type="button" fdprocessedid="x0wj37">
                                <?= $langData['Save'] ?>
                            </button>
                        </div>
                    @endif
                @endif
            </form>
        </div>
    </div>

    <!-- Single User Voting Modal -->
    <div id="voitingSingleUser-modal" style="margin-top: 4%;" tabindex="-1" aria-hidden="true"
        class="hidden fixed inset-0 z-50 flex justify-center items-center overflow-y-auto overflow-x-hidden">

        <!-- Modal Background -->
        <div
            class="relative w-full max-w-4xl p-6 bg-white dark:bg-gray-800 rounded-xl shadow-xl border-2 border-gray-300 dark:border-gray-600">
            <form id="voitingFormSingle" action="{{ route('saveVoiting') }}" method="POST">
                @csrf
                <input type="hidden" name="session_id" id="sessionId" value="{{ $record->id }}">
                <!-- Modal header -->
                <div class="flex items-center justify-between border-b pb-4 mb-4 dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        <?= $langData['Voting'] ?>
                    </h3>
                    <div class="flex items-center">
                        <button id="maximizeVoitingSingle" type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                            <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 8V6a2 2 0 0 1 2-2h2M8 20H6a2 2 0 0 1-2-2v-2m14 4h2a2 2 0 0 0 2-2v-2m-4-8h2a2 2 0 0 1 2 2v2" />
                            </svg>
                            <span class="sr-only">Maximize</span>
                        </button>
                        <button id="closeVoitingSingle" type="button"
                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">Close</span>
                        </button>
                    </div>
                </div>
                <!-- Modal body -->
                <div id="modalBody" class="overflow-auto max-h-96">
                    <div id="takeVoitingSingle">
                        <!-- Dynamic content goes here -->
                    </div>
                </div>
                <!-- Modal footer -->
                @if (is_null($record->actual_end_time) || $record->actual_end_time === '')
                    <div class="flex justify-end">
                        <button type="submit"
                            style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                            type="button" fdprocessedid="x0wj37">
                            <?= $langData['Save'] ?>
                        </button>
                    </div>
                @endif
            </form>
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
    <script src="https://cdn.jsdelivr.net/npm/moment-hijri@2.1.0/moment-hijri.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/xsoh/Hijri.js/Hijri.js"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script>
        var $langData = {
            'Success': `<?= $langData['Success'] ?>`,
            'saving data': `<?= $langData['saving data'] ?>`,
            'ok': `<?= $langData['ok'] ?>`,
            'error': `<?= $langData['error'] ?>`,
            'Add another': `<?= $langData['Add another'] ?>`,
            'end session message': `<?= $langData['end session message'] ?>`,
        };

        $(document).ready(function() {
            function showModaldecision() {
                const modal = document.getElementById('decision-modal');
                modal.classList.remove('hidden');

                // Set display: flex with !important
                modal.style.setProperty('display', 'flex',
                    'important'); // This will apply display: flex with !important

                modal.classList.add('flex');
            }


            function hideModaldecision() {
                const modal = document.getElementById('decision-modal');
                modal.classList.add('hidden');

                // Reset display to default value
                modal.style.setProperty('display', '', 'important'); // This will reset the display with !important
                modal.classList.remove('flex');
            }


            $('#toggle-form-builder').click(function() {
                var recordId = $('#recordId').val();
                // Define locale from PHP to JavaScript
                var locale = @json(app()->getLocale());
                var url =
                    '{{ route('loadFormContent', ['locale' => '__LOCALE__']) }}';
                url = url.replace('__LOCALE__', encodeURIComponent(locale));

                if (recordId) {
                    $.ajax({
                        // url: "{{ route('loadFormContent') }}",
                        url: url,
                        type: 'GET',
                        success: function(response) {
                            $('#form-container').html(response);

                            $.ajax({
                                url: "{{ route('GetFormForSession') }}",
                                type: 'GET',
                                dataType: 'json',
                                data: {
                                    id: recordId,
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                    $('#form-entries-container').empty();

                                    if (Array.isArray(response)) {
                                        response.forEach(function(item, index) {
                                            var $clone = $(
                                                    '#form-entry-template')
                                                .clone().removeAttr('id')
                                                .show();

                                            // Set values in cloned form fields
                                            $clone.find('.agendaId').val(
                                                item.agendaId);
                                            $clone.find('.topicId').val(item
                                                .topicId);
                                            $clone.find('.sessionId').val(
                                                item.sessionId);
                                            $clone.find('.title').text(item
                                                .topicTitle);
                                            // Render files (photos)
                                            // Render files (photos) using DOM manipulation (no HTML in JS strings)
                                            if (item.files && item.files
                                                .length > 0) {
                                                var filePreviewsContainer =
                                                    $clone.find(
                                                        '.file-previews-container'
                                                    );
                                                filePreviewsContainer
                                                    .empty(); // Clear existing previews

                                                item.files.forEach(function(
                                                    file) {
                                                    // Create the file preview container
                                                    var $filePreview =
                                                        $('<div>', {
                                                            class: 'file-preview'
                                                        });

                                                    // Check if the file is an image based on its extension
                                                    var fileExtension =
                                                        file
                                                        .file_path
                                                        .split('.')
                                                        .pop()
                                                        .toLowerCase();
                                                    var isImage = [
                                                        'jpg',
                                                        'jpeg',
                                                        'png',
                                                        'gif'
                                                    ].includes(
                                                        fileExtension
                                                    );

                                                    if (isImage) {
                                                        // If it's an image, create an image element
                                                        var $link =
                                                            $('<a>', {
                                                                href: file
                                                                    .file_path, // Full URL to the image
                                                                class: 'file-link', // Optional: add a class for styling or further customization
                                                            });

                                                        var $image =
                                                            $('<img>', {
                                                                src: file
                                                                    .file_path, // This should be the full URL now
                                                                alt: file
                                                                    .file_name,
                                                                class: 'w-full h-auto' // Apply width 100% for responsiveness
                                                            });

                                                        // Append the image to the link
                                                        $link
                                                            .append(
                                                                $image
                                                            );

                                                        // Append the file preview (image)
                                                        $filePreview
                                                            .append(
                                                                $link
                                                            );
                                                    } else {
                                                        // For non-image files, create a download link
                                                        var $link =
                                                            $('<a>', {
                                                                href: file
                                                                    .file_path, // Full URL to the file
                                                                class: 'file-link', // Optional: add a class for styling or further customization
                                                            });

                                                        // Create a download button (with an icon)
                                                        var $button =
                                                            $('<button>', {
                                                                class: 'download-btn',
                                                                html: `<span class="">${file.file_name}</span>` // Icon for downloading
                                                            });

                                                        // Append the download button to the link
                                                        $link
                                                            .append(
                                                                $button
                                                            );

                                                        // Append the file preview (button for non-image files)
                                                        $filePreview
                                                            .append(
                                                                $link
                                                            );
                                                    }

                                                    // Append the file preview to the container
                                                    filePreviewsContainer
                                                        .append(
                                                            $filePreview
                                                        );

                                                    // Make the file trigger the download when clicked
                                                    $filePreview.on(
                                                        'click',
                                                        function(
                                                            e) {
                                                            // Prevent the default link behavior
                                                            e
                                                                .preventDefault();

                                                            // Dynamically create a download link
                                                            var downloadLink =
                                                                document
                                                                .createElement(
                                                                    'a'
                                                                );
                                                            downloadLink
                                                                .href =
                                                                file
                                                                .file_path; // Full URL to the file

                                                            // Ensure that file_name exists, and use it for the download attribute
                                                            downloadLink
                                                                .download =
                                                                file
                                                                .file_name ||
                                                                "default-filename"; // Fallback if file_name is null

                                                            // Append the link to the document body (needed for Firefox)
                                                            document
                                                                .body
                                                                .appendChild(
                                                                    downloadLink
                                                                );

                                                            // Trigger the download
                                                            downloadLink
                                                                .click();

                                                            // Remove the link from the document after triggering the download
                                                            document
                                                                .body
                                                                .removeChild(
                                                                    downloadLink
                                                                );
                                                        });
                                                });
                                            }





                                            // Check if decisionsNames array has items
                                            if (item.decisionsNames.length >
                                                0) {
                                                var radioButtons = '';

                                                item.decisionsNames.forEach(
                                                    function(
                                                        decisionName,
                                                        index) {
                                                        // Ensure that item.decisions is not empty before accessing its properties
                                                        var isSelected =
                                                            '';
                                                        if (item
                                                            .decisions
                                                            .length >
                                                            0 && item
                                                            .decisions[
                                                                0]
                                                            .decisionChoice ===
                                                            decisionName
                                                        ) {
                                                            isSelected =
                                                                'checked';
                                                        }

                                                        radioButtons += `
                                                            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                                                                <input type="radio" name="decision[${item.agendaId}]" value="${decisionName}" class="decision-radio" ${isSelected}>
                                                                ${decisionName}
                                                            </label>
                                                        `;
                                                    });

                                                $clone.find(
                                                        '.decisionChoices')
                                                    .html(radioButtons);
                                            }


                                            if (item.decisions_approval ==
                                                1) {
                                                $clone.find('.decision')
                                                    .prop('disabled', true);
                                            }

                                            if (item.decisions.length > 0) {
                                                $clone.find('.decision')
                                                    .val(item.decisions[0]
                                                        .decision
                                                    ); // Example: using the first decision
                                            } else {
                                                $clone.find('.decision')
                                                    .val('');
                                            }

                                            $('#form-entries-container')
                                                .append($clone);

                                            $('#form-entries-container')
                                                .append($clone);

                                            // Handle contents
                                            item.contents.forEach(function(
                                                content,
                                                contentIndex) {
                                                var $contentClone =
                                                    $clone.find(
                                                        '.fb-editor-edit'
                                                    )
                                                    .clone()
                                                    .removeAttr(
                                                        'id')
                                                    .show();

                                                $clone.find(
                                                    '.fb-editor-edit'
                                                ).append(
                                                    $contentClone
                                                );

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
                                                        fieldData
                                                    ) {
                                                        const
                                                            randomNumber =
                                                            Math
                                                            .floor(
                                                                Math
                                                                .random() *
                                                                10000
                                                            ); // Generates a random number between 0 and 9999
                                                        const
                                                            uniqueId =
                                                            `${fieldData.name}_${randomNumber}`;

                                                        return {
                                                            field: `<select id="${uniqueId}" name="${fieldData.name}" class="form-control"><option>Loading countries...</option></select>`,
                                                            onRender: function() {
                                                                // Fetch and populate Arab countries only if not already populated
                                                                fetchCountries
                                                                    (uniqueId,
                                                                        fieldData
                                                                        .value
                                                                    );
                                                            }
                                                        };
                                                    },
                                                    'hijri-date-picker': function(
                                                        fieldData
                                                    ) {
                                                        const
                                                            uniqueId =
                                                            `${fieldData.name}_${Math.floor(Math.random() * 10000)}`;

                                                        return {
                                                            field: `<input type="text" id="${uniqueId}" name="${fieldData.name}" class="form-control hijri-datepicker" placeholder="Select Hijri Date"/>`,
                                                            onRender: function() {
                                                                var currDate =
                                                                    '';

                                                                function initWork() {
                                                                    // Check if HijriJS is available
                                                                    if (typeof HijriJS !==
                                                                        'undefined'
                                                                    ) {
                                                                        // If the fieldData has a value, use it, otherwise use today's date as fallback
                                                                        currDate
                                                                            =
                                                                            fieldData
                                                                            .value ||
                                                                            HijriJS
                                                                            .today()
                                                                            .toString(); // Default to today's Hijri date if none is provided

                                                                        // If there's an 'H' at the end of the year, remove it (e.g., "1440H" -> "1440")
                                                                        if (currDate
                                                                            .endsWith(
                                                                                'H'
                                                                            )
                                                                        ) {
                                                                            currDate
                                                                                =
                                                                                currDate
                                                                                .slice(
                                                                                    0,
                                                                                    -
                                                                                    1
                                                                                ); // Remove the 'H' from the year
                                                                        }

                                                                        // Reformat the date from dd/mm/yyyy to dd-mm-yyyy
                                                                        currDate
                                                                            =
                                                                            currDate
                                                                            .split(
                                                                                '/'
                                                                            )
                                                                            .join(
                                                                                '-'
                                                                            );

                                                                        // Set the date input field to currDate so that the datepicker sets it as the current date
                                                                        $(`#${uniqueId}`)
                                                                            .val(
                                                                                currDate
                                                                            );
                                                                    }
                                                                }

                                                                $(function() {
                                                                    // Initialize the Hijri Datepicker on the uniqueId
                                                                    $(`#${uniqueId}`)
                                                                        .datepicker({
                                                                            changeMonth: true, // Show months menu
                                                                            changeYear: true, // Show years menu
                                                                            dayNamesMin: [
                                                                                "س",
                                                                                "ج",
                                                                                "خ",
                                                                                "ر",
                                                                                "ث",
                                                                                "ن",
                                                                                "ح"
                                                                            ], // Arabic day names
                                                                            dateFormat: "dd-mm-yy", // Set format to dd-mm-yyyy
                                                                            monthNames: [
                                                                                "محرم",
                                                                                "صفر",
                                                                                "ربيع الأول",
                                                                                "ربيع الثاني",
                                                                                "جمادى الأول",
                                                                                "جمادى الثاني",
                                                                                "رجب",
                                                                                "شعبان",
                                                                                "رمضان",
                                                                                "شوال",
                                                                                "ذو القعدة",
                                                                                "ذو الحجة"
                                                                            ],
                                                                            yearRange: "c-0:c+15", // Year range in Hijri from current year and +15 years
                                                                            monthNamesShort: [
                                                                                "محرم",
                                                                                "صفر",
                                                                                "ربيع ١",
                                                                                "ربيع ٢",
                                                                                "جمادى ١",
                                                                                "جمادى ٢",
                                                                                "رجب",
                                                                                "شعبان",
                                                                                "رمضان",
                                                                                "شوال",
                                                                                "ذو القعدة",
                                                                                "ذو الحجة"
                                                                            ],
                                                                            showAnim: 'bounce',
                                                                            prevText: "السابق", // Arabic for "Previous"
                                                                            nextText: "التالي", // Arabic for "Next"
                                                                        });

                                                                    // Call the initWork function to initialize the date with today's date or the fetched date
                                                                    initWork
                                                                        ();
                                                                });
                                                            }
                                                        };
                                                    }
                                                };

                                                // Function to fetch countries and populate the select element
                                                function fetchCountries(
                                                    elementId,
                                                    selectedValue) {
                                                    var selectElement =
                                                        document
                                                        .getElementById(
                                                            elementId
                                                        );
                                                    if (selectElement &&
                                                        selectElement
                                                        .dataset
                                                        .fetched !==
                                                        'true') {
                                                        var appUrl =
                                                            '{{ env('APP_URL') }}';
                                                        var localJsonUrl =
                                                            appUrl +
                                                            '/admin/countries-json';
                                                        console.log(
                                                            localJsonUrl
                                                        );
                                                        // Use fetch to get the data from the generated URL
                                                        fetch(
                                                                localJsonUrl
                                                            )
                                                            .then(
                                                                response =>
                                                                response
                                                                .json()
                                                            )
                                                            .then(
                                                                data => {
                                                                    // Filter countries to include only Arab countries or those with available translations
                                                                    var options =
                                                                        data
                                                                        .map(
                                                                            country => {
                                                                                var countryName =
                                                                                    (country
                                                                                        .translations &&
                                                                                        country
                                                                                        .translations
                                                                                        .ara
                                                                                    ) ?
                                                                                    country
                                                                                    .translations
                                                                                    .ara
                                                                                    .common :
                                                                                    'Unknown Country'; // Fallback for non-Arab countries
                                                                                var isSelected =
                                                                                    countryName ===
                                                                                    selectedValue ?
                                                                                    'selected' :
                                                                                    '';
                                                                                return `<option value="${countryName}" ${isSelected}>${countryName}</option>`;
                                                                            }
                                                                        )
                                                                        .join(
                                                                            ''
                                                                        );

                                                                    // Update the select element with the options
                                                                    selectElement
                                                                        .innerHTML =
                                                                        options;
                                                                    selectElement
                                                                        .dataset
                                                                        .fetched =
                                                                        'true'; // Mark as fetched
                                                                })
                                                            .catch(
                                                                error => {
                                                                    selectElement
                                                                        .innerHTML =
                                                                        '<option>Error loading countries</option>';
                                                                    console
                                                                        .error(
                                                                            'Error fetching local JSON:',
                                                                            error
                                                                        );
                                                                });
                                                    }
                                                }

                                                // Options for the form builder, including custom fields and templates
                                                var options = {
                                                    disableFields: [
                                                        'autocomplete',
                                                        'button',
                                                        'header',
                                                        'hidden'
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

                                                $contentClone
                                                    .formBuilder(
                                                        options)
                                                    .promise.then(
                                                        function(
                                                            fb) {
                                                            var fieldData =
                                                                JSON
                                                                .parse(
                                                                    content
                                                                );

                                                            // Ensure unique names for radio buttons
                                                            fieldData
                                                                .forEach(
                                                                    function(
                                                                        field
                                                                    ) {
                                                                        if (field
                                                                            .type ===
                                                                            'radio-group'
                                                                        ) {
                                                                            field
                                                                                .name =
                                                                                `${field.name}_${index}_${contentIndex}`;
                                                                        }
                                                                    }
                                                                );

                                                            fb.actions
                                                                .setData(
                                                                    fieldData
                                                                );

                                                        });
                                            });
                                        });
                                    } else {
                                        console.error('Response is not an array:',
                                            response);
                                    }

                                    // Show the modal
                                    showModaldecision();
                                },
                                error: function(xhr, status, error) {
                                    console.error(error);

                                    // Check if the backend returned a JSON response
                                    if (xhr.responseJSON && xhr.responseJSON
                                        .error) {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: xhr.responseJSON
                                                .error, // Display the backend error message
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: 'An unexpected error occurred. Please try again.',
                                        });
                                    }
                                }

                            });

                        },
                        error: function(xhr, status, error) {
                            console.error(error);

                            // Check if the backend returned a JSON response
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON
                                        .error, // Display the backend error message
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'An unexpected error occurred. Please try again.',
                                });
                            }
                        }

                    });
                }
            });

            // Hide modal on close button click
            $('#closeform').click(function() {
                hideModaldecision();
            });
        });








        $(document).ready(function() {
            function showModal() {
                const modal = document.getElementById('authentication-modal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');

                // Fetch the updated data when the modal is opened
                fetchAttendanceData();
            }

            function hideModal() {
                const modal = document.getElementById('authentication-modal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            // Define locale from PHP to JavaScript
            var locale = @json(app()->getLocale());

            function fetchAttendanceData() {
                // Construct the URL dynamically with session_id and locale
                var url =
                    '{{ route('fetchAttendance', ['session_id' => '__SESSION_ID__', 'locale' => '__LOCALE__']) }}';
                url = url.replace('__SESSION_ID__', '{{ $record->id }}').replace('__LOCALE__',
                    encodeURIComponent(locale));

                $.ajax({
                    type: 'GET',
                    url: url,
                    success: function(data) {
                        $('#takeAttendance').html(data);
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Failed to fetch attendance data!',
                        });
                    }
                });
            }

            document.getElementById('open').addEventListener('click', showModal);
            document.getElementById('close').addEventListener('click', hideModal);

            // Handle form submission
            $('#attendanceForm').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var actionUrl = form.attr('action');
                var formData = form.serialize();

                // Fetch latest attendance data before submitting the form
                $.ajax({
                    type: 'GET',
                    url: '{{ route('fetchAttendance', ['session_id' => $record->id]) }}',
                    success: function(data) {
                        $('#takeAttendance').html(data);

                        // Proceed with form submission after updating data
                        $.ajax({
                            type: 'POST',
                            url: actionUrl,
                            data: formData,
                            success: function(response) {
                                Swal.fire({
                                    position: 'center',
                                    title: $langData['Success'],
                                    text: $langData['saving data'],
                                    icon: 'success',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                $('#close').click(); // Close the modal

                                // Fetch the updated data and update the modal content
                                fetchAttendanceData();
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Something went wrong!',
                                });
                            }
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Failed to fetch attendance data!',
                        });
                    }
                });
            });
        });







        $(document).ready(function() {
            function showModalVoiting() {
                const modal = document.getElementById('voiting-modal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');

                // Fetch the updated data when the modal is opened
                fetchVoitingData();
            }

            function hideModalVoiting() {
                const modal = document.getElementById('voiting-modal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            function fetchVoitingData() {
                // Define locale from PHP to JavaScript
                var locale = @json(app()->getLocale());
                // Construct the URL dynamically with session_id and locale
                var url =
                    '{{ route('fetchVoiting', ['session_id' => '__SESSION_ID__', 'locale' => '__LOCALE__']) }}';
                url = url.replace('__SESSION_ID__', '{{ $record->id }}').replace('__LOCALE__',
                    encodeURIComponent(locale));

                $.ajax({
                    type: 'GET',
                    // url: '{{ route('fetchVoiting', ['session_id' => $record->id]) }}',
                    url: url,
                    success: function(data) {
                        $('#takeVoiting').html(data);
                    },
                    error: function(xhr) {
                        if (xhr.status === 404) {
                            hideModalVoiting();
                            Swal.fire({
                                icon: 'error',
                                title: 'No Decisions Available',
                                text: 'There are no decisions available for this session.',
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Failed to fetch voiting data!',
                            });
                        }
                    }
                });
            }


            document.getElementById('voiting').addEventListener('click', showModalVoiting);
            document.getElementById('closeVoiting').addEventListener('click', hideModalVoiting);

            // Handle form submission
            $('#voitingForm').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var actionUrl = form.attr('action');
                var formData = form.serialize();

                // Fetch latest attendance data before submitting the form
                $.ajax({
                    type: 'GET',
                    url: '{{ route('fetchVoiting', ['session_id' => $record->id]) }}',
                    success: function(data) {
                        $('#takeVoiting').html(data);
                        // Proceed with form submission after updating data
                        $.ajax({
                            type: 'POST',
                            url: actionUrl,
                            data: formData,
                            success: function(response) {
                                Swal.fire({
                                    position: 'center',
                                    title: $langData['Success'],
                                    text: $langData['saving data'],
                                    icon: 'success',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                $('#closeVoiting').click(); // Close the modal

                                // Fetch the updated data and update the modal content
                                fetchVoitingData();
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Something went wrong!',
                                });
                            }
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Failed to fetch attendance data!',
                        });
                    }
                });
            });
        });


        $(document).ready(function() {
            function showModalVoitingSingle() {
                const modal = document.getElementById('voitingSingleUser-modal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');

                // Fetch the updated data when the modal is opened
                fetchVoitingDataSingle();
            }

            function hideModalVoitingSingle() {
                const modal = document.getElementById('voitingSingleUser-modal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            function fetchVoitingDataSingle() {
                // Define locale from PHP to JavaScript
                var locale = @json(app()->getLocale());
                // Construct the URL dynamically with session_id and locale
                var url =
                    '{{ route('fetchVoitingSingle', ['session_id' => '__SESSION_ID__', 'locale' => '__LOCALE__']) }}';
                url = url.replace('__SESSION_ID__', '{{ $record->id }}').replace('__LOCALE__',
                    encodeURIComponent(locale));

                $.ajax({
                    type: 'GET',
                    // url: '{{ route('fetchVoitingSingle', ['session_id' => $record->id]) }}',
                    url: url,
                    success: function(data) {
                        $('#takeVoitingSingle').html(data);
                    },
                    error: function(xhr) {
                        if (xhr.status === 404) {
                            hideModalVoiting();
                            Swal.fire({
                                icon: 'error',
                                title: 'No Decisions Available',
                                text: 'There are no decisions available for this session.',
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Failed to fetch voiting data!',
                            });
                        }
                    }
                });
            }


            document.getElementById('voitingSingleUser').addEventListener('click', showModalVoitingSingle);
            document.getElementById('closeVoitingSingle').addEventListener('click', hideModalVoitingSingle);

            // Handle form submission
            $('#voitingFormSingle').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var actionUrl = form.attr('action');
                var formData = form.serialize();

                // Fetch latest attendance data before submitting the form
                $.ajax({
                    type: 'GET',
                    url: '{{ route('fetchVoitingSingle', ['session_id' => $record->id]) }}',
                    success: function(data) {
                        $('#takeVoitingSingle').html(data);
                        // Proceed with form submission after updating data
                        $.ajax({
                            type: 'POST',
                            url: actionUrl,
                            data: formData,
                            success: function(response) {
                                Swal.fire({
                                    position: 'center',
                                    title: $langData['Success'],
                                    text: $langData['saving data'],
                                    icon: 'success',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                $('#closeVoitingSingle').click(); // Close the modal

                                // Fetch the updated data and update the modal content
                                fetchVoitingDataSingle();
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Something went wrong!',
                                });
                            }
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Failed to fetch attendance data!',
                        });
                    }
                });
            });
        });

        $(document).ready(function() {
            $('#viewrecorcd').on('click', function() {
                // Get the recordId value from an input field with ID 'recordId'
                var recordId = $('#recordId').val();

                // Define locale from PHP to JavaScript
                var locale = @json(app()->getLocale());

                // Construct the URL dynamically with session_id and locale
                var url =
                    '{{ route('viewRecord', ['locale' => '__LOCALE__']) }}';
                url = url.replace('__LOCALE__', encodeURIComponent(locale));

                // Make an AJAX request to the server
                $.ajax({
                    url: url, // The URL for the AJAX request (defined in routes/web.php)
                    method: 'GET', // HTTP method for the request
                    data: {
                        recordId: recordId
                    }, // Send recordId as a parameter to the server
                    success: function(response) {
                        if (response.conditionMet) {
                            window.location.href = response
                                .redirectUrl; // Redirect URL from the server response
                        } else {
                            Swal.fire({
                                title: $langData['error'],
                                text: response.errorMessage ||
                                    'An error occurred while processing your request.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred while making the request: ' +
                                textStatus,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });






        let totalElapsedTime = 0;
        let startTime;
        let stopwatchInterval;
        let isRunning = false;
        let lapTimes = [];
        let lastUpdateTime = 0;
        let hour = 0;
        let minute = 0;
        let second = 0;
        let count = 0;
        let formattedTime = "00:00:00:000"; // Initialize formattedTime with a default value

        // Function to start the stopwatch
        function startStopwatch() {
            if (!isRunning) {
                let recordId = document.getElementById('recordId').value;

                // Make AJAX request to update the start time in the database
                $.ajax({
                    url: "{{ route('sessions.start') }}",
                    method: 'POST',
                    data: {
                        sessionId: recordId,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            console.log("success")
                            document.querySelector('.btn1').style.display = 'none';
                            document.querySelector('.btn4').style.display = 'inline-block';
                            document.querySelector('.btn4').click();
                        } else {
                            console.log("error")
                        }
                    },
                    error: function(response) {
                        console.log("error")

                    }
                });
            }
        }

        // Function to stop the stopwatch
        function stopStopwatch() {
            if (isRunning) {
                clearInterval(stopwatchInterval);
                totalElapsedTime += performance.now() - startTime;
                isRunning = false;
            }
        }

        // Function to reset the stopwatch
        function resetStopwatch() {
            clearInterval(stopwatchInterval);
            totalElapsedTime = 0;
            isRunning = false;
            lapTimes = [];
            lastUpdateTime = 0;
            updateStopwatch();
        }

        // Function to complete the stopwatch
        function completeStopwatch() {
            if (!isRunning) {
                let recordId = document.getElementById('recordId').value;
                // getState(recordId); // Retrieve the state including the elapsed time
                getState(sessionCode); // Retrieve the state including the elapsed time

                startTime = performance.now() - totalElapsedTime;
                lastUpdateTime = performance.now();
                stopwatchInterval = setInterval(updateStopwatch, 1); // Update every 1 millisecond
                isRunning = true;

                saveState(); // Save the updated state including count to localStorage
            }
        }

        // Function to update the stopwatch display
        function updateStopwatch() {
            const currentTime = performance.now();
            const elapsed = isRunning ? currentTime - startTime : totalElapsedTime;

            hour = Math.floor((elapsed / (1000 * 60 * 60)) % 24);
            minute = Math.floor((elapsed / (1000 * 60)) % 60);
            second = Math.floor((elapsed / 1000) % 60);

            const milliseconds = Math.floor(elapsed % 1000);
            formattedTime = pad(hour) + ':' + pad(minute) + ':' + pad(second);
            document.getElementById('stopwatch').innerText = formattedTime;

            saveState(); // Save state to localStorage on every update
        }

        // Function to save current state to localStorage
        function saveState() {
            // let recordId = document.getElementById('recordId').value; // Get the recordId from the hidden input
            let sessionCode = document.getElementById('sessionCode').value; // Get the sessionCode from the hidden input

            // Save the current time data along with recordId and formattedTime to localStorage
            localStorage.setItem(sessionCode, JSON.stringify({
                hour: hour,
                minute: minute,
                second: second,
                formattedTime: formattedTime // Include formattedTime in localStorage
            }));
        }

        // Function to retrieve state from localStorage for a specific recordId
        // function getState(recordId) {
        function getState(sessionCode) {
            // let state = localStorage.getItem(recordId);
            let state = localStorage.getItem(sessionCode);
            if (state) {
                state = JSON.parse(state);
                hour = state.hour;
                minute = state.minute;
                second = state.second;
                formattedTime = state.formattedTime;

                // Calculate totalElapsedTime based on the stored time
                totalElapsedTime = (hour * 3600000) + (minute * 60000) + (second * 1000);

                // Update the stopwatch display
                document.getElementById('stopwatch').innerText = formattedTime;
            }
        }

        // Function to change the color zone
        function changeColorZone(color) {
            const frame = document.getElementById('stopwatch-frame');
            frame.style.backgroundColor = color;

            const colorPickerFrame = document.querySelector('.color-picker-frame');
            colorPickerFrame.style.backgroundColor = color;
        }

        // Function to pad single digits with leading zeros
        function pad(value) {
            return value < 10 ? '0' + value : value;
        }

        // Load the state from localStorage when the page loads (if needed)
        document.addEventListener('DOMContentLoaded', function() {
            // let recordId = document.getElementById('recordId').value; // Get the recordId from the hidden input
            // getState(recordId); // Retrieve and update state for the current recordId
            let sessionCode = document.getElementById('sessionCode')
                .value; // Get the sessionCode from the hidden input

            getState(sessionCode); // Retrieve and update state for the current sessionCode
        });

        function endSession() {
            let formattedTime = document.getElementById('stopwatch').innerText.trim();
            let timeParts = formattedTime.split(':');

            let hour = parseInt(timeParts[0]);
            let minute = parseInt(timeParts[1]);
            let second = parseInt(timeParts[2]);

            let recordId = document.getElementById('recordId').value;
            $.ajax({
                url: "{{ route('saveTime') }}",
                method: 'POST',
                data: {
                    hour: hour,
                    minute: minute,
                    second: second,
                    recordId: recordId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        title: $langData['Success'],
                        text: $langData['end session message'],
                        icon: 'success',
                        timer: 1500, // Timer in milliseconds (2 seconds)
                        showConfirmButton: false // Hide the "OK" button
                    }).then(function() {
                        window.history.back(); // Redirect back to the previous page
                    });
                },

                error: function(response) {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "Something went wrong!",
                    });
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('decision-modal'); // Ensure this matches your modal's ID
            const modalContent = modal.querySelector('.relative');
            const maximizeBtn = document.getElementById('maximize-btn');
            const maximizeIcon = document.getElementById('maximize-icon');
            const minimizeIcon = document.getElementById('minimize-icon');
            let isMaximized = false;

            // Function to reset the modal to its default state
            function resetModalToDefault() {
                const modal = document.getElementById('decision-modal');
                const modalContent = modal.querySelector('.relative');

                // Reset modal styles
                modal.style.marginTop = '4%';

                // Reset modal content classes
                modalContent.classList.remove('w-full', 'h-full', 'max-w-none', 'rounded-none');
                modalContent.classList.add('w-full', 'max-w-4xl', 'rounded-xl');
            }

            // Event listener for the maximize button
            maximizeBtn.addEventListener('click', function () {
    if (isMaximized) {
        // Restore to default style
        resetModalToDefault();
    } else {
        // Maximize to full screen
        modal.style.marginTop = '0';
        modal.style.maxHeight = "auto";
        modal.style.overflow = "hidden"; // Corrected the missing quotes

        const modalBody = modal.querySelector('.modal-body');
        if (modalBody) {
            modalBody.style.maxHeight = "auto";
        }

        modalContent.classList.remove('max-w-4xl', 'rounded-xl');
        modalContent.classList.add('w-full', 'h-full', 'max-w-none', 'rounded-none');
    }
    isMaximized = !isMaximized;
});

        });

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('authentication-modal');
            const maximizeButton = document.getElementById('maximize');
            const closeButton = document.getElementById('close');
            const modalContent = modal.querySelector('.relative');

            let isMaximized = false;

            // Function to reset modal to default style
            function resetModalToDefault() {
                const modal = document.getElementById('authentication-modal');
                const modalContent = modal.querySelector('.relative');

                // Reset modal styles
                modal.style.marginTop = '4%';

                // Reset modal content classes
                modalContent.classList.remove('w-full', 'h-full', 'max-w-none', 'rounded-none');
                modalContent.classList.add('w-full', 'max-w-4xl', 'rounded-xl');
            }

            // Maximize button click handler
            maximizeButton.addEventListener('click', function() {
                if (isMaximized) {
                    // Restore to default style
                    resetModalToDefault();
                } else {
                    // Maximize to full screen
                    modal.style.marginTop = '0';
                    modalContent.classList.remove('max-w-4xl', 'rounded-xl');
                    modalContent.classList.add('w-full', 'h-full', 'max-w-none', 'rounded-none');
                }
                isMaximized = !isMaximized;
            });



            // Ensure modal opens in default style
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    resetModalToDefault();
                }
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('voiting-modal');
            const modalContent = document.getElementById('modalContent');
            const maximizeButton = document.getElementById('maximizeVoiting');
            const closeButton = document.getElementById('closeVoiting');
            const takeVoiting = document.getElementById('takeVoiting');

            let isMaximized = false;

            maximizeButton.addEventListener('click', function() {
                if (isMaximized) {
                    // Reset to original size
                    modalContent.classList.remove('w-full', 'h-full', 'max-w-none', 'max-h-none');
                    modalContent.classList.add('w-full', 'max-w-4xl');
                    isMaximized = false;
                    // Reset any dynamic content if needed
                    takeVoiting.innerHTML = ''; // Example: Clear dynamic content
                } else {
                    // Maximize to full screen
                    modal.style.marginTop = '0';
                    modalContent.classList.remove('max-w-4xl', 'rounded-xl');
                    modalContent.classList.add('w-full', 'h-full', 'max-w-none', 'rounded-none');
                }
            });

            closeButton.addEventListener('click', function() {
                modal.classList.add('hidden');
                // Reset the modal to its original state when closed
                modalContent.classList.remove('w-full', 'h-full', 'max-w-none', 'max-h-none');
                modalContent.classList.add('w-full', 'max-w-4xl');
                isMaximized = false;
                takeVoiting.innerHTML = ''; // Example: Clear dynamic content
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('voitingSingleUser-modal');
            const maximizeButton = document.getElementById('maximizeVoitingSingle');
            const closeButton = document.getElementById('close');
            const modalContent = modal.querySelector('.relative');

            let isMaximized = false;

            // Function to reset modal to default style
            function resetModalToDefault() {
                const modal = document.getElementById('voitingSingleUser-modal');
                const modalContent = modal.querySelector('.relative');

                // Reset modal styles
                modal.style.marginTop = '4%';

                // Reset modal content classes
                modalContent.classList.remove('w-full', 'h-full', 'max-w-none', 'rounded-none');
                modalContent.classList.add('w-full', 'max-w-4xl', 'rounded-xl');
            }

            // Maximize button click handler
            maximizeButton.addEventListener('click', function() {
                if (isMaximized) {
                    // Restore to default style
                    resetModalToDefault();
                } else {
                    // Maximize to full screen
                    modal.style.marginTop = '0';
                    modalContent.classList.remove('max-w-4xl', 'rounded-xl');
                    modalContent.classList.add('w-full', 'h-full', 'max-w-none', 'rounded-none');
                }
                isMaximized = !isMaximized;
            });



            // Ensure modal opens in default style
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    resetModalToDefault();
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('voitingSingleUser-modal');
            const maximizeButton = document.getElementById('maximizeVoitingSingle');
            const closeButton = document.getElementById('close');
            const modalContent = modal.querySelector('.relative');

            let isMaximized = false;

            // Function to reset modal to default style
            function resetModalToDefault() {
                const modal = document.getElementById('voitingSingleUser-modal');
                const modalContent = modal.querySelector('.relative');

                // Reset modal styles
                modal.style.marginTop = '4%';

                // Reset modal content classes
                modalContent.classList.remove('w-full', 'h-full', 'max-w-none', 'rounded-none');
                modalContent.classList.add('w-full', 'max-w-4xl', 'rounded-xl');
            }

            // Maximize button click handler
            maximizeButton.addEventListener('click', function() {
                if (isMaximized) {
                    // Restore to default style
                    resetModalToDefault();
                } else {
                    // Maximize to full screen
                    modal.style.marginTop = '0';
                    modalContent.classList.remove('max-w-4xl', 'rounded-xl');
                    modalContent.classList.add('w-full', 'h-full', 'max-w-none', 'rounded-none');
                }
                isMaximized = !isMaximized;
            });



            // Ensure modal opens in default style
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    resetModalToDefault();
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('voiting-modal');
            const maximizeButton = document.getElementById('maximizeVoiting');
            const closeButton = document.getElementById('close');
            const modalContent = modal.querySelector('.relative');

            let isMaximized = false;

            // Function to reset modal to default style
            function resetModalToDefault() {
                const modal = document.getElementById('voiting-modal');
                const modalContent = modal.querySelector('.relative');

                // Reset modal styles
                modal.style.marginTop = '4%';

                // Reset modal content classes
                modalContent.classList.remove('w-full', 'h-full', 'max-w-none', 'rounded-none');
                modalContent.classList.add('w-full', 'max-w-4xl', 'rounded-xl');
            }

            // Maximize button click handler
            maximizeButton.addEventListener('click', function() {
                if (isMaximized) {
                    // Restore to default style
                    resetModalToDefault();
                } else {
                    // Maximize to full screen
                    modal.style.marginTop = '0';
                    modalContent.classList.remove('max-w-4xl', 'rounded-xl');
                    modalContent.classList.add('w-full', 'h-full', 'max-w-none', 'rounded-none');
                }
                isMaximized = !isMaximized;
            });



            // Ensure modal opens in default style
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    resetModalToDefault();
                }
            });
        });
    </script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
        /* Optional: Ensure modal content looks good when maximized */
        #authentication-modal .relative {
            transition: all 0.3s ease;
        }

        /* Maximized modal */
        #decision-modal.relative {
            transition: all 0.3s ease;
            width: 1000px;
        }

        /* #authentication-modal .relative.w-full {
    max-width: 100%;
    height: 100vh;
    margin: 0;
    border-radius: 0;
} */
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

        .dark .dark\:bg-gray-900 {
            --tw-bg-opacity: 1;
            background-color: rgba(var(--gray-900), var(--tw-bg-opacity));
        }

        :is(.dark .dark\:text-white) {
            --tw-text-opacity: 1;
            color: rgb(255 255 255 / var(--tw-text-opacity));
        }

        :root.dark {
            color-scheme: dark;
        }

        @media (min-width: 1024px) {
            :is(.dark .dark\:lg\:bg-transparent) {
                background-color: transparent;
            }
        }

        /* Override dark mode ring color */
        .dark .dark\:ring-white\/10 {
            --tw-ring-color: initial !important;
        }

        :is(.dark .dark\:bg-custom-500) {
            --tw-bg-opacity: 1;
            background-color: rgba(var(--c-500), var(--tw-bg-opacity));
        }

        .hover\:bg-custom-500 {
            --tw-bg-opacity: 1;
            background-color: rgba(var(--c-500), var(--tw-bg-opacity));
        }

        /* .ui-sortable {
            min-height: 558px !important;
        } */

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f0f0;
            font-family: 'Cairo', sans-serif;
            /* Ensure the font-family is applied correctly */
            margin: 0;
        }


        #stopwatch-container {
            text-align: center;
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        #stopwatch-frame {
            margin-bottom: 20px;
        }

        #stopwatch {
            font-size: 3rem;
            font-weight: bold;
            color: #333;
        }

        #controls {
            margin-bottom: 20px;
        }

        .btn {
            background: #607D8B;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 5px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }

        .btn1 {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 5px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }

        .btn2 {
            /* background: #29d; */
            background: #dc3741;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 5px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }

        .btn4 {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 5px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }

        .btn3 {
            background: #575760;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 5px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }

        /* .btn1:hover {
            background: #45a049;
        } */

        .color-picker-container {
            margin-top: 20px;
        }

        .color-picker-frame input[type="color"] {
            padding: 5px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .attenstatus {
            margin: 0px 5px;
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

        #voiting-modal,
        #voitingSingleUser-modal {
            /* max-height: 80vh; */
            /* Use viewport height for a responsive modal */
            overflow-y: auto;
            /* Enable scroll on the whole modal if needed */
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
