<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\Alert;

class AlertObserver
{
    public function saved(Alert $alert): void
    {
        $alert->activities()->update([
            'parent_subject_id' => $alert->project_id,
            'parent_subject_type' => Project::class,
        ]);
    }
}
