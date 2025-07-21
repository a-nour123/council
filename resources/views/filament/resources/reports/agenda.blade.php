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
                    @foreach ($faculties as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Department Select -->
            <div class="w-1/2">
                <label for="department-select"
                    class="block text-sm font-medium text-gray-700"><?= $langData['Department'] ?></label>
                <select id="department-select"
                    class="select2 mt-1 block w-full pl-3 pr-10 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value=""><?= $langData['Select department'] ?></option>
                </select>
            </div>
        </div>

        <div class="mt-6">
            <div id="agenda-overview-chart" style="width:100%; height:400px;"></div>
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
                'Add another': `<?= $langData['Add another'] ?>`,
                'Select': `<?= $langData['Select'] ?>`,
                'Select department': `<?= $langData['Select department'] ?>`,
                'Select faculty': `<?= $langData['Select faculty'] ?>`,
                'Select academic year': `<?= $langData['Select academic year'] ?>`,
            };

            $('.select2').select2({
                // placeholder: $langData['Select'], // Optional: Placeholder text
                // allowClear: true, // Enable the 'x' clear icon
                width: '100%', // Full-width dropdown
            });

            let agendaChart; // Reference for the Chart.js instance
            let departmentData = []; // Cache for department data from the backend

            // Fetch departments and their agendas
            $('#year-select, #faculty-select').change(function() {
                const selectedYearId = $('#year-select').val(); // Selected year ID
                const selectedFacultyId = $('#faculty-select').val(); // Selected faculty ID
                const $departmentSelect = $('#department-select'); // Department dropdown

                // Reset the departments dropdown
                $departmentSelect.empty().append(
                    `<option value="">` + $langData['Select department'] + `</option>`
                );

                if (selectedYearId && selectedFacultyId) {
                    $.ajax({
                        url: `{{ route('reports.agenda') }}`,
                        type: 'GET',
                        data: {
                            yearId: selectedYearId,
                            facultyId: selectedFacultyId,
                        },
                        success: function(data) {
                            departmentData = data; // Cache the department data
                            console.log(data);

                            // Populate the department dropdown
                            $.each(data, function(index, department) {
                                $departmentSelect.append(
                                    `<option value="${department.id}">${department.name}</option>`
                                );
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching departments:', error);
                        },
                    });
                }
            });

            // Handle department selection to update the chart
            $('#department-select').change(function() {
                const selectedDepartmentId = $(this).val(); // Selected department ID

                if (selectedDepartmentId) {
                    // Find the selected department's data
                    const selectedDepartment = departmentData.find(
                        (dept) => dept.id == selectedDepartmentId
                    );

                    if (selectedDepartment) {
                        // Update the chart with the selected department's data
                        updateChart(selectedDepartment);
                    }
                }
            });

            // Function to update the Highcharts chart with labels above each bar and color legend on top
            function updateChart(department) {
                console.log(department);

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
                        text: 'طلبات قسم (' + department.name + ')', // Title of the chart
                    },
                    xAxis: {
                        categories: [
                            'مجموع الطلبات',
                            'الطلبات قيد الانتظار',
                            'الطلبات المقبولة',
                            'الطلبات المرفوضة',
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
                        name: 'Agendas',
                        data: [
                            department.total_agendas,
                            department.total_pending_agendas,
                            department.total_accepted_agendas,
                            department.total_rejected_agendas,
                        ],
                        colorByPoint: true, // Different colors for each bar
                        colors: [
                            '#007bff', // Blue for Total Agendas
                            '#ffc107', // Yellow for Pending Agendas
                            '#28a745', // Green for Accepted Agendas
                            '#dc3545', // Red for Rejected Agendas
                        ],
                        borderWidth: 1,
                        dataLabels: {
                            enabled: true, // Enable data labels on bars
                            align: 'right', // Align labels to the right of each bar
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
                if (agendaChart) {
                    // Update the existing Highcharts chart
                    agendaChart.update(chartData);
                } else {
                    // Create a new Highcharts chart
                    agendaChart = Highcharts.chart('agenda-overview-chart', chartData);
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
