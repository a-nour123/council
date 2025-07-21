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


    <div class="">
        <button id="pdf" style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
            class=" self-start fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
            type="button" fdprocessedid="x0wj37">
            <?= $langData['Print Report'] ?>
        </button>
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
                        {{-- مجلس {{ $facName }} / {{ $depName }} --}}
                        مجلس / {{ $facName }}
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
                    رسالة الكلية
                </div>
                <div class="centered-text bordered-div">
                    <div class="session-info">
                        {{-- تعمل الكلية على إكساب خريجيها المعارف والمهارات الشخصية العلمية والمهنية التي تؤهلهم للالتحاق
                        بالدراسات العليا أو بسوق العمل بكفاءة، وتشجع الكلية البحث العلمي والنشر الدولي للمساهمة في
                        التقدم في العلوم الأساسية وتطبيقاتها، وتوظف نتائجه لخدمة المجتمع وحل مشكلاته. وتقوم الكلية بأداء
                        رسالتها في إطار من العدالة والمساواة يضمن عدم التمييز بين أعضاء هيئة التدريس والعاملين والطلاب. --}}
                        {!! $facultyMessage ?? 'ﻻتوجد رسالة لهذه الكلية' !!}
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
                </div>
            </div>


            <h1>محضر {{ $sessionOrder }} لمجلس {{ $facName }}</h1>
            {{-- <h1>
                @if ($this->decisionApproval == 1)
                    (تم اعتماد المحضر)
                @else
                    (لم يتم اعتماد المحضر)
                @endif
            </h1> --}}

            <p>المنعقدة يوم {{ $dayName }} {{ $higriDate }} هـ الموافق {{ $startDate }} م</p>
            <p>الحمد لله والصلاة والسلام على نبينا محمد وعلى آله وصحبه أجمعين أما بعد .</p>
            <p>فقد انعقد مجلس الكلية برئاسة عميد الكلية {{ $facDeanName }} في {{ $SessionPlace }} في تمام الساعة
                {{ $startTime }} وبعضوية كل من:</p>

            <h2 class="mt-3">أعضاء الكلية</h2>
            @if ($members)
                <table>
                    <tr>
                        <th>الاسم</th>
                        <th>المنصب</th>
                        <th>الحضور</th>
                        {{-- <th>التوقيع</th> --}}
                    </tr>
                    @foreach ($members as $member)
                        <tr>
                            <td>{{ $member['name'] }}</td>
                            <td>{{ $member['title'] }}</td>
                            <td>{{ $member['attendance'] }}</td>
                            {{-- <td class="signature-cell" style="text-align: center;">
                                @if ($member['signature'] === 'غائب')
                                    {{ $member['signature'] }}
                                @elseif ($member['signature'] === 'رفض المستخدم التوقيع')
                                    {{ $member['signature'] }}
                                @elseif(empty($member['signature']))
                                    لا يوجد توقيع للعضو
                                @else
                                    <img src="{{ asset('storage/' . $member['signature']) }}" alt="Signature"
                                        style="width: 100px;margin: auto; max-width: 100%; height: auto;">
                                @endif
                            </td> --}}

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
                        <th>التوقيع</th>
                    </tr>
                    @foreach ($invitedMembers as $invitedMember)
                        <tr>
                            <td>{{ $invitedMember['name'] }}</td>
                            <td>{{ $invitedMember['title'] }}</td>
                            <td>{{ $invitedMember['attendance'] }}</td>
                            <td class="signature-cell" style="text-align: center;">
                                @if ($invitedMember['signature'] === 'غائب')
                                    {{ $invitedMember['signature'] }}
                                @elseif(empty($invitedMember['signature']))
                                    لا يوجد توقيع
                                @else
                                    <img src="{{ asset('storage/' . $member['signature']) }}" alt="Signature">
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
            @endif

            {{-- <h2>جدول الأعمال</h2> --}}
            {{-- <ol>
            @php $i = 1; @endphp
            @foreach ($topics as $topicTitle)
                <li>الموضوع {{ $this->arabicOrdinal($i) }}: {{ $topicTitle }}</li>
                @php $i++; @endphp
            @endforeach
        </ol> --}}

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

            @if ($decisionApproval == 1)
                <h1 style="color: green">
                    تم اعتماد المحضر بتاريخ: {{ $endDateTime }} معد المحضر: {{ $createdBy }}
                </h1>
                <div class="signature-cell" style="text-align: center;">
                    <img src="{{ asset('storage/' . $createdBySignature) }}" alt="توقيع امين المجلس"
                        style="margin: auto;text-align: center;height: auto;max-width: 10%;">
                </div>
            @else
                <h1 style="color: red">
                    تم رفض اعتماد المحضر بتاريخ: {{ $endDateTime }} معد المحضر: {{ $createdBy }}
                </h1>
                <div class="signature-cell" style="text-align: center;">
                    <img src="{{ asset('storage/' . $createdBySignature) }}" alt="توقيع امين المجلس"
                        style="margin: auto;text-align: center;height: auto;max-width: 10%;">
                </div>
            @endif
        </div>
        {{-- <button class="no-print" data-modal-target="approval-modal"
    style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600);"
    class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-btn-action"
    type="button" fdprocessedid="x0wj37" type="button" onclick="printContainer()">Print</button> --}}
    </div>
    <div class="container">
        <div class="header">

            <div class="top-section">
                <!-- Arabic Section -->
                <div class="arabic-section">
                    <div class="arabic">المملكة العربية السعودية</div>
                    <div class="arabic">وزارة التعليم</div>
                    <div class="arabic">جامعة القصيم</div>
                    <div class="arabic">{{ $facName }}</div>
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
                </div>
            </div>

        </div> <!-- End of Header Section -->
        <h1>محضر {{ $sessionOrder }} لمجلس {{ $facName }}</h1>

        {{-- <h1>
            @if ($this->decisionApproval == 1)
                (تم اعتماد المحضر)
            @else
                (لم يتم اعتماد المحضر)
            @endif
        </h1> --}}

        <p>المنعقدة يوم {{ $dayName }} {{ $higriDate }} هـ الموافق {{ $startDate }} م</p>
        {{-- <p>الحمد لله والصلاة والسلام على نبينا محمد وعلى آله وصحبه أجمعين أما بعد .</p>
        <p>فقد انعقد مجلس القسم برئاسة رئيس القسم {{ $facDeanName }} في {{ $SessionPlace }} في تمام الساعة
            {{ $startTime }} وبعضوية كل من:</p> --}}

        <h2 class="mt-3">أعضاء الكلية</h2>
        @if ($members)
            <table>
                <tr>
                    <th>الاسم</th>
                    <th>المنصب</th>
                    <th>الحضور</th>
                    <th>التوقيع</th>
                </tr>
                @foreach ($members as $member)
                    <tr>
                        <td>{{ $member['name'] }}</td>
                        <td>{{ $member['title'] }}</td>
                        <td>{{ $member['attendance'] }}</td>
                        <td class="signature-cell" style="text-align: center;">
                            @if ($member['signature'] === 'غائب')
                                {{ $member['signature'] }}
                            @elseif ($member['signature'] === 'رفض المستخدم التوقيع')
                                {{ $member['signature'] }}
                            @elseif(empty($member['signature']))
                                لا يوجد توقيع للعضو
                            @else
                                <img src="{{ asset('storage/' . $member['signature']) }}" alt="Signature"
                                    style="width: 100px;margin: auto; max-width: 100%; height: auto;">
                            @endif
                        </td>
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
                    <th>التوقيع</th>
                </tr>
                @foreach ($invitedMembers as $invitedMember)
                    <tr>
                        <td>{{ $invitedMember['name'] }}</td>
                        <td>{{ $invitedMember['title'] }}</td>
                        <td>{{ $invitedMember['attendance'] }}</td>
                        <td class="signature-cell" style="text-align: center;">
                            @if ($invitedMember['signature'] === 'غائب')
                                {{ $invitedMember['signature'] }}
                            @elseif(empty($invitedMember['signature']))
                                لا يوجد توقيع
                            @else
                                <img src="{{ asset('storage/' . $member['signature']) }}" alt="Signature"
                                    style="width: 100px;margin: auto; max-width: 100%; height: auto;">
                            @endif
                        </td>

                    </tr>
                @endforeach
            </table>
        @endif
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
        //  function printContainer() {
        //     window.print();
        // }
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
        // save approval
        $("#decisionApprovalForm").on('submit', function(e) {
            e.preventDefault();

            // Get CSRF token from the page's meta tag
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            // Prepare form data
            var formData = {
                approval: $("#approvalDecision").val(),
                session_id: $("#sessionId").val(),
                _token: csrfToken // Include CSRF token
            };

            // Make a POST request with data
            $.ajax({
                url: "{{ route('session-decision-approval', $recordId) }}",
                method: 'POST',
                data: formData,
                success: function(response) {
                    // Handle successful response
                    console.log('Response:', response);
                    $('#close').click();
                    Swal.fire({
                        title: 'Success!',
                        text: 'Decision approval has been saved successfully.',
                        icon: 'success',
                        // showCancelButton: true,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#3085d6',
                        // cancelButtonColor: '#d33'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.history.back();
                            // location.back();

                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error(error);
                    var errorMessage = xhr.responseJSON.message ||
                        'An error occurred while submitting the form you should select the descion.';

                    // Check if there are validation errors
                    if (xhr.responseJSON.errors && xhr.responseJSON.errors[Object.keys(xhr
                            .responseJSON.errors)[0]]) {

                    }

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


        $("#pdf").click(function(e) {
            e.preventDefault();
            var sessionId = "{{ $recordId }}"; // Ensure session ID is correctly passed

            $.ajax({
                type: "GET",
                url: "{{ route('facultyDownloadPDF', ['recordId' => $recordId, 'content' => 'report']) }}",
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    // Create a new Blob object for the PDF
                    var blob = new Blob([response], {
                        type: "application/pdf"
                    });
                    var url = window.URL.createObjectURL(blob);

                    // Open the PDF in a new tab
                    window.open(url);
                },
                error: function(error) {
                    console.error("Error fetching PDF", error);
                }
            });
        });
    </script>

</x-filament::page>
