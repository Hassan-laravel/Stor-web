<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        // اللغات المدعومة
        $availableLocales = ['en', 'ar', 'nl', 'tr'];
        
        // قراءة اللغة من الجلسة، والافتراضي إنجليزي
        $locale = Session::get('locale', 'en');

        if (in_array($locale, $availableLocales)) {
            App::setLocale($locale);
        }

        // تحديد اتجاه الصفحة (RTL للعربية، LTR للباقي) ومشاركته مع جميع واجهات Blade
        $direction = ($locale === 'ar') ? 'rtl' : 'ltr';
        View::share('dir', $direction);
        View::share('current_locale', $locale);

        return $next($request);
    }
}