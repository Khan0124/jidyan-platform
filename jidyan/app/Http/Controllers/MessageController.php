<?php

namespace App\Http\Controllers;

use App\Models\MessageThread;
use App\Models\User;
use App\Services\Messaging\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function __construct(private readonly ConversationService $conversations)
    {
    }

    public function index(Request $request): View|JsonResponse
    {
        $threads = MessageThread::query()
            ->whereHas('participants', fn ($query) => $query->where('user_id', $request->user()->id))
            ->with([
                'participants.user',
                'messages' => fn ($query) => $query->latest()->limit(20)->with('sender'),
            ])
            ->orderByDesc('updated_at')
            ->paginate(15);

        $threads->getCollection()->each(fn ($thread) => $this->conversations->markThreadRead($thread, $request->user()));

        if ($request->wantsJson()) {
            return $threads;
        }

        return view('dashboard.messages.index', compact('threads'));
    }

    public function start(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'recipient_id' => ['nullable', 'required_without:recipient_email', 'integer', 'exists:users,id'],
            'recipient_email' => ['nullable', 'required_without:recipient_id', 'email', 'exists:users,email'],
            'subject' => ['nullable', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        /** @var User $actor */
        $actor = $request->user();

        $recipient = $this->resolveRecipient($validated);

        abort_if($recipient->is($actor), 422, __('You cannot start a conversation with yourself.'));

        [$thread, $created] = $this->conversations->findOrCreateThread($actor, $recipient, $validated['subject'] ?? null);

        $message = $thread->messages()->create([
            'sender_user_id' => $actor->getAuthIdentifier(),
            'body' => $validated['body'],
        ]);

        $thread->touch();
        $this->conversations->markThreadRead($thread, $actor);

        $message->load('sender');
        $thread->loadMissing(['participants.user']);

        if ($request->wantsJson()) {
            return response()->json([
                'thread' => $thread,
                'message' => $message,
            ], $created ? 201 : 200);
        }

        return redirect()
            ->route('dashboard.messages.index')
            ->with('status', $created ? __('Conversation started.') : __('Message sent.'));
    }

    public function store(Request $request, MessageThread $thread): RedirectResponse|JsonResponse
    {
        abort_unless($thread->participants()->where('user_id', $request->user()->id)->exists(), 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = $thread->messages()->create([
            'sender_user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        $thread->touch();
        $this->conversations->markThreadRead($thread, $request->user());
        $message->load('sender');

        if ($request->wantsJson()) {
            return response()->json(['message' => $message], 201);
        }

        return back()->with('status', __('Message sent.'));
    }

    protected function resolveRecipient(array $validated): User
    {
        if (! empty($validated['recipient_id'])) {
            return User::findOrFail($validated['recipient_id']);
        }

        return User::where('email', $validated['recipient_email'])->firstOrFail();
    }
}
