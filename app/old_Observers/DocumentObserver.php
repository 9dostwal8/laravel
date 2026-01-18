<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\Document;

class DocumentObserver
{
    public function saved(Document $document): void
    {
        $document->activities()->update([
            'parent_subject_id' => $document->project_id,
            'parent_subject_type' => Project::class,
        ]);
    }
}
