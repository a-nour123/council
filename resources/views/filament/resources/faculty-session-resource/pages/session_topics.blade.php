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

        <table class="table">
            <thead>
                <tr>
                    <th>ترتيب الموضوع</th>
                    <th>عنوان الموضوع</th>
                </tr>
            </thead>
            <tbody>
                @php $i = 1; @endphp
                @foreach ($topicsWithoutDecision as $mainTopic => $supTopics)
                    <tr>
                        <td colspan="2" class="text-center"><strong>{{ $mainTopic }}</strong></td>
                    </tr>
                    @foreach ($supTopics as $topic)
                        <tr>
                            <td>{{ $this->arabicOrdinal($i) }}</td>
                            <td>{!! strip_tags($topic) !!}</td>
                        </tr>
                        @php $i++; @endphp
                    @endforeach
                @endforeach
            </tbody>
        </table>

        {{-- <ol>
            @php $i = 1; @endphp
            @foreach ($topicsWithoutDecision as $mainTopic => $supTopics)
                <center>
                    <h3>{{ $mainTopic }}</h3>
                </center>
                @foreach ($supTopics as $topic)
                    <h3>الموضوع {{ $this->arabicOrdinal($i) }} : <span style="color: black"> {!! strip_tags($topic) !!}
                        </span>
                    </h3>

                    @php $i++; @endphp
                @endforeach
            @endforeach
        </ol> --}}
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
    </style>


</x-filament::page>
