@php
    $topicAgendaIds = $topicAgendaIds ?? [];
@endphp

<div class="container">
    <div class="row">
        @foreach($topicAgendaIds as $index => $topicId)
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        Topic {{ $index + 1 }}
                    </div>
                    <div class="card-body">
                        <!-- Display topic content based on $topicId -->
                        <p>Topic ID: {{ $topicId }}</p>
                        <!-- You can fetch and display additional details here -->
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
