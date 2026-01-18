<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\InvestorProject;

class InvestorProjectObserver
{
    public function created(InvestorProject $investorProject): void
    {
        $this->updateActivities($investorProject);
    }

    public function updated(InvestorProject $investorProject): void
    {
        $this->updateActivities($investorProject);
    }

    public function deleted(InvestorProject $investorProject): void
    {
        $this->updateActivities($investorProject);
    }

    private function updateActivities(InvestorProject $investorProject): void
    {
        $investorProject->activities()->update([
            'parent_subject_id' => $investorProject->project_id,
            'parent_subject_type' => Project::class,
        ]);
    }
}
