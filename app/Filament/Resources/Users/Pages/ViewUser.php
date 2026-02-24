<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('close')
                ->label('Close')
                ->color('gray')
                ->url(static::getResource()::getUrl('index')),
        ];
    }
}
