<!doctype html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>خطاب التغطية</title>

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
        $department = Department::findOrFail($data['session']->department_id);
        $faculty = Faculty::where('id', $department->faculty_id)->first();

        // Assign the parts to variables
        $yearCode = $parts[0]; // Before the first "_"
        $departmentCode = $parts[1]; // Between the first and second "_"
        $lastPart = $parts[2]; // After the second "_"

        $yearName = YearlyCalendar::where('code', $yearCode)->value('name');
        $departmentArName = Department::where('code', $departmentCode)->value('ar_name');

        $newSessionCode = "{$yearName}_{$departmentArName}_{$lastPart}";
    @endphp
    <div class="container">
        <!-- Main Content Starts Here -->
        <div class="main-content">
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
                            <div class="arabic-text">{{ $department->ar_name }}</div>
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
                            <div class="english-text">{{ $department->en_name }}</div>
                        </td>
                    </tr>
                </table>
            </div>

            <ol>
                @foreach ($data['topics'] as $mainTopic => $supTopics)
                    @foreach ($supTopics['details'] as $topic)
                        <li style="margin-top: 16px;">
                            {!! $topic['report_contents'] !!}
                        </li>
                    @endforeach
                @endforeach
            </ol>

        </div>
    </div>
</body>

</html>
