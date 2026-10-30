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
Route::get('/lang/{locale}', function ($locale, \Illuminate\Http\Request $request) {
    // حفظ اللغة الجديدة في الجلسة
    session()->put('locale', $locale);
    app()->setLocale($locale);

    // إذا كان هناك رابط توجيه مخصص (مثل رابط التصنيف المترجم)، انتقل إليه
    if ($request->has('redirect_to') && !empty($request->redirect_to)) {
        return redirect($request->redirect_to);
    }

    // وإلا، قم بإعادة تحميل الصفحة الحالية (الوضع الافتراضي)
    return back();
})->name('lang.switch');