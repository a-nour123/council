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
        <div class="p-6 bg-white shadow-lg rounded-lg max-w-4xl mx-auto">
            <!-- Flex container for heading and test button -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold"><?= $langData['LDAP Settings'] ?></h2>

                @if (isset($ldapSettings) && !empty($ldapSettings))
                    <button type="button" style="background-color: #4abb80; color: white;" id="testConnection"
                        class="py-2 px-4 rounded-md hover:bg-green-700 text-white text-sm">
                        Test connection
                    </button>
                @endif
            </div>

            <form action="" class="space-y-6">
                @csrf
                <input type="hidden" name="id" value="{{ $ldapSettings['id'] ?? '' }}">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="hosts"
                            class="block text-sm font-medium text-gray-700">hosts</label>
                        <input type="text" name="hosts" id="hosts" value="{{ $ldapSettings['hosts'] ?? '' }}"
                            required placeholder="Enter the LDAP server address."
                            class="w-full mt-1 p-2 border rounded-md">
                    </div>

                    <div>
                        <label for="port"
                            class="block text-sm font-medium text-gray-700">port</label>
                        <input type="number" name="port" id="port" value="{{ $ldapSettings['port'] ?? '' }}"
                            required placeholder="Enter the LDAP port (e.g., 3268)"
                            class="w-full mt-1 p-2 border rounded-md">
                    </div>
                </div>

                <div>
                    <label for="base_dn"
                        class="block text-sm font-medium text-gray-700">base_dn</label>
                    <input type="text" name="base_dn" id="base_dn" value="{{ $ldapSettings['base_dn'] ?? '' }}"
                        required placeholder="Enter the Base DN (e.g., dc=pk,dc=com)."
                        class="w-full mt-1 p-2 border rounded-md">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="username"
                            class="block text-sm font-medium text-gray-700">username</label>
                        <input type="text" name="username" id="username"
                            value="{{ $ldapSettings['username'] ?? '' }}" required
                            placeholder="Enter the LDAP administrator username."
                            class="w-full mt-1 p-2 border rounded-md">
                    </div>
                    <div>
                        <label for="password"
                            class="block text-sm font-medium text-gray-700">password</label>
                        <input type="password" name="password" id="password"
                            @if (empty($ldapSettings['id'])) required @endif placeholder="******"
                            class="w-full mt-1 p-2 border rounded-md">
                    </div>
                </div>

                <div>
                    <label for="filter"
                        class="block text-sm font-medium text-gray-700">filter</label>
                    <input type="text" name="filter" id="filter" value="{{ $ldapSettings['filter'] ?? '' }}"
                        class="w-full mt-1 p-2 border rounded-md"
                        placeholder="Enter an optional LDAP search filter.">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="version"
                            class="block text-sm font-medium text-gray-700">version</label>
                        <input type="number" name="version" id="version" value="{{ $ldapSettings['version'] ?? '' }}"
                            required placeholder="Enter the LDAP protocol version (default: 3)."
                            class="w-full mt-1 p-2 border rounded-md">
                    </div>
                    <div>
                        <label for="timeout"
                            class="block text-sm font-medium text-gray-700">timeout</label>
                        <input type="number" name="timeout" id="timeout" value="{{ $ldapSettings['timeout'] ?? '' }}"
                            required placeholder="Enter the connection timeout in seconds."
                            class="w-full mt-1 p-2 border rounded-md">
                    </div>
                </div>

                <div class="flex gap-8">
                    <div class="flex flex-col items-center text-center">
                        <div class="flex items-center">
                            <input type="checkbox" name="ssl" id="ssl" class="rounded border-gray-300"
                                {{ !empty($ldapSettings['ssl']) ? 'checked' : '' }}>
                            <label for="ssl" class="ml-2 text-sm text-gray-700">ssl</label>
                        </div>
                        <p class="text-xs text-gray-500">Enable SSL encryption.</p>
                    </div>

                    <div class="flex flex-col items-center text-center">
                        <div class="flex items-center">
                            <input type="checkbox" name="tls" id="tls" class="rounded border-gray-300"
                                {{ !empty($ldapSettings['tls']) ? 'checked' : '' }}>
                            <label for="tls" class="ml-2 text-sm text-gray-700">tls</label>
                        </div>
                        <p class="text-xs text-gray-500">Enable TLS encryption.</p>
                    </div>

                    <div class="flex flex-col items-center text-center">
                        <div class="flex items-center">
                            <input type="checkbox" name="follow" id="follow" class="rounded border-gray-300"
                                {{ !empty($ldapSettings['follow']) ? 'checked' : '' }}>
                            <label for="follow"
                                class="ml-2 text-sm text-gray-700">follow</label>
                        </div>
                        <p class="text-xs text-gray-500">Enable follow referrals.</p>
                    </div>
                </div>

                <button type="submit" style="background-color: #0d99f2; color: white;" id="submitForm"
                    class="w-full bg-blue-600 text-black py-2 px-4 rounded-md hover:bg-blue-700 mt-6">
                    Save
                </button>

            </form>
        </div>
    </div>




    <script src="{{ URL::asset('assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/jquery-ui.min.js') }}"></script>
    <link rel="stylesheet" href="{{ URL::asset('assets/css/sweetalert2.min.css') }}">
    <script src="{{ URL::asset('assets/js/sweetalert2.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/quill.min.js') }}"></script>
    <script src="{{ URL::asset('assets/js/flowbite.min.js') }}"></script>
    <link rel="stylesheet" href="{{ URL::asset('assets/css/quill.snow.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('assets/css/select2.min.css') }}">
    <script src="{{ URL::asset('assets/js/select2.min.js') }}"></script>


    <script>
        var $langData = {
            'Success': `<?= $langData['Success'] ?>`,
            'saving data': `<?= $langData['saving data'] ?>`,
            'ok': `<?= $langData['ok'] ?>`,
            'Add another': `<?= $langData['Add another'] ?>`,
            'error': `<?= $langData['error'] ?>`,
            'Processing': `<?= $langData['processing'] ?>`,
            'Please wait while we test the connection...': `<?= $langData['Please wait while we test the connection...'] ?>`,
        };

        $(document).ready(function() {
            var ldapSettings = @json($ldapSettings);

            $('form').on('submit', function(e) {
                e.preventDefault();
                // Target the submit button
                let formData = $(this).serializeArray(); // Convert form data to an array
                var submitButton = $('#submitForm');
                submitButton.prop('disabled', true).text('تحميل...');

                // Convert checkbox values to boolean
                formData.forEach(field => {
                    if (['ssl', 'tls', 'follow'].includes(field.name)) {
                        field.value = field.value === 'on' ? true : false;
                    }
                });

                console.log("formData", formData);

                $.ajax({
                    url: "{{ route('ldap-settings.save') }}",
                    method: "post",
                    data: formData,
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: $langData['Success'],
                            text: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location
                                .reload(); // Reload the page after the timer ends
                        });
                    },

                    error: function(xhr) {
                        submitButton.prop('disabled', false);
                        let errorMessage = xhr.responseJSON?.message || 'An error occurred';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage
                        });
                    }
                });
            });

            $('#testConnection').click(function(e) {
                e.preventDefault();

                // Get CSRF token from the meta tag
                var csrfToken = $('meta[name="csrf-token"]').attr('content');

                // Show processing alert
                Swal.fire({
                    title: $langData['Processing'],
                    text: $langData['Please wait while we test the connection...'],
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ route('ldap-settings.testConnection') }}", // Laravel route
                    type: "POST",
                    data: {
                        _token: csrfToken, // CSRF token
                        ldapSettings // Sending LDAP settings data
                    },
                    success: function(response) {
                        Swal.close(); // Close processing alert

                        if (response.success) {
                            // Success case
                            Swal.fire({
                                icon: 'success',
                                title: $langData['Success'],
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            // Error case returned from the backend
                            Swal.fire({
                                icon: 'error',
                                title: $langData['error'],
                                text: response.error,
                                showConfirmButton: true,
                                confirmButtonText: $langData['ok'],
                                confirmButtonColor: '#d33'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.close(); // Close processing alert

                        // Handle errors like 400 or 500 responses
                        let errorMessage = xhr.responseJSON?.error || 'An error occurred';
                        Swal.fire({
                            icon: 'error',
                            title: $langData['error'],
                            text: errorMessage,
                            showConfirmButton: true,
                            confirmButtonText: $langData['ok'],
                            confirmButtonColor: '#d33'
                        });
                    }
                });
            });


        });
    </script>

</x-filament::page>
