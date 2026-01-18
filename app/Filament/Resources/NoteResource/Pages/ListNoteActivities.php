<?php

namespace App\Filament\Resources\NoteResource\Pages;

use Throwable;
use App\Helpers\SaveTemporaryFields;
use App\Traits\LangSwitcher;
use Carbon\Carbon;
use DateTime;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;
use App\Filament\Resources\NoteResource;
use App\Models\Note;
use Filament\Forms\Form;
use Livewire\WithPagination;

class ListNoteActivities extends ListActivities
{
    use LangSwitcher, InteractsWithForms, WithPagination;

    protected ?array $fields = [];

    public ?array $data = [];

    protected static string $resource = NoteResource::class;

    protected static string $view = 'filament.list-activities';

    public function form(Form $form): Form
    {
        $this->fields = (new NoteResource)
            ->form($form)
            ->model($this->record)
            // ->getFlatComponentsByKey(true);
            ->getComponents();

        SaveTemporaryFields::setFields($this->getAllFieldsWithChildren($this->fields));
        return $form->schema([])
            ->statePath('data');
    }

    public function getActivities()
    {
        return $this->paginateTableQuery(
            $this->record->activities()
                ->orWhere(function (Builder $query) {
                    $query->where('parent_subject_type', Note::class)
                        ->where('parent_subject_id', $this->record->id);
                })
                ->with('causer', 'command')->latest()->getQuery()
        );
    }

    protected function createFieldLabelMap(): Collection
    {
        $form = static::getResource()::form(new Form($this));

        $components = collect($form->getComponents());
        $extracted = collect();

        while (($component = $components->shift()) !== null) {
            if ($component instanceof Field || $component instanceof MorphToSelect) {
                $extracted->push($component);
                continue;
            }

            $children = $component->getChildComponents();

            if (count($children) > 0) {
                $components = $components->merge($children);

                continue;
            }

            $extracted->push($component);
        }

        return $extracted;

        return $extracted
            ->filter(fn ($field) => $field instanceof Field)
            ->mapWithKeys(fn (Field $field) => [
                $field->getName() => $field->getLabel(),
            ]);
    }

    public function isValidDate($date, $format = 'Y-m-d H:i:s') {
        $dateTime = DateTime::createFromFormat($format, $date);
        return $dateTime && $dateTime->format($format) === $date;
    }

    public function formatDate($date, $format = 'd-m-Y')
    {
        return Carbon::parse($date)->format($format);
    }

    private function getAllFieldsWithChildren($components)
    {
        $fields = [];

        foreach ($components as $child) {
            if (method_exists($child, 'getChildComponents') && $child->getChildComponents()) {
                $fields = array_merge($fields, $this->getAllFieldsWithChildren($child->getChildComponents()));
            } elseif ($child instanceof Field) {
                try {
                    $fields[$child->getName()] = $child->getLabel() ?? null;
                } catch (Throwable $e) {
                    continue;
                }
            }
        }

        return $fields;
    }
}
