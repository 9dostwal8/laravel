<?php

namespace App\Livewire\Projects;

use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use App\Filament\Resources\ProjectResource;
use App\Models\Project;
use App\Traits\LangSwitcher;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Summary extends Component implements HasForms, HasActions
{
    use InteractsWithActions;
    use InteractsWithForms;
    use LangSwitcher;

    public ?array $data = [];

    public Project $record;

    public function mount(Project $project): void
    {
        if ($lang = request()->cookie('filament_language_switch_locale')) {
            app()->setLocale($lang);
        }

        $this->record = $project;

        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        $fields = collect();
        foreach (ProjectResource::form($schema)->getComponents()[0]->getDefaultChildComponents() as $childs) {
            $fields->push(...$childs->getDefaultChildComponents());
        }

        foreach ($fields as $field) {
            $field->disabled();
        }

        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'sm' => 2,
                ])
                    ->schema($fields->all()),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->record->update($data);
    }

    public function render(): View
    {
        return view('livewire.projects.summary');
    }
}
