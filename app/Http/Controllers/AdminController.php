<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Vote;

class AdminController extends Controller
{
    public function index()
    {
        $polls = Poll::with(['votes.option', 'options'])
            ->orderBy('id', 'desc')
            ->get();

        return view('admin', compact('polls'));
    }

    public function storePoll(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|max:255',
        ]);

        $poll = Poll::create([
            'question' => trim($request->input('question')),
            'is_active' => $request->input('status') === 'active',
        ]);

        foreach ($request->input('options') as $optionText) {
            PollOption::create([
                'poll_id' => $poll->id,
                'option_text' => trim($optionText),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Poll created successfully.',
        ]);
    }

    public function releaseIp(Request $request)
    {
        $request->validate([
            'poll_id' => 'required|exists:polls,id',
            'ip_address' => 'required|string',
        ]);

        $updated = Vote::where('poll_id', $request->input('poll_id'))
            ->where('ip_address', $request->input('ip_address'))
            ->where('status', 'active')
            ->update(['status' => 'released']);

        if ($updated === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No active vote found for this IP in the selected poll.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'IP released successfully. They can now vote again.'
        ]);
    }

    public function history($poll_id, $ip_address)
    {
        $history = Vote::where('poll_id', $poll_id)
            ->where('ip_address', $ip_address)
            ->with('option')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'history' => $history
        ]);
    }
}
