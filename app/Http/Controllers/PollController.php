<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;

class PollController extends Controller
{
    public function index()
    {
        $polls = Poll::where('is_active', true)
            ->select('id', 'question')
            ->orderBy('id', 'desc')
            ->get();

        return view('dashboard', compact('polls'));
    }

    public function show($id)
    {
        $poll = Poll::where('is_active', true)
            ->with('options:id,poll_id,option_text')
            ->findOrFail($id);

        return response()->json($poll);
    }

    public function vote(Request $request)
    {
        $request->validate([
            'poll_id' => 'required|exists:polls,id',
            'option_id' => 'required|exists:poll_options,id',
        ]);

        $pollId = $request->input('poll_id');
        $optionId = $request->input('option_id');
        $ipAddress = $request->ip();

        $poll = Poll::where('is_active', true)->findOrFail($pollId);

        $optionBelongsToPoll = PollOption::where('id', $optionId)
            ->where('poll_id', $poll->id)
            ->exists();

        if (! $optionBelongsToPoll) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid option selected for this poll.',
            ], 422);
        }

        $existingVote = Vote::where('poll_id', $pollId)
                            ->where('ip_address', $ipAddress)
                            ->where('status', 'active')
                            ->first();

        if ($existingVote) {
            return response()->json([
                'success' => false,
                'message' => 'You have already voted on this poll. Only one vote per IP is allowed.'
            ], 403);
        }

        Vote::create([
            'poll_id' => $pollId,
            'poll_option_id' => $optionId,
            'ip_address' => $ipAddress,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your vote has been successfully recorded!'
        ]);
    }

    public function results($id)
    {
        $poll = Poll::findOrFail($id);

        $results = DB::table('poll_options')
            ->leftJoin('votes', function($join) {
                $join->on('poll_options.id', '=', 'votes.poll_option_id')
                     ->where('votes.status', '=', 'active');
            })
            ->where('poll_options.poll_id', $poll->id)
            ->select('poll_options.id', 'poll_options.option_text', DB::raw('COUNT(votes.id) as vote_count'))
            ->groupBy('poll_options.id', 'poll_options.option_text')
            ->get();

        $totalVotes = $results->sum('vote_count');

        return response()->json([
            'poll_id' => $poll->id,
            'results' => $results,
            'total_votes' => $totalVotes
        ]);
    }
}
