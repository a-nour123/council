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

@foreach ($decisionData as $decision)
    <div class="decision-content flex flex-col items-start space-y-2 mb-4">
        <label for="decision" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
            التصويت علي موضوع: <span style="color:red">{!! strip_tags($decision['TopicTitle']) !!}</span>
        </label>
        <input type="hidden" value="1" name="voteType">
        <textarea id="decision" readonly name="decision[{{ $decision['decision_id'] }}]" rows="4"
            class="decision bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">{{ $decision['decision'] }}</textarea>
        <input type="hidden" name="decision_id[{{ $decision['decision_id'] }}]" value="{{ $decision['decision_id'] }}">
    </div>

    @foreach ($decision['users'] as $user)
        <div class="flex items-center space-x-4 mb-4">
            <div class="user-info flex-shrink-0" style="margin: 0px 10px;">
                <span id="userName" class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ $user['name'] }}
                </span>
            </div>
            <div class="voiting-options flex items-center space-x-2">
                <label class="flex items-center text-sm font-medium text-gray-900 dark:text-white">
                    <input class="voitingstatus" type="radio" @if ($decisionApproval == 1) disabled @endif
                        name="voiting[{{ $decision['decision_id'] }}][{{ $user['id'] }}]" value="1"
                        {{ $user['vote'] === 1 ? 'checked' : '' }} required>
                    <span class="ml-1"><?= $langData['Agree'] ?></span>
                </label>
                <label class="flex items-center text-sm font-medium text-gray-900 dark:text-white">
                    <input class="voitingstatus" type="radio" @if ($decisionApproval == 1) disabled @endif
                        name="voiting[{{ $decision['decision_id'] }}][{{ $user['id'] }}]" value="2"
                        {{ $user['vote'] === 2 ? 'checked' : '' }} required>
                    <span class="ml-1"><?= $langData['Refuse'] ?></span>
                </label>
            </div>
        </div>
    @endforeach
@endforeach
