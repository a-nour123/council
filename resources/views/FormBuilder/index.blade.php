 
   <x-filament::page>

   <div class="card">
        <div class="card-body">
            <a href="{{ URL('formbuilder') }}" class="btn btn-success">{{__('Create')}}</a>
            <table class="table">
                <thead>
                    <th>{{__('Name')}}</th>
                    <th>{{__('Action')}}</th>
                </thead>
                <tbody>
                    @foreach ($forms as $form)
                        <tr>
                            <td>{{ $form->name }}</td>
                            <td>
                                <a href="{{ URL('edit-form-builder', $form->id) }}" class="btn btn-primary">{{__('Edit')}}</a>
                                <a href="{{ URL('read-form-builder', $form->id) }}" class="btn btn-primary">{{__('Show')}}</a>
                                <form method="POST" action="{{ URL('form-delete', $form->id) }}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this product?')">{{__('Delete')}}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
  <!-- Include necessary scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
  <script src="{{ URL::asset('assets/form-builder/form-builder.min.js') }}"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
  <script src="{{ URL::asset('assets/form-builder/form-builder.min.js') }}"></script>    <!-- Include Flowbite CSS and JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
  <!-- Override dark mode styles -->
  <style>
    .form-actions {
        display: none !important;
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
    .dark .form-field{
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
    .dark .form-elements{
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
</style>
</x-filament::page>
