<?php

namespace App\Filament\Resources;

use App\Enums\GenderEnum;
use App\Enums\InvestmentTypeEnum;
use App\Filament\Resources\InvestorResource\Pages;
use App\Models\Investor;
use App\Services\TranslatableField;
use App\Traits\LangSwitcher;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class InvestorResource extends Resource
{
    use LangSwitcher;

    protected static ?string $model = Investor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return trans('resources.navigation.manage-projects');
    }

    public static function getModelLabel(): string
    {
        return trans('resources.investor.single');
    }

    public static function getPluralLabel(): ?string
    {
        return trans('resources.investor.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Select::make('type')
                    ->label(trans('resources.investor.type'))
                    ->live()
                    ->required()
                    ->options([
                        1 => trans('resources.investor.person'),
                        2 => trans('resources.investor.company'),
                    ]),

                Hidden::make('user_id')
                    ->label(trans('resources.creator'))
                    ->required()
                    ->formatStateUsing(fn($state) => empty($state) ? auth()->id() : $state )
                    ->default(auth()->id()),

                Section::make(self::personFields($form))
                    ->visible(fn(Get $get) => $get('type') == 1)
                    ->columns(),

                Section::make(self::companyFields($form))
                    ->visible(fn(Get $get) => $get('type') == 2)
                    ->columns(),

                Repeater::make('representatives')
                    ->columnSpanFull()
                    ->label(trans('resources.representatives'))
                    ->relationship()
                    ->addable()
                    ->schema([

                        Fieldset::make(trans('resources.investor.name'))
                            ->schema(TranslatableField::make(ckb: false)),

                        Select::make('occupation')
                            ->label(trans('resources.occupation'))
                            ->options([
                                'lawyer' => trans('resources.lawyer'),
                                'agent' => trans('resources.agent'),
                                'principal' => trans('resources.principal'),
                            ]),

                        TextInput::make('mobile_number')
                            ->label(trans('resources.mobile_number'))
                            ->prefix('+')
                            ->mask('999999999999')
                            ->numeric()
                            ->tel()
                            ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                            ->maxLength(255),


                        Textarea::make('address')
                            ->label(trans('resources.address')),

                        FileUpload::make('identity_doc')
                            ->label(trans('resources.identity_doc'))
                            ->openable()
                            ->safeDefaults()
                            ->maxSize(5122)
                            ->downloadable()
                            ->visibility('private')
                            ->directory('investors-representative-documents'),


                    ])
                    ->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                TextColumn::make('country.name')
                    ->formatStateUsing(fn($record) => self::getTranslation($record?->country->name))
                    ->label(trans('resources.country.single'))
                    ->sortable(),
                TextColumn::make('name')
                    ->label(trans('resources.investor.name'))
                    ->formatStateUsing(fn($record) => self::getTranslation($record->name))
                    ->searchable(true, function (Builder $query, $search) {
                        $query->where('name->ckb', 'like', "%$search%")
                            ->orWhere('name->en', 'like', "%$search%");
                    }),
                TextColumn::make('gender')
                    ->label(trans('resources.investor.gender'))
                    ->badge()
                    ->color(fn(GenderEnum $state): string => $state->getColor()),
                TextColumn::make('nationality')
                    ->label(trans('resources.investor.nationality'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(trans('resources.investor.email'))
                    ->searchable(),
                TextColumn::make('first_phone_number')
                    ->label(trans('resources.investor.first_phone_number')),
                TextColumn::make('type')
                    ->formatStateUsing(fn($state) => $state == 1 ? trans('resources.investor.person') : trans('resources.investor.company'))
                    ->label(trans('resources.investor.type')),
                TextColumn::make('organization.name')
                    ->getStateUsing(fn($record) => self::getTranslation($record?->organization->name))
                    ->label(trans('resources.organization.single')),
                TextColumn::make('user.name')
                    ->label(trans('resources.creator')),

            ])
            ->filters([
                Tables\Filters\Filter::make('my_investors')
                    ->label(trans('resources.see_my_investors'))
                    ->query(function (Builder $query) {
                        $query->where('organization_id', auth()->user()->organization_id);
                    })
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label(trans('resources.export_all'))
                        ->exports([
                        ExcelExport::make()
                            ->withColumns([

                                Column::make('name')
                                    ->formatStateUsing(fn($record) => self::getTranslation($record->name))
                                    ->heading(trans('resources.investor.name')),

                                Column::make('company_name')
                                    ->formatStateUsing(fn($record) => self::getTranslation($record->company_name))
                                    ->heading(trans('resources.company_name')),

                                Column::make('type')
                                    ->formatStateUsing(fn($state) => $state == 1 ? trans('resources.investor.person') : trans('resources.investor.company'))
                                    ->heading(trans('resources.investor.type')),

                                Column::make('country_id')
                                    ->formatStateUsing(fn($record) => self::getTranslation($record->country?->name))
                                    ->heading(trans('resources.country.single')),

                                Column::make('gender')
                                    ->formatStateUsing(fn($state) => $state ? GenderEnum::from($state->value)->getLabel() : null)
                                    ->heading(trans('resources.investor.gender')),

                                Column::make('nationality')
                                    ->heading(trans('resources.investor.nationality')),

                                Column::make('national_code')
                                    ->heading(trans('resources.investor.national_code')),

                                Column::make('email')
                                    ->heading(trans('resources.investor.email')),

                                Column::make('first_phone_number')
                                    ->heading(trans('resources.investor.first_phone_number')),

                                Column::make('second_phone_number')
                                    ->heading(trans('resources.investor.second_phone_number')),

                                Column::make('passport_number')
                                    ->heading(trans('resources.investor.second_phone_number')),

                                Column::make('address')
                                    ->heading(trans('resources.investor.address')),


                        ]),
                    ]),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvestors::route('/'),
            'create' => Pages\CreateInvestor::route('/create'),
            'edit' => Pages\EditInvestor::route('/{record}/edit'),
        ];
    }

    public static function companyFields($form)
    {
        return [
            Fieldset::make(function (Get $get) {
                if ($get('type') == 1) {
                    return trans('resources.investor.name');
                } else {
                    return trans('resources.company_name');
                }
            })
                ->schema(TranslatableField::make(ar: true, en: true)),
            Select::make('country_id')
                ->getOptionLabelFromRecordUsing(fn($record) => self::getTranslation($record->name))
                //   ->createOptionForm(CountryResource::form($form)->getComponents())
                ->relationship('country', 'name')
                ->label(trans('resources.country.single'))
                ->searchable(['name->ckb', 'name->en'])
                ->preload()
                ->required(),
            TextInput::make('license_number')
                ->label(trans('resources.investor.license_number')),
            TextInput::make('email')
                ->label(trans('resources.investor.email'))
                ->email()
                ->maxLength(255),
            TextInput::make('first_phone_number')
                ->label(trans('resources.investor.first_phone_number'))
              //  ->unique(ignoreRecord: true)
                ->prefix('+')
                ->mask('999999999999')
                ->numeric()
                ->tel()
                ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                ->required()
                ->maxLength(255),
            TextInput::make('second_phone_number')
                ->label(trans('resources.investor.second_phone_number'))
                ->prefix('+')
                ->mask('999999999999')
                ->numeric()
                ->tel()
                ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                ->maxLength(255),
            TextInput::make('address')
                ->label(trans('resources.investor.company_address'))
                ->maxLength(255),
            FileUpload::make('company_certificate')
                ->label(trans('resources.investor.company_certificate'))
                ->openable()
                ->safeDefaults()
                ->maxSize(5122)
                ->downloadable()
                ->visibility('private')
                ->directory('investors-documents'),
        ];
    }

    public static function personFields($form)
    {
        return [
            FileUpload::make('avatar')
                ->label(trans('resources.investor.avatar'))
                ->openable()
                ->safeDefaults()
                ->maxSize(1024)
                ->downloadable()
                ->visibility('private')
                ->directory('investors-avatar'),
            Fieldset::make(function (Get $get) {
                if ($get('type') == 1) {
                    return trans('resources.investor.name');
                } else {
                    return trans('resources.investor.company');
                }
            })
                ->schema(TranslatableField::make(ar: true, en: true)),
            Select::make('country_id')
                ->getOptionLabelFromRecordUsing(fn($record) => self::getTranslation($record->name))
                //   ->createOptionForm(CountryResource::form($form)->getComponents())
                ->relationship('country', 'name')
                ->label(trans('resources.country.single'))
                ->searchable(['name->ckb', 'name->en'])
                ->preload()
                ->required(),
            Select::make('gender')
                ->label(trans('resources.investor.gender'))
                ->options(GenderEnum::class)
                ->required(),
            TextInput::make('nationality')
                ->label(trans('resources.investor.nationality'))
                ->required()
                ->maxLength(255),
            TextInput::make('national_code')
                ->label(trans('resources.investor.national_code'))
                //  ->required()
                ->numeric(),
            TextInput::make('email')
                ->label(trans('resources.investor.email'))
                ->email()
                //  ->required()
                ->maxLength(255),
            TextInput::make('first_phone_number')
                ->label(trans('resources.investor.first_phone_number'))
               // ->unique(ignoreRecord: true)
                ->prefix('+')
                ->mask('999999999999')
                ->numeric()
                ->tel()
                ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                ->required()
                ->maxLength(255),
            TextInput::make('second_phone_number')
                ->label(trans('resources.investor.second_phone_number'))
                ->prefix('+')
                ->mask('999999999999')
                ->numeric()
                ->tel()
                ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[-\s\.\/0-9]*$/')
                ->maxLength(255),
            TextInput::make('passport_number')
                ->label(trans('resources.investor.passport_number'))
                //  ->required()
                ->maxLength(255),
            TextInput::make('address')
                ->label(trans('resources.investor.address'))
                ->required()
                ->maxLength(255),
            FileUpload::make('identify_card')
                ->label(trans('resources.investor.identify-card'))
                ->openable()
                ->safeDefaults()
                ->maxSize(5122)
                ->downloadable()
                //  ->required()
                ->visibility('private')
                ->directory('investors-documents'),
            FileUpload::make('national_card')
                ->label(trans('resources.investor.national-card'))
                ->openable()
                ->safeDefaults()
                ->maxSize(5122)
                ->downloadable()
                //  ->required()
                ->visibility('private')
                ->directory('investors-documents'),
            FileUpload::make('passport')
                ->label(trans('resources.investor.passport'))
                ->openable()
                ->safeDefaults()
                ->maxSize(5122)
                ->downloadable()
                ->visibility('private')
                ->directory('investors-documents'),
        ];
    }
}
