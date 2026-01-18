<?php

namespace App\Filament\Pages\Auth;

use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Hash;
use function Filament\Support\is_app_url;

class EditProfile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
//                $this->getNameFormComponent(),
//                $this->getEmailFormComponent(),
                TextInput::make('current_password')
                    ->password()
                    ->required()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->label(trans('resources.current_password')),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    public function save(): void
    {
        try {
            $current_password = $this->form->getState()['current_password'] ?? null;

            if (! Hash::check($current_password, auth()->user()->password)) {
                 Notification::make()
                     ->danger()
                     ->title(trans('resources.current_password_not_valid'))
                     ->send();

                 return;
            }

            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeSave($data);

            $this->callHook('beforeSave');

            $this->handleRecordUpdate($this->getUser(), $data);

            $this->callHook('afterSave');

            $this->commitDatabaseTransaction();
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } catch (\Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        if (request()->hasSession() && array_key_exists('password', $data)) {
            request()->session()->put([
                'password_hash_' . Filament::getAuthGuard() => $data['password'],
            ]);
        }

        $this->data['password'] = null;
        $this->data['passwordConfirmation'] = null;

        $this->getSavedNotification()?->send();

        if ($redirectUrl = $this->getRedirectUrl()) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
        }
    }
}
