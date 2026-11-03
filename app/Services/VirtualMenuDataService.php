<?php

namespace App\Services;

use App\Models\Setting;

class VirtualMenuDataService
{
    public function data(): array
    {
        $menuNames = array_pad(Setting::virtualMenuNames(), 5, '');
        $secondNames = array_pad(Setting::virtualMenuSecondNames(), 5, '');
        $menuPrices = array_pad(Setting::virtualMenuPrices(), 5, 0);

        $menus = [];
        foreach ($menuNames as $index => $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }

            $second = trim((string) ($secondNames[$index] ?? ''));

            $menus[] = [
                'number' => $index + 1,
                'main_name' => $name,
                'second_name' => $second !== '' ? $second : null,
                'price' => (float) ($menuPrices[$index] ?? 0),
            ];
        }

        $seconds = [];
        foreach ($secondNames as $index => $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }

            $menuName = trim((string) ($menuNames[$index] ?? ''));

            $seconds[] = [
                'number' => $index + 1,
                'name' => $name,
                'menu_number' => $menuName !== '' ? $index + 1 : null,
            ];
        }

        return compact('menus', 'seconds');
    }
}
