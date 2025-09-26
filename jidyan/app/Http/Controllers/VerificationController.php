<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerificationRequest;
use App\Models\Verification;
use App\Notifications\VerificationStatusUpdated;
use App\Services\Verification\VerificationStatusSynchronizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VerificationController extends Controller
{
    public function __construct(private readonly VerificationStatusSynchronizer $synchronizer)
    {
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        return view('dashboard.player.verification', [
            'player' => $user->playerProfile,
            'verifications' => $user->verifications()->latest()->take(10)->get(),
        ]);
    }

    public function index(): View|JsonResponse
    {
        $verifications = Verification::query()->with('user')->latest()->paginate(20);

        if (request()->wantsJson()) {
            return response()->json($verifications);
        }

        return view('dashboard.admin.verifications.index', compact('verifications'));
    }

    public function store(VerificationRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $disk = config('filesystems.verification_disk', 'secure_documents');

        $document = $request->file('document');
        $path = $document->store((string) $request->user()->getAuthIdentifier(), [
            'disk' => $disk,
        ]);

        $verification = $request->user()->verifications()->create([
            'type' => $data['type'],
            'document_path' => $path,
            'document_name' => $document->getClientOriginalName() ?: basename($path),
            'status' => 'pending',
        ]);

        if ($request->wantsJson()) {
            return response()->json(['verification' => $verification], 201);
        }

        return back()->with('status', __('Verification request submitted.'));
    }

    public function update(VerificationRequest $request, Verification $verification): RedirectResponse|JsonResponse
    {
        $this->authorize('verify', $verification);

        $verification->fill($request->validated());
        $verification->reviewed_by = $request->user()->id;
        $verification->reviewed_at = now();
        $verification->save();

        $this->synchronizer->sync($verification);

        if ($verification->wasChanged('status')) {
            $verification->user->notify(new VerificationStatusUpdated($verification));
        }

        if ($request->wantsJson()) {
            return response()->json(['verification' => $verification]);
        }

        return back()->with('status', __('Verification updated.'));
    }

    public function download(Verification $verification): StreamedResponse
    {
        $this->authorize('verify', $verification);

        $disk = config('filesystems.verification_disk', 'secure_documents');

        if (! Storage::disk($disk)->exists($verification->document_path)) {
            abort(404);
        }

        $filename = $verification->document_name ?: basename($verification->document_path);

        return Storage::disk($disk)->download($verification->document_path, $filename);
    }
}
