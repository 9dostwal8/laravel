<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\Note;

class NoteObserver
{
    public function created(Note $note): void
    {
        $this->updateActivities($note);
    }

    public function updated(Note $note): void
    {
        $this->updateActivities($note);
    }

    public function deleted(Note $note): void
    {
        $this->updateActivities($note);
    }

    private function updateActivities(Note $note): void
    {
        $note->activities()->update([
            'parent_subject_id' => $note->project_id,
            'parent_subject_type' => Project::class,
        ]);
    }
}
