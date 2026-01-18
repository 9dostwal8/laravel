<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\ProjectVariant;

class ProjectVariantObserver
{
    public function created(ProjectVariant $projectVariant): void
    {
        $this->updateActivities($projectVariant);
    }

    public function updated(ProjectVariant $projectVariant): void
    {
        $this->updateActivities($projectVariant);
    }

    public function deleted(ProjectVariant $projectVariant): void
    {
        $this->updateActivities($projectVariant);
    }

    private function updateActivities(ProjectVariant $projectVariant): void
    {
        $projectVariant->activities()->update([
            'parent_subject_id' => $projectVariant->project_id,
            'parent_subject_type' => Project::class,
        ]);
    }
}
