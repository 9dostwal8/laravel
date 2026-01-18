<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\Document;

class DocumentObserver
{
    public function created(Document $document): void
    {
        $this->updateActivities($document);
    }

    public function updated(Document $document): void
    {
        $this->updateActivities($document);
    }

    public function deleted(Document $document): void
    {
        $this->updateActivities($document);
    }

    private function updateActivities(Document $document): void
    {
        $document->activities()->update([
            'parent_subject_id' => $document->project_id,
            'parent_subject_type' => Project::class,
        ]);
    }
}
