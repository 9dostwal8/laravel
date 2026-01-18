<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\Letter;

class LetterObserver
{
    public function created(Letter $letter): void
    {
        $this->updateActivities($letter);
    }

    public function updated(Letter $letter): void
    {
        $this->updateActivities($letter);
    }

    public function deleted(Letter $letter): void
    {
        $this->updateActivities($letter);
    }

    private function updateActivities(Letter $letter): void
    {
        $letter->activities()->update([
            'parent_subject_id' => $letter->project_id,
            'parent_subject_type' => Project::class,
        ]);
    }
}
