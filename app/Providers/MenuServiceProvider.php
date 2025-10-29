<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
        $verticalMenuData = json_decode($verticalMenuJson);
        $horizontalMenuJson = file_get_contents(base_path('resources/menu/horizontalMenu.json'));
        $horizontalMenuData = json_decode($horizontalMenuJson);

        View::composer('*', function ($view) use ($verticalMenuData, $horizontalMenuData) {
            // Clone the decoded JSON structures so we can safely mutate per request
            $vertical = json_decode(json_encode($verticalMenuData));
            $horizontal = json_decode(json_encode($horizontalMenuData));

            if (auth()->check()) {
                [$vertical, $horizontal] = $this->filterMenusForUser($vertical, $horizontal, auth()->user());
            }

            $view->with('menuData', [$vertical, $horizontal]);
        });
    }

    /**
     * Apply role-aware filtering for menu collections.
     */
    protected function filterMenusForUser($verticalMenu, $horizontalMenu, $user): array
    {
        if (!$user->hasRole('Employee')) {
            return [$verticalMenu, $horizontalMenu];
        }

        $allowedSlugs = [
            'dashboard-analytics',
            'whs4-incidents',
            'driver.vehicle-inspections',
        ];

        $allowedUrls = [
            '/',
            'incidents',
            'my-vehicle-inspection',
        ];

        $verticalMenu->menu = $this->filterMenuItems($verticalMenu->menu ?? [], $allowedSlugs, $allowedUrls);

        // Hide the horizontal launcher for frontline employees
        $horizontalMenu->menu = [];

        return [$verticalMenu, $horizontalMenu];
    }

    /**
     * Recursively filter menu items by allowed slugs/urls.
     *
     * @param  array<int, object>  $items
     * @return array<int, object>
     */
    protected function filterMenuItems(array $items, array $allowedSlugs, array $allowedUrls): array
    {
        $filtered = [];

        foreach ($items as $item) {
            if (isset($item->menuHeader)) {
                $filtered[] = $item;
                continue;
            }

            $keep = false;

            if (isset($item->slug) && in_array($item->slug, $allowedSlugs, true)) {
                $keep = true;
            }

            if (isset($item->url) && in_array($item->url, $allowedUrls, true)) {
                $keep = true;
            }

            if (!empty($item->submenu)) {
                $item->submenu = $this->filterMenuItems($item->submenu, $allowedSlugs, $allowedUrls);
                if (!empty($item->submenu)) {
                    $keep = true;
                }
            }

            if ($keep) {
                $filtered[] = $item;
            }
        }

        // Remove headers that no longer precede an item.
        $final = [];
        foreach ($filtered as $index => $item) {
            if (!isset($item->menuHeader)) {
                $final[] = $item;
                continue;
            }

            $hasFollowingItem = false;
            for ($i = $index + 1, $count = count($filtered); $i < $count; $i++) {
                if (!isset($filtered[$i]->menuHeader)) {
                    $hasFollowingItem = true;
                    break;
                }
            }

            if ($hasFollowingItem) {
                $final[] = $item;
            }
        }

        return array_values($final);
    }
}
