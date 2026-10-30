<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\HomeController;
// مسار الصفحة الرئيسية
// Route::get('/', function () {
//     return view('home');
// })->name('home');
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/category/{slug}', [App\Http\Controllers\HomeController::class, 'category'])->name('category.show');
Route::get('/product/{slug}', [\App\Http\Controllers\HomeController::class, 'show'])->name('product.show');

// مسار تبديل اللغة (الذي أضفناه في الخطوة السابقة)
Route::get('/lang/{locale}', function ($locale) {$availableLocales = ['en', 'ar', 'nl', 'tr'];
    if (in_array($locale,$availableLocales)) {
        Session::put('locale', $locale);
    }
    return Redirect::back();
})->name('lang.switch');