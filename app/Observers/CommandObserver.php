<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\Command;

class CommandObserver
{
    public function created(Command $command): void
    {
        $this->updateActivities($command);
    }

    public function updated(Command $command): void
    {
        $this->updateActivities($command);
    }

    public function deleted(Command $command): void
    {
        $this->updateActivities($command);
    }

    private function updateActivities(Command $command): void
    {
        $command->activities()->update([
            'parent_subject_id' => $command->project_id,
            'parent_subject_type' => Project::class,
        ]);
    }
}
