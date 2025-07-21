<?php
// Assuming this script is running in a Laravel application context
use Illuminate\Support\Facades\App;

// Initialize an empty array to hold the language data
$langData = [];

// Define the path to the language file based on the locale
$langFile = $locale == 'ar' ? __DIR__ . '/../../../lang/ar.json' : __DIR__ . '/../../../lang/en.json';

// Check if the file exists
if (file_exists($langFile)) {
    // Read the contents of the language file and decode it
    $langData = json_decode(file_get_contents($langFile), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        // Handle JSON decoding error
        echo 'Error decoding JSON from language file.';
    }
} else {
    // Handle the scenario where the language file doesn't exist
    echo "Language file for '$locale' not found!";
}

// Use $langData here or within this block

?>

<!-- External checkbox to attend all users -->
<div style="margin: 7px 11px;">
    <label class="flex items-center text-sm font-medium text-gray-900 dark:text-white">
        <input type="checkbox" id="attendAll" class="mr-2">
        <span style="margin: 5px"><?= $langData['attend all'] ?></span>
    </label>
</div>

@foreach ($userData as $user)
    <div class="flex items-center space-x-4 mb-4">
        <div class="user-info flex-shrink-0" style="margin: 0px 10px;">
            <span id="userName" class="text-sm font-medium text-gray-900 dark:text-white">
                @if ($user['taken'] == 1 && $user['actual_status'] == 1)
                    <svg class="w-4 h-4 ml-1 text-green-500 inline" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                @else
                    <svg class="w-4 h-4 ml-1 text-red-500 inline" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                @endif
                {{ $user['name'] }}

            </span>
        </div>
        <div class="attendance-options flex items-center space-x-2">
            <label class="flex items-center text-sm font-medium text-gray-900 dark:text-white">
                {{-- <input class="attenstatus" type="radio" name="attendance[{{ $user['id'] }}]" value="1" --}}
                <input class="attenstatus" @if ($decisionApproval == 1) disabled @endif type="radio"
                    name="attendance[{{ $user['id'] }}]" value="1"
                    {{ isset($user['actual_status']) ? ($user['actual_status'] == 1 ? 'checked' : '') : ($user['status'] == 1 ? 'checked' : '') }}
                    required>
                <span class="ml-1"><?= $langData['attend'] ?></span>
            </label>
            <label class="flex items-center text-sm font-medium text-gray-900 dark:text-white">
                <input class="attenstatus" @if ($decisionApproval == 1) disabled @endif type="radio"
                    name="attendance[{{ $user['id'] }}]" value="2"
                    {{ isset($user['actual_status']) ? ($user['actual_status'] == 2 ? 'checked' : '') : ($user['status'] == 2 ? 'checked' : '') }}
                    required>
                <span class="ml-1"><?= $langData['sorry'] ?></span>
            </label>
            <label class="flex items-center text-sm font-medium text-gray-900 dark:text-white">
                <input class="attenstatus" @if ($decisionApproval == 1) disabled @endif type="radio"
                    name="attendance[{{ $user['id'] }}]" value="3"
                    {{ isset($user['actual_status']) ? ($user['actual_status'] == 3 ? 'checked' : '') : ($user['status'] == 3 ? 'checked' : '') }}
                    required>
                <span class="ml-1"><?= $langData['absent'] ?></span>
            </label>
        </div>
    </div>
    @if ($user['absent_reason'])
        <div class="mt-2 mb-4" id="absentReasonSection-{{ $user['id'] }}">
            <p class="text-sm font-medium text-gray-900 dark:text-white mb-2">Reason Of Absent : </p>
            <textarea readonly
                class="w-full p-2 text-sm font-medium text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-800 rounded-lg">{{ $user['absent_reason'] }}</textarea>
        </div>
    @endif
@endforeach

<script>
    $(document).ready(function() {
        // Function to toggle the visibility of the absent reason section
        function toggleAbsentReasonSection(userId) {
            let selectedValue = $(`input[name="attendance[${userId}]"]:checked`).val();
            let absentReasonSection = $(`#absentReasonSection-${userId}`);

            if (selectedValue === "1") {
                // Hide the absent reason section if "Attend" is selected
                absentReasonSection.addClass("hidden");
            } else {
                // Show the absent reason section for all other options
                absentReasonSection.removeClass("hidden");
            }
        }

        // Initial check on page load for each user
        @foreach ($userData as $user)
            toggleAbsentReasonSection({{ $user['id'] }});
        @endforeach

        // Bind the change event to all attendance radio buttons
        $('input.attenstatus[type="radio"]').on('change', function() {
            let userId = $(this).attr('name').match(/\d+/)[
                0]; // Extract user ID from the name attribute
            toggleAbsentReasonSection(userId);
        });

        // Handle the "Attend All" checkbox
        $('#attendAll').on('change', function() {
            if ($(this).is(':checked')) {
                // Check all radio buttons with value 1
                $('input.attenstatus[type="radio"][value="1"]:not(:disabled)').prop('checked', true)
                    .trigger('change');
            } else {
                // Uncheck all radio buttons with value 1 if the checkbox is unchecked
                $('input.attenstatus[type="radio"][value="1"]:not(:disabled)').prop('checked', false)
                    .trigger('change');
            }
        });
    });
</script>
