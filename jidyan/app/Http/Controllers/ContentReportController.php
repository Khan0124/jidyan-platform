<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportStoreRequest;
use App\Http\Requests\ReportUpdateRequest;
use App\Models\ContentReport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentReportController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', ContentReport::class);

        $query = ContentReport::query()
            ->with(['reporter', 'resolver', 'reportable'])
            ->latest();

        $status = $request->string('status')->toString();
        if ($status && in_array($status, [
            ContentReport::STATUS_PENDING,
            ContentReport::STATUS_RESOLVED,
            ContentReport::STATUS_DISMISSED,
        ], true)) {
            $query->where('status', $status);
        }

        $reports = $query->paginate(20)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($reports);
        }

        return view('dashboard.admin.reports.index', [
            'reports' => $reports,
        ]);
    }

    public function store(ReportStoreRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $typeMap = $request->typeMap();
        /** @var class-string<Model> $modelClass */
        $modelClass = $typeMap[$data['reportable_type']];

        /** @var Model|null $reportable */
        $reportable = $modelClass::query()->find($data['reportable_id']);

        abort_unless($reportable, 404);

        $report = ContentReport::firstOrNew([
            'reportable_type' => $modelClass,
            'reportable_id' => $reportable->getKey(),
            'reporter_user_id' => $request->user()->getAuthIdentifier(),
            'status' => ContentReport::STATUS_PENDING,
        ]);

        $report->reason = $data['reason'];
        $report->description = $data['description'] ?? null;
        $report->save();

        if ($request->wantsJson()) {
            $statusCode = $report->wasRecentlyCreated ? 201 : 200;

            return response()->json(['report' => $report->load('reportable')], $statusCode);
        }

        return back()->with('status', __('Report submitted.'));
    }

    public function update(ReportUpdateRequest $request, ContentReport $report): RedirectResponse|JsonResponse
    {
        $this->authorize('review', $report);

        $data = $request->validated();
        $report->status = $data['status'];
        $report->resolution_notes = $data['resolution_notes'] ?? null;

        if ($report->status === ContentReport::STATUS_PENDING) {
            $report->resolved_by = null;
            $report->resolved_at = null;
        } else {
            $report->resolved_by = $request->user()->getAuthIdentifier();
            $report->resolved_at = now();
        }

        $report->save();

        if ($request->wantsJson()) {
            return response()->json(['report' => $report->fresh(['reporter', 'resolver'])]);
        }

        return redirect()->route('reports.index')->with('status', __('Report updated.'));
    }
}
