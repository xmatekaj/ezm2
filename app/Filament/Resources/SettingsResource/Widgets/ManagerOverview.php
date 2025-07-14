<?php

namespace App\Filament\Resources\SettingsResource\Widgets;

use App\Models\Setting;
use Filament\Widgets\Widget;

class ManagerOverview extends Widget
{
    protected static string $view = 'filament.resources.settings-resource.widgets.manager-overview';

    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $managerData = Setting::getManagerData();
        $isInitialized = Setting::get('app_initialized', false);

        return [
            'manager_data' => $managerData,
            'is_initialized' => $isInitialized,
            'is_configured' => !empty($managerData['name']) && !empty($managerData['address_street']),
        ];
    }
}