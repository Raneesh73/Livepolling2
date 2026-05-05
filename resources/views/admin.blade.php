@extends('layout')

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <h2 class="mb-4">Admin Dashboard - Poll Moderation</h2>

        <div id="admin-alert" class="alert d-none" role="alert"></div>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Create New Poll</h5>
            </div>
            <div class="card-body">
                <form id="create-poll-form">
                    <div class="mb-3">
                        <label class="form-label" for="poll-question">Question</label>
                        <input id="poll-question" type="text" class="form-control" name="question" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="poll-status">Status</label>
                        <select id="poll-status" class="form-select" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <label class="form-label">Options</label>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6"><input type="text" class="form-control poll-option-input" name="options[]" placeholder="Option 1" required></div>
                        <div class="col-md-6"><input type="text" class="form-control poll-option-input" name="options[]" placeholder="Option 2" required></div>
                        <div class="col-md-6"><input type="text" class="form-control poll-option-input" name="options[]" placeholder="Option 3"></div>
                        <div class="col-md-6"><input type="text" class="form-control poll-option-input" name="options[]" placeholder="Option 4"></div>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Poll</button>
                </form>
            </div>
        </div>

        @foreach($polls as $poll)
            <div class="card mb-4">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $poll->question }}</h5>
                    <span class="badge bg-light text-dark">Total Votes: {{ $poll->votes->where('status', 'active')->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped admin-table mb-0">
                        <thead>
                            <tr>
                                <th>IP Address</th>
                                <th>Current Vote</th>
                                <th>Status</th>
                                <th>Date/Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Group votes by IP to easily see the latest active/released state
                                $groupedVotes = $poll->votes->groupBy('ip_address');
                            @endphp

                            @foreach($groupedVotes as $ip => $votes)
                                @php
                                    // Get the most recent vote for display
                                    $latestVote = $votes->sortByDesc('created_at')->first();
                                @endphp
                                <tr id="row_{{ $poll->id }}_{{ md5($ip) }}">
                                    <td>{{ $ip }}</td>
                                    <td>{{ $latestVote->option->option_text }}</td>
                                    <td>
                                        @if($latestVote->status == 'active')
                                            <span class="badge vote-status bg-success">Active</span>
                                        @else
                                            <span class="badge vote-status bg-secondary">Released</span>
                                        @endif
                                    </td>
                                    <td>{{ $latestVote->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        @if($latestVote->status == 'active')
                                            <button class="btn btn-sm btn-warning btn-release-ip" data-poll-id="{{ $poll->id }}" data-ip="{{ $ip }}">Release IP</button>
                                        @endif
                                        <button class="btn btn-sm btn-info btn-view-history" data-poll-id="{{ $poll->id }}" data-ip="{{ $ip }}">View History</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title">Vote History for IP: <span id="modal-ip"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="modal-history-content">
        <!-- Loaded via AJAX -->
      </div>
    </div>
  </div>
</div>
@endsection
