<?php

namespace App\Rules;

use Illuminate\Translation\PotentiallyTranslatedString;
use App\Models\Progress;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ProgressDate implements ValidationRule
{
    public function __construct(protected $id)
    {

    }


    /**
     * Run the validation rule.
     *
     * @param Closure(string):PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $records = Progress::where('project_id', $this->id)
            ->get();

        foreach ($records as $record) {
            if ($value < $record->visited_at) {
                $fail(trans('resources.progress_visited_validate'));
            }
        }
    }
}
