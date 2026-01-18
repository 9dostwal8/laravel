<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\Letter;

class LetterObserver
{
    public function saved(Letter $letter): void
    {
        $letter->activities()->update([
            'parent_subject_id' => $letter->project_id,
            'parent_subject_type' => Project::class,
        ]);
    }
}
