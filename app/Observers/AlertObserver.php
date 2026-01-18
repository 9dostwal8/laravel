<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\Alert;

class AlertObserver
{
    public function created(Alert $alert): void
    {
        $this->updateActivities($alert);
    }

    public function updated(Alert $alert): void
    {
        $this->updateActivities($alert);
    }

    public function deleted(Alert $alert): void
    {
        $this->updateActivities($alert);
    }

    private function updateActivities(Alert $alert): void
    {
        $alert->activities()->update([
            'parent_subject_id' => $alert->project_id,
            'parent_subject_type' => Project::class,
        ]);
    }
}
