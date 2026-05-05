$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function showAlert(box, type, message) {
        box.removeClass('d-none alert-success alert-danger')
           .addClass('alert-' + type)
           .text(message);
        setTimeout(() => box.addClass('d-none'), 5000);
    }

    let activePollId = null;
    let resultTimer = null;
    const voteAlert = $('#vote-alert');

    function renderPoll(poll) {
        let optionsHtml = '';
        poll.options.forEach(function(option) {
            optionsHtml += `
                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="option_id" id="option_${option.id}" value="${option.id}">
                    <label class="form-check-label w-100" for="option_${option.id}">
                        ${option.option_text}
                        <div class="progress mt-1">
                            <div class="progress-bar bg-success" id="progress_${option.id}" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0% (0 votes)</div>
                        </div>
                    </label>
                </div>
            `;
        });

        $('#poll-question').text(poll.question);
        $('#poll-options-container').html(optionsHtml);
        $('#poll-form').attr('data-poll-id', poll.id);
        $('#poll-placeholder').addClass('d-none');
        $('#poll-detail-card').removeClass('d-none');
        activePollId = poll.id;
    }

    function loadPoll(pollId) {
        $.ajax({
            url: '/poll/' + pollId,
            type: 'GET',
            success: function(response) {
                renderPoll(response);
                fetchResults(pollId);
            }
        });
    }

    function fetchResults(pollId) {
        $.ajax({
            url: '/poll/results/' + pollId,
            type: 'GET',
            success: function(response) {
                let totalVotes = response.total_votes;
                let results = response.results;

                results.forEach(function(res) {
                    let percentage = totalVotes > 0 ? Math.round((res.vote_count / totalVotes) * 100) : 0;
                    let progressBar = $('#progress_' + res.id);
                    progressBar.css('width', percentage + '%');
                    progressBar.attr('aria-valuenow', percentage);
                    progressBar.text(percentage + '% (' + res.vote_count + ' votes)');
                });
            }
        });
    }

    function startRealtimeResults() {
        if (resultTimer) {
            clearInterval(resultTimer);
        }

        resultTimer = setInterval(function() {
            if (activePollId) {
                fetchResults(activePollId);
            }
        }, 1000);
    }

    $('.poll-list-item').click(function() {
        $('.poll-list-item').removeClass('active');
        $(this).addClass('active');
        loadPoll($(this).data('poll-id'));
    });

    $('#poll-form').on('click', '.btn-vote', function(e) {
        e.preventDefault();
        const pollId = $('#poll-form').attr('data-poll-id');
        const optionId = $('#poll-form').find('input[name="option_id"]:checked').val();

        if (!pollId || !optionId) {
            showAlert(voteAlert, 'danger', 'Please select an option to vote.');
            return;
        }

        $.ajax({
            url: '/vote',
            type: 'POST',
            data: { poll_id: pollId, option_id: optionId },
            success: function(response) {
                showAlert(voteAlert, 'success', response.message);
                fetchResults(pollId);
            },
            error: function(xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred.';
                showAlert(voteAlert, 'danger', msg);
            }
        });
    });

    if ($('#poll-list').length > 0) {
        startRealtimeResults();
        if (window.livePollingData && window.livePollingData.initialPollId) {
            $('.poll-list-item').first().addClass('active');
            loadPoll(window.livePollingData.initialPollId);
        }
    }

    $('#create-poll-form').submit(function(e) {
        e.preventDefault();
        const form = $(this);
        const alertBox = $('#admin-alert');
        const payload = {
            question: form.find('input[name="question"]').val(),
            status: form.find('select[name="status"]').val(),
            options: []
        };

        form.find('.poll-option-input').each(function() {
            const value = $(this).val().trim();
            if (value !== '') {
                payload.options.push(value);
            }
        });

        $.ajax({
            url: '/admin/polls',
            type: 'POST',
            data: payload,
            success: function(response) {
                showAlert(alertBox, 'success', response.message + ' Refreshing...');
                setTimeout(function() {
                    window.location.reload();
                }, 700);
            },
            error: function(xhr) {
                const msg = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Could not create poll. Check the inputs and try again.';
                showAlert(alertBox, 'danger', msg);
            }
        });
    });

    $(document).on('click', '.btn-release-ip', function(e) {
        e.preventDefault();
        let pollId = $(this).data('poll-id');
        let ipAddress = $(this).data('ip');
        let row = $(this).closest('tr');
        let btn = $(this);
        let alertBox = $('#admin-alert');

        if (confirm('Are you sure you want to release this IP? They will be able to vote again.')) {
            $.ajax({
                url: '/admin/release-ip',
                type: 'POST',
                data: { poll_id: pollId, ip_address: ipAddress },
                success: function(response) {
                    showAlert(alertBox, 'success', response.message);
                    row.find('.vote-status').removeClass('bg-success').addClass('bg-secondary').text('Released');
                    btn.remove();
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error releasing IP.';
                    showAlert(alertBox, 'danger', msg);
                }
            });
        }
    });

    $('.btn-view-history').click(function(e) {
        e.preventDefault();
        let pollId = $(this).data('poll-id');
        let ipAddress = $(this).data('ip');

        $('#modal-ip').text(ipAddress);
        $('#modal-history-content').html('<p>Loading...</p>');
        let historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
        historyModal.show();

        $.ajax({
            url: '/admin/history/' + pollId + '/' + ipAddress,
            type: 'GET',
            success: function(response) {
                let html = '<ul class="list-unstyled">';
                if (response.history.length === 0) {
                    html += '<li class="text-muted">No history found.</li>';
                }
                response.history.forEach(function(vote) {
                    let statusClass = vote.status === 'active' ? 'active' : 'released';
                    html += `<li class="history-item ${statusClass}">
                        <strong>Voted for:</strong> ${vote.option.option_text} <br>
                        <small class="text-muted">Status: ${vote.status.toUpperCase()} | Date: ${new Date(vote.created_at).toLocaleString()}</small>
                    </li>`;
                });
                html += '</ul>';
                $('#modal-history-content').html(html);
            }
        });
    });
});
