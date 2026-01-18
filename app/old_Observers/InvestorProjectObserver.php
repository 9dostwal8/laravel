<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\InvestorProject;

class InvestorProjectObserver
{
    public function saved(InvestorProject $investorProject): void
    {
        $investorProject->activities()->update([
            'parent_subject_id' => $investorProject->project_id,
            'parent_subject_type' => Project::class,
        ]);
    }
}
