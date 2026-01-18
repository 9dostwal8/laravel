<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\Note;

class NoteObserver
{
    public function saved(Note $note): void
    {
        $note->activities()->update([
            'parent_subject_id' => $note->project_id,
            'parent_subject_type' => Project::class,
        ]);
    }
}
