<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\Command;

class CommandObserver
{
    public function saved(Command $command): void
    {
        $command->activities()->update([
            'parent_subject_id' => $command->project_id,
            'parent_subject_type' => Project::class,
        ]);
    }
}
