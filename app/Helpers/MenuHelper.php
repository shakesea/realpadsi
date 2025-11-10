<?php

namespace App\Helpers;

use App\Models\Menu;
use Illuminate\Support\Facades\Cache;

class MenuHelper
{
    public static function getCategories()
    {
        // Cache categories for 1 hour to improve performance
        return Cache::remember('menu_categories', 3600, function () {
            return Menu::select('Kategori')
                ->distinct()
                ->orderBy('Kategori')
                ->pluck('Kategori')
                ->toArray();
        });
    }
}
