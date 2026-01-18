<?php

use Illuminate\Support\HtmlString;
use App\Services\TranslatableField;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Fieldset;


function ldo_fields($directory = null): array
{
    return [
        Fieldset::make(trans('resources.subject'))
            ->schema(TranslatableField::make(name: 'subject', ar: true, en: true)),

        TextInput::make('number')
            ->required()
            ->numeric()
            ->label(trans('resources.number')),

        DatePicker::make('date')
            ->native(false)
            ->required()
            ->format('Y/m/d')
            ->displayFormat('Y/m/d')
            ->label(trans('resources.date')),

        FileUpload::make('attachment')
            ->label(trans('resources.attachment'))
            ->openable()
            ->maxSize(51200)
            ->downloadable()
            ->visibility('private')
            ->required()
            ->directory("{$directory}-doc"),
    ];
}

function ldo_columns($directory = null): array
{
    return [
        TextColumn::make('subject.' . app()->getLocale())
            ->label(trans('resources.subject'))
            ->searchable(),

        TextColumn::make('number')
            ->label(trans('resources.number'))
            ->numeric(locale: 'en')
            ->searchable(),

        TextColumn::make('attachment')
            ->disabledClick()
            ->state(function ($record) use ($directory) {
                $link = ("/storage/$record->attachment");
                $text = trans('resources.see_attachment');

                return new HtmlString(
                    '<a class="fi-color-primary" target="_blank"  href="' . $link . '"><b>' . $text . '</b></a>'
                );
            })
            ->label(trans('resources.attachment')),

        TextColumn::make('date')
            ->label(trans('resources.date'))
            ->date('Y/m/d'),
    ];
}

function get_user_sectors()
{
    return auth()->user()->activity_area_limit ?? [];
}

function smart_number($number) {
    return rtrim(rtrim(number_format($number, 8, '.', ','), '0'), '.');
}