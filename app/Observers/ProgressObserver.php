<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\Progress;

class ProgressObserver
{
    public function created(Progress $progress): void
    {
        $this->updateActivities($progress);
    }

    public function updated(Progress $progress): void
    {
        $this->updateActivities($progress);
    }

    public function deleted(Progress $progress): void
    {
        $this->updateActivities($progress);
    }

    private function updateActivities(Progress $progress): void
    {
        $progress->activities()->update([
            'parent_subject_id' => $progress->project_id,
            'parent_subject_type' => Project::class,
        ]);
    }
}
