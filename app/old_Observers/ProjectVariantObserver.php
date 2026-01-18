<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\ProjectVariant;

class ProjectVariantObserver
{
    public function saved(ProjectVariant $projectVariant): void
    {
        $projectVariant->activities()->update([
            'parent_subject_id' => $projectVariant->project_id,
            'parent_subject_type' => Project::class,
        ]);
    }
}
