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


    <div class="p-6 bg-white rounded-lg shadow">
        <h2 class="text-xl font-medium text-gray-900 mb-4"><?= $langData['Import users from LDAP'] ?></h2>

        @if (empty($ldapUsers))
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <p class="text-gray-500"><?= $langData['No LDAP users found'] ?></p>
            </div>
        @else
            <form action="">
                @csrf
                <div class="mb-4 flex justify-between items-center">
                    <div>
                        <button type="button" id="selectAll"
                            class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            <?= $langData['Select All'] ?>
                        </button>
                        <button type="button" id="deselectAll"
                            class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 ml-2">
                            <?= $langData['Deselect All'] ?>
                        </button>
                    </div>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <?= $langData['Import Selected Users'] ?>
                    </button>
                </div>

                <div class="overflow-x-auto relative">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50" style="text-align: center;">
                            <tr>
                                <th scope="col" class="p-4">
                                    <span class="sr-only"><?= $langData['Select'] ?></span>
                                </th>
                                <th scope="col" class="py-3 px-6"><?= $langData['Name'] ?></th>
                                <th scope="col" class="py-3 px-6"><?= $langData['Username'] ?></th>
                                <th scope="col" class="py-3 px-6"><?= $langData['Email'] ?></th>
                                <th scope="col" class="py-3 px-6"><?= $langData['Phone number'] ?></th>
                                <th scope="col" class="py-3 px-6"><?= $langData['Status'] ?></th>
                            </tr>
                        </thead>
                        <tbody style="text-align: center;">
                            @foreach ($ldapUsers as $index => $user)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="p-4 w-4">
                                        <div class="flex items-center">
                                            @if (!$user['exist'])
                                                <input id="checkbox-{{ $index }}" type="checkbox"
                                                    name="selected_users[]" value="{{ $user['username'] }}"
                                                    class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500">
                                                <label for="checkbox-{{ $index }}"
                                                    class="sr-only">{{ $user['username'] }}</label>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 px-6">{{ $user['name'] }}</td>
                                    <td class="py-3 px-6">{{ $user['username'] }}</td>
                                    <td class="py-3 px-6">{{ $user['email'] }}</td>
                                    <td class="py-3 px-6">{{ $user['phone'] ?: '-' }}</td>
                                    <td class="py-3 px-6">
                                        @if ($user['exist'])
                                            <span style="background-color: #d33838;"
                                                class="px-2 py-1 text-xs font-medium text-white rounded">
                                                <?= $langData['Already exists in system'] ?>
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </form>
        @endif
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
            'Cancel': `<?= $langData['Cancel'] ?>`,
            'Confirm': `<?= $langData['Confirm'] ?>`,
            'Add another': `<?= $langData['Add another'] ?>`,
            'error': `<?= $langData['error'] ?>`,
            'Importing users, please wait...': `<?= $langData['Importing users, please wait...'] ?>`,
            'Please select at least one user to import': `<?= $langData['Please select at least one user to import'] ?>`,
            'processing': `<?= $langData['processing'] ?>`,
            'Are you sure you want to import the selected users?': `<?= $langData['Are you sure you want to import the selected users?'] ?>`,
            'Users imported successfully!': `<?= $langData['Users imported successfully!'] ?>`,
            'An error occurred. Please try again.': `<?= $langData['An error occurred. Please try again.'] ?>`,
            'Failed Imports': `<?= $langData['Failed Imports'] ?>`,
        };

        $(document).ready(function() {
            const selectAllBtn = $('#selectAll');
            const deselectAllBtn = $('#deselectAll');
            const checkboxes = $('input[name="selected_users[]"]');
            const form = $('form');

            // Select All
            selectAllBtn.on('click', function() {
                checkboxes.prop('checked', true);
            });

            // Deselect All
            deselectAllBtn.on('click', function() {
                checkboxes.prop('checked', false);
            });

            // SweetAlert with AJAX
            form.on('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                const selectedUsers = $('input[name="selected_users[]"]:checked');

                if (selectedUsers.length === 0) {
                    Swal.fire({
                        title: $langData.error,
                        text: '<?= $langData['Please select at least one user to import'] ?>',
                        icon: 'error',
                        confirmButtonText: $langData.ok,
                        confirmButtonColor: '#d33' // Red error button color
                    });
                    return;
                }

                // Collect user data
                let usersData = [];
                selectedUsers.each(function() {
                    let row = $(this).closest('tr');
                    usersData.push({
                        username: $(this).val(),
                        name: row.find('td:nth-child(2)').text().trim(),
                        email: row.find('td:nth-child(4)').text().trim(),
                        phone: row.find('td:nth-child(5)').text().trim() === '-' ? '' : row
                            .find('td:nth-child(5)').text().trim(),
                    });
                });

                Swal.fire({
                    title: $langData['saving data'],
                    text: '<?= $langData['Are you sure you want to import the selected users?'] ?>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: $langData.Confirm,
                    cancelButtonText: $langData.Cancel,
                    confirmButtonColor: '#3085d6', // Blue color
                    cancelButtonColor: '#d33' // Red color
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ route('ldap-settings.importUsers') }}`,
                            type: "post",
                            data: {
                                _token: $('input[name="_token"]').val(),
                                users: usersData
                            },
                            beforeSend: function() {
                                Swal.fire({
                                    title: $langData['processing'],
                                    text: $langData[
                                        'Importing users, please wait...'],
                                    icon: 'info',
                                    showConfirmButton: false,
                                    allowOutsideClick: false
                                });
                            },
                            success: function(response) {
                                let message = `<p>${response.message}</p>`;

                                // Check if there are failed imports and display them properly
                                if (response.failed_imports && response.failed_imports
                                    .length > 0) {
                                    message +=
                                        `<hr><strong>${$langData['Failed Imports'] ?? 'Failed Imports'}:</strong><ul style="text-align:left;">`;
                                    response.failed_imports.forEach(user => {
                                        message +=
                                            `<li><strong>${user.username}</strong>: ${user.reason}</li>`;
                                    });
                                    message += `</ul>`;
                                }

                                Swal.fire({
                                    title: response.success ? ($langData[
                                        'Success'] ?? 'Success') : (
                                        $langData['Warning'] ?? 'Warning'),
                                    html: message, // Use `html` to format content
                                    icon: response.success ? 'success' :
                                        'warning',
                                    showConfirmButton: true,
                                    allowOutsideClick: false, // Prevent clicking outside
                                    confirmButtonText: $langData['ok'] ?? 'OK',
                                    confirmButtonColor: '#3085d6'
                                }).then(() => {
                                    location
                                .reload(); // Reload after confirmation
                                });
                            },
                            error: function(xhr) {
                                let errorMessage =
                                    $langData['An error occurred. Please try again.'];
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                Swal.fire({
                                    title: $langData.error,
                                    text: errorMessage,
                                    icon: 'error',
                                    confirmButtonText: $langData.ok,
                                    confirmButtonColor: '#d33'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>

</x-filament::page>
