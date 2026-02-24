<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            Action::make('cancel')
                ->label('Cancel')
                ->color('gray')
                ->url(static::getResource()::getUrl('view', ['record' => $this->record])),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', ['record' => $this->record]);
    }
}
