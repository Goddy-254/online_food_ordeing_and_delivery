<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    private const DEFAULTS = [
        'delivery_fee' => '0.00',
        'operating_hours' => 'Every day, 9:00 AM – 9:00 PM',
        'store_open' => '1',
    ];

    /** Public, non-sensitive store details used on customer checkout. */
    public function storeInfo()
    {
        $settings = $this->settings();

        return response()->json([
            'delivery_fee' => (float) $settings['delivery_fee'],
            'operating_hours' => $settings['operating_hours'],
            'store_open' => $settings['store_open'] === '1',
        ]);
    }

    /** Show the current values, including defaults for a fresh install. */
    public function show()
    {
        return response()->json($this->settings());
    }

    /** Save the settings controlled by the admin settings form. */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'delivery_fee' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'operating_hours' => ['required', 'string', 'max:255'],
            'store_open' => ['required', 'boolean'],
        ]);

        foreach ($validated as $key => $value) {
            AppSetting::updateOrCreate(
                ['key' => $key],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value],
            );
        }

        return response()->json([
            'message' => 'Store settings saved.',
            'settings' => $this->settings(),
        ]);
    }

    /** Merge persisted values over safe defaults. */
    private function settings(): array
    {
        return array_merge(self::DEFAULTS, AppSetting::query()
            ->whereIn('key', array_keys(self::DEFAULTS))
            ->pluck('value', 'key')
            ->all());
    }
}
