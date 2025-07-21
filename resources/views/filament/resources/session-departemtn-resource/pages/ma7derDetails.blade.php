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


    <div>
        @if ($decisionApproval != 1)
            @if (auth()->user()->id == $sessionResposibleId)
                <button id="open" data-modal-target="approval-modal"
                    style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                    class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                    type="button" fdprocessedid="x0wj37" type="button">
                    <?= $langData['Approval on report'] ?>
                </button>
            @endif
        @endif
        @if (!$decisionApproval)
            <!-- Apply Signature Button -->
            @if (auth()->user()->id != $sessionResposibleId && $absentOrNot == 1)
                <button id="apply-signature-button" data-modal-target="signature-modal"
                    style="--c-400:var(--success-400);--c-500:var(--success-500);--c-600:var(--success-600);"
                    class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                    type="button" fdprocessedid="x0wj37" type="button">
                    {{ $langData['Apply Signature'] }}
                </button>
            @endif
        @endif




        <div id="approval-modal" class="modal-background hidden">
            <div class="modal-content">
                <!-- Modal header -->
                <div class="modal-header">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        <?= $langData['Approval on report'] ?>
                    </h3>
                    <button type="button" class="close-button" id="close">
                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="p-4 md:p-5 space-y-4">
                    <form action="{{ route('session-decision-approval', $recordId) }}" id="decisionApprovalForm"
                        method="post">
                        @csrf
                        <input type="hidden" name="session_id" id="sessionId" value="{{ $recordId }}">
                        <label for="approval"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"><?= $langData['Decision'] ?></label>
                        <select name="approval" id="approvalDecision" class="form-select mt-1 block w-full border"
                            @if ($decisionApproval != null) disabled @endif required>
                            <option disabled selected value=""><?= $langData['Select'] ?></option>
                            <option @if ($decisionApproval == 1) selected @endif value="1">
                                <?= $langData['Accepted'] ?></option>
                            <option @if ($decisionApproval == 2) selected @endif value="2">
                                <?= $langData['Rejected'] ?></option>
                        </select>

                        <div id="rejectReasonWrapper" class="@if ($decisionApproval != null && $decisionApproval != 2) hidden @endif mt-3">
                            <label for="rejectReason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                <?= $langData['Rejected reason'] ?>
                            </label>
                            <textarea id="rejectReason" placeholder="<?= $langData['Enter rejection reason here'] ?>"
                                @if ($decisionApproval != null) disabled @endif
                                class="form-textarea mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                rows="3">{{ $decisionApprovalReason }}</textarea>
                        </div>

                        @foreach ($decisionsStatusDependOnHead as $decision)
                            <div class="border p-4 rounded-lg shadow-md bg-white dark:bg-gray-800"
                                style="margin-top: 4px">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                    <?= $langData['Resolution on Resolutions'] ?></h3>
                                <div class="attendance-options mb-3">
                                    <div>
                                        <label for="decision_{{ $decision->id }}"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            <?= $langData['Decision'] ?>:
                                        </label>
                                        <textarea id="decision_{{ $decision->id }}"
                                            class="form-textarea mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            rows="2" disabled>{{ $decision->decision }}</textarea>
                                    </div>

                                    <div class="mt-3">
                                        <span><?= $langData['Action on decision'] ?>:</span>

                                        <label class=" text-sm font-medium text-gray-900 dark:text-white mt-1">
                                            <input type="radio" class="attenstatus" name="dess[{{ $decision->id }}]"
                                                value="3" required>
                                            <span class="ml-1"><?= $langData['Accepted'] ?></span>
                                        </label>

                                        <label class=" text-sm font-medium text-gray-900 dark:text-white">
                                            <input type="radio" class="attenstatus" name="dess[{{ $decision->id }}]"
                                                value="4" required>
                                            <span class="ml-1"><?= $langData['Rejected'] ?></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endforeach




                        <div class="modal-footer">
                            @unless ($decisionApproval == 1 || $decisionApproval == 2)
                                <button id="submitApproval" data-modal-target="approval-modal"
                                    style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                    class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                    type="submit" fdprocessedid="x0wj37" type="button">
                                    <?= $langData['Submit'] ?>
                                </button>
                            @endunless


                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- Modal for Signature -->
        <div id="signature-modal" class="modal-background hidden">
            <div class="modal-content">
                <!-- Modal header -->
                <div class="modal-header">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        {{ $langData['Apply Signature'] }}
                    </h3>
                    <button type="button" class="close-button" id="close">
                        <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="p-4 md:p-5 space-y-4">
                    <form id="signatureForm" action="{{ route('session-attendance.applySigniture') }}" method="POST">
                        @csrf
                        <input type="hidden" name="session_id" id="sessionId" value="{{ $recordId }}">
                        <label for="signatureStatus"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $langData['Signature Status'] }}</label>
                        <select name="signatureStatus" id="signatureStatus"
                            class="form-select mt-1 block w-full border" required>
                            <option disabled selected value="">{{ $langData['Select'] }}</option>
                            <option @if ($signitureApplyForAuthUser == 1) selected @endif value="1">
                                {{ $langData['Accepted'] }}</option>
                            <option @if ($signitureApplyForAuthUser == 2) selected @endif value="2">
                                {{ $langData['Rejected'] }}</option>
                        </select>

                        <div class="modal-footer">
                            <button id="submitSignature" type="submit"
                                style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
                                class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
                                type="button" fdprocessedid="x0wj37">
                                {{ $langData['Submit'] }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>




    </div>

    <div class="container">

        <!-- Header Section -->
        <div class="header" style="border: 1px solid;">

            <div class="top-section">
                <!-- Arabic Section -->
                <div class="arabic-section">
                    <div class="arabic">المملكة العربية السعودية</div>
                    <div class="arabic">وزارة التعليم</div>
                    <div class="arabic">جامعة القصيم</div>
                    <div class="arabic">{{ $facName }}</div>
                    <div class="arabic">{{ $depName }}</div>
                </div>

                <!-- Logo Section -->
                <div class="logo-section">
                    <img src="{{ URL::asset('assets/logo.png') }}" alt="College Logo">
                </div>

                <!-- English Section -->
                <div class="english-section">
                    <div class="english">Kingdom of Saudi Arabia</div>
                    <div class="english">Ministry of Education</div>
                    <div class="english">Qassim University</div>
                    <div class="english">{{ $facNameEn }}</div>
                    <div class="english">{{ $depNameEn }}</div>
                </div>
            </div>

            <div class="center-section" style="margin: 70px 0px">
                <div class="centered-text">
                    بسم الله الرحمن الرحيم
                </div>
            </div>

            <div class="center-section" style="margin: 70px 0px">
                <div class="centered-text bordered-div">
                    <div class="header-text">
                        {{-- مجلس  {{ $facName }} /  {{ $depName }} --}}
                        {{ $facName }} / مجلس {{ $depName }}
                    </div>
                    <span style="font-size: 18px">محضر</span>
                    <div class="session-info">
                        {{ $sessionOrder }} للعام الجامعى {{ $yearName }}<br>
                        المنعقدة يوم {{ $dayName }} {{ $higriDate }} هـ الموافق {{ $startDate }} م
                    </div>
                </div>
            </div>

            <div class="center-section" style="margin: 70px 0px">
                <div class="centered-text">
                    رسالة القسم
                </div>
                <div class="centered-text bordered-div">
                    <div class="session-info">
                        {{-- تعمل الكلية على إكساب خريجيها المعارف والمهارات الشخصية العلمية والمهنية التي تؤهلهم للالتحاق
                        بالدراسات العليا أو بسوق العمل بكفاءة، وتشجع الكلية البحث العلمي والنشر الدولي للمساهمة في
                        التقدم في العلوم الأساسية وتطبيقاتها، وتوظف نتائجه لخدمة المجتمع وحل مشكلاته. وتقوم الكلية بأداء
                        رسالتها في إطار من العدالة والمساواة يضمن عدم التمييز بين أعضاء هيئة التدريس والعاملين والطلاب. --}}
                        {!! $departmentMessage ?? 'ﻻتوجد رسالة لهذا القسم' !!}
                    </div>
                </div>
            </div>
        </div> <!-- End of Header Section -->

        <!-- Main Content Section -->
        <div class="main-content">

            <div class="top-section">
                <!-- Arabic Section -->
                <div class="arabic-section">
                    <div class="arabic">المملكة العربية السعودية</div>
                    <div class="arabic">وزارة التعليم</div>
                    <div class="arabic">جامعة القصيم</div>
                    <div class="arabic">{{ $facName }}</div>
                    <div class="arabic">{{ $depName }}</div>
                </div>

                <!-- Logo Section -->
                <div class="logo-section">
                    <img src="{{ URL::asset('assets/logo.png') }}" alt="College Logo">
                </div>

                <!-- English Section -->
                <div class="english-section">
                    <div class="english">Kingdom of Saudi Arabia</div>
                    <div class="english">Ministry of Education</div>
                    <div class="english">Qassim University</div>
                    <div class="english">{{ $facNameEn }}</div>
                    <div class="english">{{ $depNameEn }}</div>
                </div>
            </div>
            <h1>محضر {{ $sessionOrder }} لمجلس {{ $depName }} {{ $facName }}</h1>
            <p>المنعقدة يوم {{ $dayName }} {{ $higriDate }} هـ الموافق {{ $startDate }} م</p>
            <p>الحمد لله والصلاة والسلام على نبينا محمد وعلى آله وصحبه أجمعين أما بعد .</p>
            <p>فقد انعقد مجلس القسم برئاسة رئيس القسم {{ $DepHeadName }} في {{ $SessionPlace }} في تمام الساعة
                {{ $startTime }} وبعضوية كل من:</p>

            <h2 class="mt-3">أعضاء القسم</h2>
            @if ($members)
                <table>
                    <tr>
                        <th>الاسم</th>
                        <th>المنصب</th>
                        <th>الحضور</th>
                    </tr>
                    @foreach ($members as $member)
                        <tr>
                            <td>{{ $member['name'] }}</td>
                            <td>{{ $member['title'] }}</td>
                            <td>{{ $member['attendance'] }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif
            @if ($invitedMembers)
                <h2>أعضاء المجلس المدعوون</h2>
                <table>
                    <tr>
                        <th>الاسم</th>
                        <th>المنصب</th>
                        <th>الحضور</th>
                    </tr>
                    @foreach ($invitedMembers as $invitedMember)
                        <tr>
                            <td>{{ $invitedMember['name'] }}</td>
                            <td>{{ $invitedMember['title'] }}</td>
                            <td>{{ $invitedMember['attendance'] }}</td>
                        </tr>
                    @endforeach
                </table>
            @endif

            <h2>مناقشة جدول الأعمال</h2>
            <p>وتم استعراض جدول الأعمال ومناقشة ما ورد فيه واتخذ مجلس القسم القرارات والتوصيات وفق ما يلي :</p>

            <table class="table">
                <thead>
                    <tr>
                        <th>ترتيب الموضوع</th>
                        <th>عنوان الموضوع</th>
                    </tr>
                </thead>
                <tbody>
                    @php $i = 1; @endphp
                    @foreach ($decisions as $mainTopic => $supTopics)
                        <tr>
                            <td colspan="2" class="text-center"><strong>{{ $mainTopic }}</strong></td>
                        </tr>
                        @foreach ($supTopics['details'] as $topic)
                            <tr>
                                <td>{{ $this->arabicOrdinal($i) }}</td>
                                <td>{!! strip_tags($topic['topic_title']) !!}</td>
                            </tr>
                            @php $i++; @endphp
                        @endforeach
                    @endforeach
                </tbody>
            </table>

            <ol>
                @php $i = 1; @endphp
                @foreach ($decisions as $mainTopic => $supTopics)
                    <center>
                        <h3>{{ $mainTopic }}</h3>
                    </center>
                    @foreach ($supTopics['details'] as $topic)
                        <h3>الموضوع {{ $this->arabicOrdinal($i) }} : {!! strip_tags($topic['topic_title']) !!}</h3>

                        <li>
                            {!! $topic['report_contents'] !!}
                        </li>
                        @php $i++; @endphp
                    @endforeach
                @endforeach
            </ol>
        </div> <!-- End of Main Content Section -->
    </div>

    <!-- Include necessary scripts -->
    <script src="{{ URL::asset('assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ URL::asset('assets/form-builder/form-builder.min.js') }}"></script>
    <link rel="stylesheet" href="{{ URL::asset('assets/css/sweetalert2.min.css') }}">
    <script src="{{ URL::asset('assets/js/sweetalert2.min.js') }}"></script>

    <script></script>


    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script> --}}

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



        .container {
            font-family: 'Cairo', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            background-color: #fff;
            text-align: center;
            margin: 0px auto 0;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            font-weight: bold;
            width: 100%;
            /* max-width: 900px; */
        }

        h1,
        h2,
        h3 {
            color: #0056b3;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-weight: bold;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        ol {
            text-align: right;
            margin: 20px auto;
            padding: 0 20px;
            list-style: none;
        }

        ol li {
            margin-bottom: 10px;
        }

        .hidden {
            display: none;
        }

        .visible {
            display: flex;
        }

        .modal-background {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: 50;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 40rem;
            padding: 1rem;
            overflow-y: auto;
            max-height: calc(100% - 2rem);
        }

        .modal-header,
        .modal-footer {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-footer {
            border-top: 1px solid #e2e8f0;
        }

        .close-button {
            background-color: transparent;
            border: none;
            cursor: pointer;
        }

        .close-button svg {
            width: 1rem;
            height: 1rem;
        }

        .form-select {
            display: block;
            width: 100%;
            padding: 0.5rem 1rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-select:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .modal.hidden {
            display: none;
        }

        /* Show modal */
        .modal.visible {
            display: block;
            /* Optionally add styles for showing the modal */
        }





        @import url('https://fonts.googleapis.com/css2?family=Amiri&display=swap');
        /* Example Arabic font if Ruq'ah is unavailable */

        .top-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 1200px;
            /* Adjust based on your layout */
            padding: 20px;
        }

        .arabic-section,
        .english-section {
            text-align: right;
            /* Arabic section */
        }

        .english-section {
            text-align: left;
            /* English section */
        }

        .logo-section img {
            height: 100px;
            /* Adjust logo size */
            width: auto;
        }

        .centered-text {
            font-family: 'Cairo', sans-serif;
            /* Ruq'ah is not available on Google Fonts; Amiri is a classic Arabic style */
            font-size: 24px;
            /* Adjust font size as desired */
            font-weight: bold;
            text-align: center;
            color: #333;
            /* Adjust color as desired */
        }

        .bordered-div {
            border: 2px solid #000;
            /* 2px solid black border */
            padding: 20px;
            /* Space inside the div */
            margin: 20px 0;
            /* Space outside the div */
            width: fit-content;
            /* Adjust width based on content */
            text-align: center;
            /* Center the text */
            border-radius: 8px;
            /* Optional: rounded corners */
        }

        .header-text {
            font-size: 20px;
            /* Font size for the header text */
            font-weight: bold;
            margin-bottom: 10px;
            /* Space between header and session info */
        }

        .session-info {
            font-size: 16px;
            /* Font size for the session info */
            line-height: 1.5;
        }

        .container {
            display: flex;
            flex-direction: column;
        }

        .header {
            margin-bottom: 20px;
            border: 1px solid #000;
            padding: 20px;
            width: 100%;
        }

        .center-section {
            margin-bottom: 15px;
            text-align: center;
        }

        .bordered-div {
            border: 2px solid #000;
            padding: 15px;
            margin: auto;
            text-align: center;
            border-radius: 8px;
            width: fit-content;
        }

        .main-content {
            padding: 20px;
            margin: 0 20px;
        }
    </style>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const openButton = document.getElementById('open');
            const closeButton = document.getElementById('close');
            const modal = document.getElementById('approval-modal');

            openButton.addEventListener('click', function() {
                modal.classList.remove('hidden');
                modal.classList.add('visible');
            });

            closeButton.addEventListener('click', function() {
                modal.classList.remove('visible');
                modal.classList.add('hidden');
            });

            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.classList.remove('visible');
                    modal.classList.add('hidden');
                }
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const openButton = document.getElementById('apply-signature-button');
            const closeButton = document.getElementById('close');
            const modal = document.getElementById('signature-modal');

            // Open the modal when the "Apply Signature" button is clicked
            openButton.addEventListener('click', function() {
                modal.classList.remove('hidden');
                modal.classList.add('visible');
            });

            // Close the modal when the close button is clicked
            closeButton.addEventListener('click', function() {
                modal.classList.remove('visible');
                modal.classList.add('hidden');
            });

            // Close the modal when clicking outside the modal content
            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.classList.remove('visible');
                    modal.classList.add('hidden');
                }
            });
        });

        // Function to toggle the visibility of the reject reason section
        function toggleRejectReason() {
            let selectedValue = $('#approvalDecision').val();
            let rejectReasonWrapper = $("#rejectReasonWrapper");

            if (selectedValue === "2" || selectedValue === "") {
                // Show the reject reason wrapper if "Rejected" is selected or the default option is selected
                rejectReasonWrapper.removeClass("hidden");
            } else {
                // Hide the reject reason wrapper for all other options
                rejectReasonWrapper.addClass("hidden");
            }
        }

        // Initial check on page load
        toggleRejectReason();

        // Bind the change event to the approvalDecision select box
        $('#approvalDecision').on('change', function() {
            toggleRejectReason();
        });

        // save approval
        $("#decisionApprovalForm").on('submit', function(e) {
            e.preventDefault();

            // Get CSRF token from the page's meta tag
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            // Prepare form data including dess values
            var formData = {
                rejectReason: $("#rejectReason").val(),
                approval: $("#approvalDecision").val(),
                session_id: $("#sessionId").val(),
                _token: csrfToken // Include CSRF token
            };

            // Add dess values to formData
            $("input[type=radio].attenstatus:checked").each(function() {
                var name = $(this).attr('name');
                formData[name] = $(this).val();
            });

            var $langData = {
                'Success': `<?= $langData['Success'] ?>`,
                'saving data': `<?= $langData['saving data'] ?>`,
                'ok': `<?= $langData['ok'] ?>`,
                'Add another': `<?= $langData['Add another'] ?>`,
            };

            // Make a POST request with data
            $.ajax({
                url: "{{ route('session-decision-approval', $recordId) }}",
                method: 'POST',
                data: formData,
                success: function(response) {
                    $('#close').click();
                    Swal.fire({
                        title: $langData['Success'],
                        text: $langData['saving data'],
                        icon: 'success',
                        confirmButtonText: $langData['ok'],
                        confirmButtonColor: '#3085d6',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = response.redirect_url;
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error(error);
                    var errorMessage = xhr.responseJSON.error ||
                        'An error occurred while submitting the form.';

                    Swal.fire({
                        title: 'Failed!',
                        html: errorMessage, // Display the error message from the backend
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33'
                    });
                }
            });
        });


        $(document).ready(function() {
            // Handle the submit button click (Form submission)
            $('#submitSignature').on('click', function(e) {
                // Prevent default form submission
                e.preventDefault();

                // Get the selected value from the dropdown
                var signatureStatus = $('#signatureStatus').val();
                if (signatureStatus === "") {
                    Swal.fire({
                        title: "{{ $langData['Error'] }}",
                        text: "{{ $langData['Please select an option.'] }}",
                        icon: 'warning',
                        confirmButtonText: "{{ $langData['ok'] }}"
                    });
                    return;
                }

                var sessionId = $('#sessionId').val();

                // Perform the AJAX request to update the session attendance
                $.ajax({
                    url: '{{ route('session-attendance.applySigniture') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        session_id: sessionId,
                        apply_signature: signatureStatus // Send selected value
                    },
                    success: function(response) {
                        $('#signature-modal').removeClass('visible').addClass(
                            'hidden'); // Close modal on success

                        if (response.success) {
                            Swal.fire({
                                title: "{{ $langData['Success'] }}",
                                text: "{{ $langData['Saving data'] }}",
                                icon: 'success',
                                confirmButtonText: "{{ $langData['ok'] }}",
                                confirmButtonColor: '#3085d6',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location
                                        .reload(); // Reload the page to reflect changes
                                }
                            });
                        } else {
                            Swal.fire({
                                title: "{{ $langData['Error'] }}",
                                text: "{{ $langData['An error occurred while saving.'] }}",
                                icon: 'error',
                                confirmButtonText: "{{ $langData['ok'] }}",
                                confirmButtonColor: '#d33',
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        Swal.fire({
                            title: "{{ $langData['Error'] }}",
                            text: "{{ $langData['An error occurred while saving.'] }}",
                            icon: 'error',
                            confirmButtonText: "{{ $langData['ok'] }}",
                            confirmButtonColor: '#d33',
                        });
                    }
                });
            });
        });
    </script>

</x-filament::page>
