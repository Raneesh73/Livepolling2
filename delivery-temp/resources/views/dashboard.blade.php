@extends('layout')

@section('content')
<div class="row">
    <div class="col-md-4">
        <h2 class="mb-4">Active Polls</h2>
        <div class="list-group" id="poll-list">
            @forelse($polls as $poll)
                <button type="button" class="list-group-item list-group-item-action poll-list-item" data-poll-id="{{ $poll->id }}">
                    {{ $poll->question }}
                </button>
            @empty
                <div class="list-group-item text-muted">No active polls available.</div>
            @endforelse
        </div>
    </div>
    <div class="col-md-8">
        <div id="vote-alert" class="alert d-none" role="alert"></div>
        <div class="card d-none" id="poll-detail-card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0" id="poll-question"></h5>
            </div>
            <div class="card-body">
                <form id="poll-form" class="poll-form">
                    <div id="poll-options-container"></div>
                    <button type="button" class="btn btn-primary mt-3 btn-vote">Vote Now</button>
                </form>
            </div>
        </div>
        <div id="poll-placeholder" class="alert alert-info">
            Select a poll to view options and vote.
        </div>
    </div>
</div>
<script>
    window.livePollingData = {
        initialPollId: @json($polls->first()->id ?? null)
    };
</script>
@endsection
