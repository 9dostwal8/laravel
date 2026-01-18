<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\Progress;

class ProgressObserver
{
    public function saved(Progress $progress): void
    {
        $progress->activities()->update([
            'parent_subject_id' => $progress->project_id,
            'parent_subject_type' => Project::class,
        ]);
    }
}
