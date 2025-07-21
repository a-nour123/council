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


    <div class="container">

        <div class="radio-buttons" style="margin-bottom: 15px;">
            <label class="radio-label">
                <input type="radio" name="bulk_status" id="acceptAll" value="1" class="radio-input accept">
                <span><?= $langData['Accept all'] ?></span>
            </label>

            <label class="radio-label">
                <input type="radio" name="bulk_status" id="rejectAll" value="2" class="radio-input reject">
                <span><?= $langData['Reject all'] ?></span>
            </label>

            <!-- Textarea under radio buttons -->
            <textarea name="bulk_rejected_reason" placeholder="<?= $langData['Rejected reason'] ?>" rows="3" class="textarea"
                style="display: none;"></textarea>
        </div>

        @foreach ($topics as $topic)
            <div class="topic-card">
                <div class="topic-name">
                    {!! $topic->agenda_topic !!}
                </div>
                <input type="hidden" name="escalation_authority[{{ $topic->agenda_id }}]"
                    value="{{ $topic->agenda_escalation }}">
                <div class="radio-buttons">
                    <label class="radio-label">
                        <input type="radio" name="status[{{ $topic->agenda_id }}]" id="accept_{{ $topic->agenda_id }}"
                            value="1" class="radio-input accept">
                        <span><?= $langData['Accept'] ?></span>
                    </label>

                    <label class="radio-label">
                        <input type="radio" name="status[{{ $topic->agenda_id }}]" id="reject_{{ $topic->agenda_id }}"
                            value="2" class="radio-input reject">
                        <span><?= $langData['Reject'] ?></span>
                    </label>
                </div>

                <!-- Textarea under radio buttons -->
                <textarea name="rejected_reason_[{{ $topic->agenda_id }}]" placeholder="<?= $langData['Rejected reason'] ?>"
                    rows="3" class="textarea" style="display: none;"></textarea>
            </div>
        @endforeach

        <div class="save-button">
            <button type="button" id="saveBtn" class="save-btn"><?= $langData['Save'] ?></button>
        </div>
    </div>

    <style>
        /* Topic Card */
        .topic-card {
            display: flex;
            flex-direction: column;
            /* Change from row to column to stack elements */
            justify-content: flex-start;
            align-items: flex-start;
            padding: 15px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
            transition: box-shadow 0.3s ease;
        }

        .topic-card:hover {
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .topic-name {
            font-size: 16px;
            color: #333;
            margin-bottom: 10px;
            word-wrap: break-word;
            /* Correct word-wrap */
        }

        .radio-buttons {
            display: flex;
            gap: 20px;
            margin-bottom: 10px;
            /* Add some space between radio buttons and textarea */
        }

        .radio-label {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #555;
            cursor: pointer;
        }

        .radio-input {
            margin-right: 5px;
            accent-color: #4caf50;
        }

        .radio-input.reject {
            accent-color: #f44336;
        }

        /* Textarea Styles */
        .textarea {
            width: 100%;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
            color: #555;
            margin-top: 10px;
            resize: vertical;
        }

        /* Save Button */
        .save-button {
            text-align: start;
            margin-top: 30px;
        }

        span {
            margin: 5px;
        }

        .save-btn {
            padding: 12px 30px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .save-btn:hover {
            background-color: #0056b3;
        }
    </style>


    <!-- Include necessary scripts -->
    <script src="{{ URL::asset('assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ URL::asset('assets/form-builder/form-builder.min.js') }}"></script>
    <link rel="stylesheet" href="{{ URL::asset('assets/css/sweetalert2.min.css') }}">
    <script src="{{ URL::asset('assets/js/sweetalert2.min.js') }}"></script>


    <script>
        $(document).ready(function() {
            var $langData = {
                'Success': `<?= $langData['Success'] ?>`,
                'saving data': `<?= $langData['saving data'] ?>`,
                'ok': `<?= $langData['ok'] ?>`,
                'error': `<?= $langData['error'] ?>`,
                'Add another': `<?= $langData['Add another'] ?>`,
                'end session message': `<?= $langData['end session message'] ?>`,
            };

            // Show textarea when reject is selected
            $('input[type="radio"].reject').on('change', function() {
                var agendaId = $(this).attr('id').split('_')[1];
                var textarea = $('textarea[name="rejected_reason_[' + agendaId + ']"]');
                textarea.css('display', 'block');
            });

            // Hide textarea when accept is selected
            $('input[type="radio"].accept').on('change', function() {
                var agendaId = $(this).attr('id').split('_')[1];
                var textarea = $('textarea[name="rejected_reason_[' + agendaId + ']"]');
                textarea.css('display', 'none');
                textarea.val('');
            });

            // Bulk status selection change
            $('input[name="bulk_status"]').on('change', function() {
                var bulkStatus = $(this).val();
                console.log(bulkStatus);

                // Loop through each topic card and disable/enable status radio buttons
                $('div.topic-card').each(function() {
                    var agendaId = $(this).find('input[type="radio"]').first().attr('name').split(
                        '[')[1].split(']')[0];

                    // If bulk status is 'Accept all' or 'Reject all', disable the individual topic radio buttons
                    if (bulkStatus == '1' || bulkStatus == '2') {
                        $('input[name="status[' + agendaId + ']"]').prop('disabled', true);
                        // Set all status to the selected bulk status
                        $('input[name="status[' + agendaId + ']"][value="' + bulkStatus + '"]')
                            .prop('checked', true);
                    } else {
                        $('input[name="status[' + agendaId + ']"]').prop('disabled', false);
                    }
                });

                // Show or hide the "Rejected reason" textarea based on bulk status
                if (bulkStatus == '2') {
                    $('textarea[name="bulk_rejected_reason"]').css('display', 'block');
                } else {
                    $('textarea[name="bulk_rejected_reason"]').css('display', 'none');
                    $('textarea[name="bulk_rejected_reason"]').val('');
                }
            });

            // Click event for the Save button
            $("#saveBtn").click(function(e) {
                e.preventDefault();
                var sessionId = `{{ $session->id }}`;

                // Create an array to store the data
                var data = [];

                // Collect data based on bulk status or individual status
                if ($('input[name="bulk_status"]:checked').val() != null) {
                    var bulkStatus = $('input[name="bulk_status"]:checked').val();
                    var rejectReason = $('textarea[name="bulk_rejected_reason"]').val();

                    // Loop through each topic to collect data
                    $('div.topic-card').each(function() {
                        var agendaId = $(this).find('input[type="radio"]').first().attr('name')
                            .split('[')[1].split(']')[0];

                        var agendaEscalation = $('input[name="escalation_authority[' + agendaId +
                            ']"]').val();

                        // Push the values into the data array as an object
                        data.push({
                            session_id: sessionId,
                            escalation_authority: agendaEscalation,
                            topic_id: agendaId,
                            status: bulkStatus, // Apply the bulk status
                            reject_reason: rejectReason
                        });
                    });
                } else {
                    // Loop through each topic to collect individual data
                    $('div.topic-card').each(function() {
                        var agendaId = $(this).find('input[type="radio"]').first().attr('name')
                            .split('[')[1].split(']')[0];

                        var agendaEscalation = $('input[name="escalation_authority[' + agendaId +
                            ']"]').val();

                        // Get the selected status (Accept or Reject)
                        var status = $('input[name="status[' + agendaId + ']"]:checked').val();

                        // Get the rejected reason if reject is selected
                        var rejectedReason = $('textarea[name="rejected_reason_[' + agendaId +
                            ']"]').val();

                        // Push the values into the data array as an object
                        data.push({
                            session_id: sessionId,
                            escalation_authority: agendaEscalation,
                            topic_id: agendaId,
                            status: status,
                            reject_reason: rejectedReason,
                        });
                    });
                }

                // console.log(data);

                // Make AJAX request
                $.ajax({
                    url: '{{ route('saveCollegeCouncil', ':sessionId') }}'.replace(':sessionId',
                        sessionId),
                    method: 'POST',
                    data: {
                        data: data,
                        _token: $('meta[name="csrf-token"]').attr('content'), // Add CSRF token here
                    },
                    success: function(response) {
                        console.log(response);
                        Swal.fire({
                            position: 'center',
                            title: $langData['Success'],
                            text: $langData['saving data'],
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            window.history
                                .back(); // Correct way to go back to the previous page
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(error);
                        Swal.fire({
                            position: 'center',
                            title: $langData['error'],
                            text: $langData['saving data'],
                            icon: 'error',
                            showConfirmButton: true,
                        });
                    }
                });
            });
        });
    </script>


</x-filament::page>
