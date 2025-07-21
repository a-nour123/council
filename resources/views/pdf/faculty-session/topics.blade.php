<!doctype html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>جدول اعمال جلسة القسم</title>

    <!-- Linking to Google Fonts for Cairo Font -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo&display=swap" rel="stylesheet">
    <style>
        /* General Layout */
        * {
            box-sizing: border-box;
            font-family: 'Cairo', sans-serif;
            margin: 0;
            padding: 0;
        }

        .cover-wrapper {
            max-width: 900px;
            padding: 20px;
            border: 2px solid #000;
            background-color: #fff;
            direction: rtl;
            /* Set text direction to right-to-left */

        }

        .cover-wrapper-footer {
            page-break-inside: avoid;
            /* Prevent splitting inside */
            page-break-before: always;
            max-height: 100vh;
            /* Prevent overflow beyond one page */
            max-width: 900px;
            padding: 20px;
            border: 2px solid #000;
            background-color: #fff;
            direction: rtl;
            /* Set text direction to right-to-left */
        }


        .main-content {
            max-width: 900px;
            margin: 0 auto;
            /* Center the content horizontally */
            padding: 20px;
            background-color: #fff;
            direction: rtl;
            /* Set text direction to right-to-left */

        }

        /* Apply RTL direction and right text alignment to main content */
        .main-content {
            margin-top: 30px;
            /* Space after the cover page */
            text-align: right;
            /* Align text to the right */
            direction: rtl;
            /* Set text direction to right-to-left */
        }

        /* Specific style for the cover page (keep it as is) */
        .cover-wrapper {
            /* Cover page takes full page height */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            /* Space out the elements vertically */
        }

        .cover-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding: 1px 54px;
        }

        .arabic-section,
        .english-section {
            flex: 1;
            text-align: center;
        }

        .college-logo {
            height: 120px;
            margin: 0 30px;
        }

        /* Arabic & English Text Styling */
        .arabic-text,
        .english-text {
            font-size: 13px;
            font-weight: bold;
            margin: 0px;
            line-height: 20px;
        }

        .arabic-text {
            color: black;
            /* Dark Green for Arabic */
        }

        .english-text {
            color: black;
            /* Navy for English */
        }

        /* Centered Text Section */
        .centered-section {
            text-align: center;
        }

        .centered-text {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
            color: black;
            /* Dark Slate Gray */
        }

        .session-details {
            font-size: 16px;
            color: black;
            /* Indigo */
            margin-top: 20px;
        }

        .session-header {
            font-weight: bold;
            font-size: 18px;
            margin: 10px 0;
        }

        .session-info {
            margin-top: 10px;
            line-height: 1.6;
        }

        /* College Message Section */
        .college-message {
            text-align: center;
            margin: 30px 0;
        }

        .message-header {
            font-size: 20px;
            font-weight: bold;
            color: black;
            /* Saddle Brown */
        }

        .message-body {
            font-size: 18px;
            line-height: 1.8;
            color: #c00505;

        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th,
        table td {
            padding: 8px;
            text-align: center;
            border: 1px solid #000;
        }

        h3 {
            margin-top: 20px;
        }

        .text-primary {
            color: black;
        }

        .text-success {
            color: black;
        }

        /* Ensure no page break within content */
        .page-break {
            page-break-before: always;
        }

        /* Table layout */
        .simple-table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            font-size: 16px;
            text-align: center;
            margin-top: 20px;
        }

        /* Header style */
        .simple-table thead th {
            background-color: #f4f4f4;
            border: 1px solid #ddd;
            padding: 10px;
            font-weight: bold;
        }

        /* Body cells style */
        .simple-table tbody td {
            padding: 8px;
            border: 1px solid #ddd;

            color: black;
        }

        /* Alternating row background color */
        .simple-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        ol {
            list-style-type: none;
            padding-left: 0;
            /* Optional: to remove default padding */
            margin-left: 0;
            /* Optional: to remove default margin */
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
    </style>

</head>

<body>
    @php
        use Alkoumi\LaravelHijriDate\Hijri;
        use Carbon\Carbon;
        use App\Models\Department;
        use App\Models\YearlyCalendar;
        use App\Models\Faculty;

        $endDateTime = Carbon::parse($data['session']->actual_end_time);
        $startDateTime = Carbon::parse($data['session']->start_time);

        $hijriEndDateTime = Hijri::DateIndicDigits('Y/m/d', $endDateTime->format('Y/m/d'));
        $higriDate = Hijri::DateIndicDigits('Y/m/d', $startDateTime->format('Y/m/d'));
        $dayName = Hijri::DateIndicDigits('l', $startDateTime->format('l'));
        $startTime = $startDateTime->format('g:i'); // 12-hour format with minutes
        $startDate = $startDateTime->format('Y/m/d'); // Split the code into parts using "_"
        $parts = explode('_', $data['session']->code);
        // $department = Department::findOrFail($data['session']->department_id);
        $faculty = Faculty::where('id', $data['session']->faculty_id)->first();
        // dd(['$department' => $department['ar_name'], '$faculty' => $faculty['ar_name']]);
        $facultyMessage = $faculty->message;

        // Assign the parts to variables
        $yearCode = $parts[0]; // Before the first "_"
        $facultyCode = $parts[1]; // Between the first and second "_"
        $lastPart = $parts[2]; // After the second "_"

        $yearName = YearlyCalendar::where('code', $yearCode)->value('name');
        $facultyArName = Faculty::where('code', $facultyCode)->value('ar_name');

        $newSessionCode = "{$yearName}_{$facultyArName}_{$lastPart}";
    @endphp
    <div class="container">
        <!-- Cover Page -->
        <div class="cover-wrapper" style="margin:100px auto">
            <!-- Header Section -->
            <div class="cover-header" style="margin-bottom:100px">
                <!-- Hidden Table Section -->
                <table class="hidden-table" style="border-collapse: collapse; width: 100%; border: none;">
                    <tr>
                        <!-- Arabic Section (Left Side) -->
                        <td class="arabic-section" style="border: none;">
                            <div class="arabic-text">المملكة العربية السعودية</div>
                            <div class="arabic-text">وزارة التعليم</div>
                            <div class="arabic-text">جامعة القصيم</div>
                            <div class="arabic-text">{{ $faculty->ar_name }}</div>
                        </td>

                        <!-- Logo Section (Center) -->
                        <td class="logo-section" style="border: none;">
                            <img src="{{ URL::asset('assets/logo.png') }}" alt="College Logo" class="college-logo">
                        </td>

                        <!-- English Section (Right Side) -->
                        <td class="english-section" style="border: none;">
                            <div class="english-text">Kingdom of Saudi Arabia</div>
                            <div class="english-text">Ministry of Education</div>
                            <div class="english-text">Qassim University</div>
                            <div class="english-text">{{ $faculty->en_name }}</div>
                        </td>
                    </tr>
                </table>
            </div>



            <!-- Centered Text Section -->
            <div class="centered-section" style="margin:20px auto 0px">
                <div class="centered-text">
                    بسم الله الرحمن الرحيم
                </div>

                <div class="session-details bordered-div" style="margin-top: 80px">
                    <div class="session-header">
                        {{-- مجلس {{ $data['session']->department->faculty->ar_name }} /
                        {{ $data['session']->department->ar_name }} --}}
                        مجلس / {{ $faculty->ar_name }}
                    </div>
                    <span style="font-size: 18px">محضر</span>
                    <div class="session-info">
                        {{ $data['sessionOrder'] }} للعام الجامعى {{ $yearName }}<br>
                        المنعقدة يوم {{ $dayName }} {{ $higriDate }} هـ الموافق {{ $startDate }} م
                    </div>
                </div>
            </div>

            <!-- College Message Section -->
            <div class="college-message" style="margin:100px auto 100px">
                <div class="message-header">رسالة الكلية</div>
                <div class="message-body bordered-div" style="margin-bottom: 300px; color: #000;">
                    {{-- تعمل الكلية على إكساب خريجيها المعارف والمهارات الشخصية العلمية والمهنية التي تؤهلهم للالتحاق
                    بالدراسات العليا أو بسوق العمل بكفاءة، وتشجع الكلية البحث العلمي والنشر الدولي للمساهمة في
                    التقدم في العلوم الأساسية وتطبيقاتها، وتوظف نتائجه لخدمة المجتمع وحل مشكلاته. وتقوم الكلية بأداء
                    رسالتها في إطار من العدالة والمساواة يضمن عدم التمييز بين أعضاء هيئة التدريس والعاملين والطلاب. --}}
                    {!! $facultyMessage ?? 'ﻻتوجد رسالة لهذه الكلية' !!}
                </div>
            </div>
        </div>

        <!-- Main Content Starts Here -->
        <div class="main-content" style="margin-bottom:10px ">
            <!-- Header Section -->
            <div class="cover-header">
                <!-- Hidden Table Section -->
                <table class="hidden-table" style="border-collapse: collapse; width: 100%; border: none;">
                    <tr>
                        <!-- Arabic Section (Left Side) -->
                        <td class="arabic-section" style="border: none;">
                            <div class="arabic-text">المملكة العربية السعودية</div>
                            <div class="arabic-text">وزارة التعليم</div>
                            <div class="arabic-text">جامعة القصيم</div>
                            <div class="arabic-text">{{ $faculty->ar_name }}</div>
                        </td>

                        <!-- Logo Section (Center) -->
                        <td class="logo-section" style="border: none;">
                            <img src="{{ URL::asset('assets/logo.png') }}" alt="College Logo" class="college-logo">
                        </td>

                        <!-- English Section (Right Side) -->
                        <td class="english-section" style="border: none;">
                            <div class="english-text">Kingdom of Saudi Arabia</div>
                            <div class="english-text">Ministry of Education</div>
                            <div class="english-text">Qassim University</div>
                            <div class="english-text">{{ $faculty->en_name }}</div>
                        </td>
                    </tr>
                </table>
            </div>

            <h2 style="text-align: center;margin-bottom:9px;margin-top: 16px;">مناقشة جدول الأعمال</h2>
            <p style="text-align: center;">وتم استعراض جدول الأعمال ومناقشة ما ورد فيه واتخذ مجلس القسم القرارات
                والتوصيات وفق ما يلي :</p>

            <table class="simple-table">
                <thead>
                    <tr>
                        <th>ترتيب الموضوع</th>
                        <th>عنوان الموضوع</th>
                    </tr>
                </thead>
                <tbody>
                    @php $i = 1; @endphp
                    @foreach ($data['topics'] as $mainTopic => $supTopics)
                        <tr>
                            <td colspan="2" class="text-center"><strong>{{ $mainTopic }}</strong></td>
                        </tr>
                        @foreach ($supTopics['details'] as $topic)
                            <tr>
                                <td>{{ $data['arabicOrder'][$i] }}</td>
                                <td>{!! strip_tags($topic['topic_title']) !!}</td>
                            </tr>
                            @php $i++; @endphp
                        @endforeach
                    @endforeach
                </tbody>
            </table>

            @php $i = 1; @endphp
            @foreach ($data['topics'] as $mainTopic => $supTopics)
                @foreach ($supTopics['details'] as $topic)
                    <div class="topic-page" style="page-break-before: always;"> <!-- Page break for each subtopic -->
                        <!-- Header Cover for Each Subtopic -->
                        <div class="cover-header" style="margin-bottom:20px; ">
                            <!-- Hidden Table Section -->
                            <table class="hidden-table" style="border-collapse: collapse; width: 100%; border: none;">
                                <tr>
                                    <!-- Arabic Section (Left Side) -->
                                    <td class="arabic-section" style="border: none;">
                                        <div class="arabic-text">المملكة العربية السعودية</div>
                                        <div class="arabic-text">وزارة التعليم</div>
                                        <div class="arabic-text">جامعة القصيم</div>
                                        <div class="arabic-text">{{ $faculty->ar_name }}</div>
                                    </td>

                                    <!-- Logo Section (Center) -->
                                    <td class="logo-section" style="border: none;">
                                        <img src="{{ URL::asset('assets/logo.png') }}" alt="College Logo"
                                            class="college-logo">
                                    </td>

                                    <!-- English Section (Right Side) -->
                                    <td class="english-section" style="border: none;">
                                        <div class="english-text">Kingdom of Saudi Arabia</div>
                                        <div class="english-text">Ministry of Education</div>
                                        <div class="english-text">Qassim University</div>
                                        <div class="english-text">{{ $faculty->en_name }}</div>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Main Topic Title -->
                        <center>
                            <div
                                style="background-color: #f5f5f5; border-bottom: 1px solid; padding: 10px; border-radius: 5px; position: relative; text-align: center;">
                                <h3 style="margin: 0; font-weight: bold; position: relative; z-index: 1;">
                                    {{ $mainTopic }}
                                </h3>
                            </div>
                        </center>

                        <!-- Subtopic Title -->
                        <h3 style="margin-top:24px ">الموضوع {{ $data['arabicOrder'][$i] }} : {!! strip_tags($topic['topic_title']) !!}
                        </h3>

                        <!-- Subtopic Content -->
                        <ol>
                            <li style="margin-top: 16px;">
                                {!! $topic['report_contents'] !!}
                            </li>
                        </ol>
                        <hr style="border: none; border-top: 2px solid #ccc; width: 100%; margin: 15px auto;">

                        @php $i++; @endphp
                    </div>
                @endforeach
            @endforeach



            <h3 style="text-align: center;" class="text-success mt-4">تم اعتماد المحضر
                بتاريخ:<span>{{ $hijriEndDateTime }}</span>
            </h3>
            <h3 style="text-align: center;" class="text-success mt-4">
                معد المحضر:
                {{ $data['session']->createdBy->ar_name }}
            </h3>

        </div>
    </div>
</body>

</html>
