<?php

namespace App\Livewire\Club;

use App\Models\Application;
use App\Models\Opportunity;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class OpportunityPipeline extends Component
{
    use AuthorizesRequests;

    public Opportunity $opportunity;

    public const STAGES = [
        'received',
        'shortlisted',
        'invited',
        'rejected',
        'signed',
    ];

    public function mount(Opportunity $opportunity): void
    {
        $this->opportunity = $opportunity;
        $this->authorize('view', $this->opportunity);
    }

    public function moveTo(int $applicationId, string $stage): void
    {
        abort_unless(in_array($stage, self::STAGES, true), 422);

        /** @var Application $application */
        $application = $this->opportunity->applications()
            ->with('opportunity.club')
            ->findOrFail($applicationId);

        $this->authorize('update', $application);

        $application->forceFill([
            'status' => $stage,
            'reviewed_by_user_id' => auth()->id(),
        ])->save();

        session()->flash('status', __('Application moved to :status.', ['status' => __("applications.status.$stage")]));
    }

    public function render()
    {
        $opportunity = $this->opportunity->fresh([
            'applications.player.user',
        ]);

        $columns = collect(self::STAGES)->mapWithKeys(function (string $stage) use ($opportunity) {
            return [$stage => $opportunity->applications->where('status', $stage)->sortBy('updated_at')];
        });

        return view('livewire.club.opportunity-pipeline', [
            'opportunity' => $opportunity,
            'columns' => $columns,
            'stages' => self::STAGES,
        ]);
    }
}
