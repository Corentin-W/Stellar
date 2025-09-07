<?php

if (!function_exists('localized_route')) {
    /**
     * Generate a localized route URL
     */
    function localized_route($name, $parameters = [], $locale = null)
    {
        if (!$locale) {
            $locale = app()->getLocale();
        }

        return route($name, array_merge(['locale' => $locale], $parameters));
    }
}

if (!function_exists('current_route_localized')) {
    /**
     * Get current route with different locale
     */
    function current_route_localized($locale)
    {
        $currentRoute = request()->route();
        if (!$currentRoute) {
            return url($locale);
        }

        $routeName = $currentRoute->getName();
        $parameters = $currentRoute->parameters();

        if ($routeName) {
            $parameters['locale'] = $locale;
            return route($routeName, $parameters);
        }

        return url($locale);
    }
}

if (!function_exists('available_locales')) {
    /**
     * Get available locales
     */
    function available_locales()
    {
        return config('app.available_locales', ['fr', 'en']);
    }
}

if (!function_exists('locale_name')) {
    /**
     * Get locale display name
     */
    function locale_name($locale)
    {
        $names = [
            'fr' => 'Français',
            'en' => 'English',
        ];

        return $names[$locale] ?? $locale;
    }
}

if (!function_exists('locale_flag')) {
    /**
     * Get locale flag emoji
     */
    function locale_flag($locale)
    {
        $flags = [
            'fr' => '🇫🇷',
            'en' => '🇬🇧',
        ];

        return $flags[$locale] ?? '🌍';
    }
}
