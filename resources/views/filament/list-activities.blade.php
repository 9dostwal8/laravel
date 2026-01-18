@php
    use App\Filament\Resources\CommandResource;
    use App\Models\ProjectVariant;
    use \Illuminate\Support\Js;
    $fields = \App\Helpers\SaveTemporaryFields::getFields();
@endphp
<x-filament-panels::page>

    {{$this->form}}

    @php
        $fields = \App\Helpers\SaveTemporaryFields::getFields();
    @endphp
    <div class="space-y-6">
        @foreach($this->getActivities() as $activityItem)
            <div @class([
                'p-2 space-y-2 bg-white rounded-xl shadow-sm',
                'dark:border-gray-600 dark:bg-gray-800',
            ])>
                <div class="p-2">
                    <div class="flex justify-between">
                        <div class="flex items-center gap-4">
                            @if ($activityItem->causer)
                                <x-filament-panels::avatar.user :user="$activityItem->causer" class="w-7! h-7!"/>
                            @endif
                            <div class="flex flex-col ltr:text-left rtl:text-right">
                                <span class="font-bold">{{ $activityItem->causer?->name }}</span>
                                <span class="text-xs text-gray-500">
                                    @lang('filament-activity-log::activities.events.' . $activityItem->event) {{ $activityItem->created_at->format(__('filament-activity-log::activities.default_datetime_format')) }}
                                </span>
                            </div>
                        </div>
                        <div class="flex flex-col text-xs text-gray-500 justify-end">
                            @if (static::getResource()::canRestore($record))
                                <x-filament::button
                                    tag="button"
                                    icon="heroicon-o-arrow-path-rounded-square"
                                    labeled-from="sm"
                                    color="gray"
                                    class="right"
                                    wire:click="restoreActivity({{ Js::from($activityItem->getKey()) }})"
                                >
                                    @lang('filament-activity-log::activities.table.restore')
                                </x-filament::button>
                            @endif
                        </div>
                    </div>
                </div>

                @php
                    /* @var \Spatie\Activitylog\Models\Activity $activityItem */
                    $changes = $activityItem->getChangesAttribute();
                    $isProjectVariant = $activityItem->subject_type === ProjectVariant::class;
                @endphp

                <div class="py-2 bg-gray-50 dark:bg-white/5 rounded-lg">
                    <div class="p-3">
                        <span
                            class="font-bold">{{ trans('resources.activity-log.description') }}:</span> {{ $activityItem->description }}
                    </div>
                    @if($activityItem?->command)
                        <div class="p-3">
                            <span class="font-bold">{{ trans('resources.activity-log.commander') }}:</span>
                            <a
                                href="{{ CommandResource::getUrl('edit', ['record' => $activityItem?->command_id]) }}"
                                class="text-primary-600"
                            >
                                {{ $activityItem?->command?->subject }}
                            </a>
                        </div>
                    @endif
                    @if($isProjectVariant)
                        <div class="p-3">
                            <span class="font-bold">{{ trans('resources.activity-log.is-project-variant') }}:</span>
                            Yes
                        </div>
                    @endif
                </div>

                <x-filament-tables::table class="w-full overflow-hidden text-sm">
                    <x-slot:header>
                        <x-filament-tables::header-cell>
                            @lang('filament-activity-log::activities.table.field')
                        </x-filament-tables::header-cell>
                        <x-filament-tables::header-cell>
                            @lang('filament-activity-log::activities.table.old')
                        </x-filament-tables::header-cell>
                        <x-filament-tables::header-cell>
                            @lang('filament-activity-log::activities.table.new')
                        </x-filament-tables::header-cell>
                    </x-slot:header>
                    @foreach(data_get($changes, 'attributes', []) as $field => $change)
                        @php
                            $oldValue = data_get($changes, "old.{$field}");
                            if (str_contains($field, 'id')) {
                                $fieldRelationName = str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
                                $fieldRelationName = str_replace('Id', '', $fieldRelationName);
                                $fieldRelationName = lcfirst($fieldRelationName);
                                $fieldRelationName = sprintf('App\Models\%s', $fieldRelationName);
                                try {
                                    if ($fieldRelationName) {
                                        $subjectModel = (new $fieldRelationName)->find($oldValue);

                                        if ($subjectModel->name) {
                                            if (is_array($subjectModel->name)) {
                                                $oldValue = self::getTranslation($subjectModel->name);
                                            }
                                        } elseif ($subjectModel->project_name) {
                                            $oldValue = self::getTranslation($subjectModel->project_name);
                                        }
                                    }
                                } catch (Throwable $e) {
                                    continue;
                                }
                            }

                            if (is_numeric($oldValue) && str_contains($oldValue, '.')) {
                                $oldValue = number_format($oldValue, 2);
                            }

                            if (is_string($oldValue) && $this->isValidDate($oldValue)) {
                                $oldValue = $this->formatDate($oldValue);
                            }

                            $newValue = data_get($changes, "attributes.{$field}");
                            if (str_contains($field, 'id')) {
                                $fieldRelationName = str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
                                $fieldRelationName = str_replace('Id', '', $fieldRelationName);
                                $fieldRelationName = lcfirst($fieldRelationName);
                                $fieldRelationName = sprintf('App\Models\%s', $fieldRelationName);
                                try {
                                    if ($fieldRelationName) {
                                        $subjectModel = (new $fieldRelationName)->find($newValue);

                                        if ($subjectModel->name) {
                                            if (is_array($subjectModel->name)) {
                                                $newValue = self::getTranslation($subjectModel->name);
                                            }
                                        } elseif ($subjectModel->project_name) {
                                            $newValue = self::getTranslation($subjectModel->project_name);
                                        }
                                    }
                                } catch (Throwable $e) {
                                    continue;
                                }
                            }

                            if (is_numeric($newValue) && str_contains($newValue, '.')) {
                                $newValue = number_format($newValue, 2);
                            }

                            if (is_string($newValue) && $this->isValidDate($newValue)) {
                                $newValue = $this->formatDate($newValue);
                            }
                        @endphp
                        <x-filament-tables::row @class(['bg-gray-100/30' => $loop->even])>
                            <x-filament-tables::cell width="20%"
                                                     class="px-4 py-2 align-top sm:first-of-type:ps-6 sm:last-of-type:pe-6">

                                {{isset(\App\Helpers\SaveTemporaryFields::getFields()[$field]) ? \App\Helpers\SaveTemporaryFields::getFields()[$field] : $this->getFieldLabel($field) }}
                            </x-filament-tables::cell>
                            <x-filament-tables::cell width="40%"
                                                     class="px-4 py-2 align-top break-all whitespace-normal!">
                                @if(is_array($oldValue))
                                    @if(array_key_exists('ckb', $oldValue))
                                        @foreach($oldValue as $key => $value)
                                            <pre
                                                class="text-xs text-gray-500"> <span class="font-bold">{{ $key }}</span>: {{ $value }}</pre>
                                        @endforeach
                                    @else
                                        <pre
                                            class="text-xs text-gray-500">{{ json_encode($oldValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                    @endif
                                @else
                                    {{ $oldValue }}
                                @endif
                            </x-filament-tables::cell>
                            <x-filament-tables::cell width="40%"
                                                     class="px-4 py-2 align-top break-all whitespace-normal!">
                                @if(is_array($newValue))
                                    @foreach($newValue as $key => $value)
                                        <pre
                                            class="text-xs text-gray-500"> <span class="font-bold">{{ $key }}</span>: {{ $value }}</pre>
                                    @endforeach
                                @else
                                    {{ $newValue }}
                                @endif
                            </x-filament-tables::cell>
                        </x-filament-tables::row>
                    @endforeach
                </x-filament-tables::table>
            </div>
        @endforeach

        <x-filament::pagination
            :page-options="$this->getTableRecordsPerPageSelectOptions()"
            :paginator="$this->getActivities()"
            class="px-3 py-3 sm:px-6"
        />
    </div>
</x-filament-panels::page>
