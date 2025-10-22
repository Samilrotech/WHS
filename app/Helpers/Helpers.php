<?php

namespace App\Helpers;

class Helpers
{
  public static function getMenuAttributes(bool $semiDarkEnabled = false): array
  {
    return $semiDarkEnabled ? ['data-bs-theme' => 'dark'] : [];
  }

  public static function appClasses(): array
  {
    $config = config('custom.custom', []);

    $layout = $config['layout'] ?? 'vertical';
    $contentLayout = ($config['contentLayout'] ?? 'wide') === 'wide' ? 'container-fluid' : 'container-xxl';
    $menuCollapsed = ($config['menuCollapsed'] ?? false) ? 'layout-menu-collapsed' : '';
    $theme = $config['theme'] ?? 'dark';
    $rtl = $config['rtl'] ?? false;

    return [
      'layout' => $layout,
      'contentLayout' => $contentLayout,
      'menuCollapsed' => $menuCollapsed,
      'navbarType' => 'layout-navbar-fixed',
      'menuFixed' => true,
      'footerFixed' => false,
      'theme' => $theme,
      'themeOpt' => $theme,
      'themeOptVal' => $theme,
      'rtlMode' => $rtl,
      'textDirection' => $rtl ? 'rtl' : 'ltr',
      'hasCustomizer' => false,
      'displayCustomizer' => false,
      'showDropdownOnHover' => false,
      'menuAttributes' => self::getMenuAttributes(false),
      'color' => null,
      'skinName' => 'default',
      'semiDark' => false,
    ];
  }

  public static function updatePageConfig($pageConfigs)
  {
    if (!$pageConfigs || !is_array($pageConfigs)) {
      return;
    }

    foreach ($pageConfigs as $config => $val) {
      config(["custom.custom.$config" => $val]);
    }
  }

  public static function generatePrimaryColorCSS($color)
  {
    return '';
  }
}
