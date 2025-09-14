<?php
// app/Http/Controllers/LocaleController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    public function change(Request $request, $newLocale)
    {
        if (in_array($newLocale, config('app.available_locales', ['fr', 'en']))) {
            Session::put('locale', $newLocale);
        }

        // IMPORTANT : Rediriger vers une URL avec la nouvelle locale
        $currentUrl = $request->headers->get('referer');

        if ($currentUrl) {
            // Remplacer l'ancienne locale par la nouvelle dans l'URL
            $parsedUrl = parse_url($currentUrl);
            $path = trim($parsedUrl['path'] ?? '', '/');
            $segments = explode('/', $path);

            // Si le premier segment est une locale, le remplacer
            if (in_array($segments[0] ?? '', config('app.available_locales', ['fr', 'en']))) {
                $segments[0] = $newLocale;
            } else {
                // Sinon, ajouter la nouvelle locale au début
                array_unshift($segments, $newLocale);
            }

            $newPath = '/' . implode('/', array_filter($segments));
            return redirect($newPath);
        }

        // Fallback : rediriger vers le dashboard avec la nouvelle locale
        return redirect('/' . $newLocale . '/dashboard');
    }
}
