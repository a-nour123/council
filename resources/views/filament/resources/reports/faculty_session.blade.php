<x-filament::page>
    <?php
    $langData = [];
    $locale = app()->getLocale();
    $langFile = $locale == 'ar' ? __DIR__ . '/../../../lang/ar.json' : __DIR__ . '/../../../lang/en.json';

    if (file_exists($langFile)) {
        $langData = json_decode(file_get_contents($langFile), true);
    } else {
        echo "Language file for '$locale' not found!";
    }
    ?>

    <div class="p-6">
        <div class="flex space-x-4 items-center">
            <!-- Acadimic year Select -->
            <div class="w-1/2">
                <label for="year-select"
                    class="block text-sm font-medium text-gray-700"><?= $langData['Academic year'] ?></label>
                <select id="year-select"
                    class="select2 mt-1 block w-full pl-3 pr-10 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value=""><?= $langData['Select academic year'] ?></option>
                    @foreach ($years as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Faculty Select -->
            <div class="w-1/2">
                <label for="faculty-select"
                    class="block text-sm font-medium text-gray-700"><?= $langData['Faculty'] ?></label>
                <select id="faculty-select"
                    class="select2 mt-1 block w-full pl-3 pr-10 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value=""><?= $langData['Select faculty'] ?></option>
                </select>
            </div>
        </div>

        <div class="mt-6">
            <div id="session-overview-chart" style="width:100%; height:400px;"></div>
        </div>

        <div class="mt-6">
            <div id="session-details-chart" style="width:100%; height:400px;"></div>
        </div>

    </div>




    <script src="{{ URL::asset('assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ URL::asset('assets/form-builder/form-builder.min.js') }}"></script>
    <link rel="stylesheet" href="{{ URL::asset('assets/css/sweetalert2.min.css') }}">
    <script src="{{ URL::asset('assets/js/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/quill.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/flowbite.min.js') }}"></script>
    <link rel="stylesheet" href="{{ URL::asset('assets/css/quill.snow.css') }}">
    <script src="{{ URL::asset('assets/js/highcharts.js') }}"></script>
    <script src="{{ URL::asset('assets/js/exporting.js') }}"></script>
    <script src="{{ URL::asset('assets/js/data.js') }}"></script>
    <script src="{{ URL::asset('assets/js/accessibility.js') }}"></script>
    <link rel="stylesheet" href="{{ URL::asset('assets/css/select2.min.css') }}">
    <script src="{{ URL::asset('assets/js/select2.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            const $langData = {
                'Success': `<?= $langData['Success'] ?>`,
                'saving data': `<?= $langData['saving data'] ?>`,
                'ok': `<?= $langData['ok'] ?>`,
                'Select': `<?= $langData['Select'] ?>`,
                'Add another': `<?= $langData['Add another'] ?>`,
                'Select faculty': `<?= $langData['Select faculty'] ?>`,
                'Select faculty': `<?= $langData['Select faculty'] ?>`,
                'Select academic year': `<?= $langData['Select academic year'] ?>`,
            };

            $('.select2').select2({
                // placeholder: $langData['Select'], // Optional: Placeholder text
                // allowClear: true, // Enable the 'x' clear icon
                width: '100%', // Full-width dropdown
            });

            let sessionOverviewChart; // Reference for the Chart.js instance
            let sessionDetailsChart; // Reference for the Chart.js instance
            let facultyData = []; // Cache for faculty data from the backend

            // Fetch facultys and their sessions
            $('#year-select').change(function() {
                const selectedYearId = $('#year-select').val(); // Selected year ID
                const $facultySelect = $('#faculty-select'); // faculty dropdown

                // Reset the facultys dropdown
                $facultySelect.empty().append(
                    `<option value="">` + $langData['Select faculty'] + `</option>`
                );

                if (selectedYearId) {
                    $.ajax({
                        url: `{{ route('reports.sessions.faculty') }}`,
                        type: 'GET',
                        data: {
                            yearId: selectedYearId,
                        },
                        success: function(data) {
                            facultyData = data; // Cache the faculty data
                            console.log(data);

                            // Populate the faculty dropdown
                            $.each(data, function(index, faculty) {
                                $facultySelect.append(
                                    `<option value="${faculty.id}">${faculty.name}</option>`
                                );
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching facultys:', error);
                        },
                    });
                }
            });

            // Handle faculty selection to update the chart
            $('#faculty-select').change(function() {
                const selectedfacultyId = $(this).val(); // Selected faculty ID

                if (selectedfacultyId) {
                    // Find the selected faculty's data
                    const selectedfaculty = facultyData.find(
                        (fac) => fac.id == selectedfacultyId
                    );

                    if (selectedfaculty) {
                        // Update the chart with the selected faculty's data
                        generalChart(selectedfaculty);
                        detailsChart(selectedfaculty);
                    }
                }
            });

            function generalChart(faculty) {
                console.log(faculty);

                // Prepare the data for the chart
                const chartData = {
                    chart: {
                        type: 'bar', // Change chart type to 'bar' for horizontal bars
                        height: '400', // Optional: Set the chart height
                    },
                    exporting: {
                        enabled: false, // Disable the exporting functionality
                    },
                    title: {
                        text: 'جلسات كلية (' + faculty.name + ')', // Title of the chart
                    },
                    xAxis: {
                        categories: [
                            'مجموع الجلسات',
                            'الجلسات قيد الانتظار',
                            'الجلسات المقبولة',
                            'الجلسات المرفوضة',
                        ],
                        title: {
                            text: null, // No title for the x-axis
                        },
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: '', // No title for the y-axis
                            align: 'high',
                        },
                        labels: {
                            overflow: 'justify',
                        },
                    },
                    series: [{
                        name: 'sessions',
                        data: [
                            faculty.total_sessions,
                            faculty.total_pending_sessions,
                            faculty.total_accepted_sessions,
                            faculty.total_rejected_sessions,
                        ],
                        colorByPoint: true, // Different colors for each bar
                        colors: [
                            '#007bff', // Blue for Total sessions
                            '#ffc107', // Yellow for Pending sessions
                            '#28a745', // Green for Accepted sessions
                            '#dc3545', // Red for Rejected sessions
                        ],
                        borderWidth: 1,
                        dataLabels: {
                            enabled: true, // Enable data labels on bars
                            align: 'right', // Right-align the labels
                            verticalAlign: 'middle', // Center-align labels inside bars
                            style: {
                                fontSize: '12px', // Set font size for the labels
                                fontWeight: 'bold',
                                color: 'black', // Set text color for better visibility
                            },
                            formatter: function() {
                                // Format the label text to show the value
                                return this.y;
                            },
                        },
                    }],
                    plotOptions: {
                        bar: {
                            pointPadding: 0.2, // Adjust the space between bars
                            groupPadding: 0.1, // Decrease space between the groups
                        },
                    },
                    legend: {
                        enabled: false, // Disable the legend, as we don't need the series legend
                    },
                    tooltip: {
                        headerFormat: '',
                        pointFormat: '{point.category}: <b>{point.y}</b>', // Show category and value on hover
                    },
                    credits: {
                        enabled: false, // Remove Highcharts watermark
                    },
                };

                // Check if the chart exists, then update, else create a new one
                if (sessionOverviewChart) {
                    // Update the existing Highcharts chart
                    sessionOverviewChart.update(chartData);
                } else {
                    // Create a new Highcharts chart
                    sessionOverviewChart = Highcharts.chart('session-overview-chart', chartData);
                }
            }


            function detailsChart(faculty) {
                console.log(faculty);

                // Prepare the data for the chart
                const chartData = {
                    chart: {
                        type: 'column', // Use 'column' for normal vertical bars
                        height: '400', // Optional: Set the chart height
                    },
                    exporting: {
                        enabled: false // Disable the exporting functionality
                    },
                    title: {
                        text: '', // Title of the chart
                    },
                    xAxis: {
                        categories: [
                            'الجلسات قيد الاعتماد',
                            'الجلسات المعتمدة',
                            'الجلسات المرفوضة',
                        ],
                        // title: {
                        //     text: 'Agenda Types',
                        // },
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: '',
                            align: 'high',
                        },
                        labels: {
                            overflow: 'justify',
                        },
                    },
                    series: [{
                        name: 'Sessions',
                        data: [
                            faculty.total_pendingDecision_sessions,
                            faculty.total_approvedDecision_sessions,
                            faculty.total_rejectedDecision_sessions,
                        ],
                        colorByPoint: true, // Different colors for each bar
                        colors: [
                            '#ffc107', // Yellow for Pending sessions
                            '#28a745', // Green for Accepted sessions
                            '#dc3545', // Red for Rejected sessions
                        ],
                        borderWidth: 1,
                        pointWidth: 30, // Adjust the width of each bar
                        dataLabels: {
                            enabled: true, // Enable data labels on top of the bars
                            align: 'center', // Center align the labels above the bars
                            verticalAlign: 'bottom', // Place labels above the bars
                            style: {
                                fontSize: '12px', // Set font size for the labels
                                fontWeight: 'bold',
                                color: 'black', // Set text color to white for better visibility
                            },
                            formatter: function() {
                                // Format the label text to show the value above each bar
                                return this.y;
                            }
                        },
                    }],
                    plotOptions: {
                        column: {
                            pointPadding: 0.2, // Adjust the space between columns
                            groupPadding: 0.1, // Decrease space between the columns in the group
                        },
                    },
                    legend: {
                        enabled: false, // Disable default legend, as we don't need the series legend
                    },
                    tooltip: {
                        headerFormat: '',
                        pointFormat: '{point.category}: <b>{point.y}</b>', // Show category and value on hover
                    },
                };

                // Check if the chart exists, then update, else create a new one
                if (sessionDetailsChart) {
                    // Update the existing Highcharts chart
                    sessionDetailsChart.update(chartData);
                } else {
                    // Create a new Highcharts chart
                    sessionDetailsChart = Highcharts.chart('session-details-chart', chartData);
                }
            }


        });
    </script>

    <style>
        /* Increase the height and padding of the Select2 container */
        .select2-container .select2-selection--single {
            height: 45px !important;
            /* Adjust height */
            padding: 6px 12px !important;
            /* Adjust padding */
            border: 1px solid #d1d5db;
            /* Add a border color */
            border-radius: 8px;
            /* Rounded corners */
            background-color: #f9fafb;
            /* Background color */
        }

        /* Style the placeholder text */
        .select2-container .select2-selection--single .select2-selection__placeholder {
            color: #6b7280;
            /* Neutral text color */
            font-size: 14px;
            /* Text size */
        }

        /* Style the dropdown arrow */
        .select2-container .select2-selection--single .select2-selection__arrow {
            height: 45px;
            /* Match the height of the select box */
        }

        /* Style the dropdown menu */
        .select2-container .select2-dropdown {
            border-radius: 8px;
            /* Rounded corners */
            border: 1px solid #d1d5db;
            /* Border color */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            /* Dropdown shadow */
        }

        /* Style dropdown items */
        .select2-container .select2-results__option {
            padding: 10px;
            /* Spacing for each item */
            font-size: 14px;
            /* Font size */
        }

        /* Style dropdown items on hover */
        .select2-container .select2-results__option--highlighted {
            background-color: #e5e7eb;
            /* Light hover background */
            color: #000;
            /* Text color */
        }

        .highcharts-credits {
            display: none !important;
        }
    </style>

</x-filament::page>
