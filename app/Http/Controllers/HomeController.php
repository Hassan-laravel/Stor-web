<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $baseUrl = env('API_BASE_URL');
        $headers = ['Accept-Language' => app()->getLocale()];

        $categories = Http::withHeaders($headers)->get("{$baseUrl}/categories")->json('data') ?? [];
        $stores = Http::withHeaders($headers)->get("{$baseUrl}/stores")->json('data') ?? [];

        // جلب الصفحات الثابتة من الـ API
        // $posts = Http::withHeaders($headers)->get("{$baseUrl}/posts", ['type' => 'article'])->json('data') ?? [];
        $pages = Http::withHeaders($headers)->get("{$baseUrl}/posts", ['type' => 'page'])->json('data') ?? [];
        $productsResponse = Http::withHeaders($headers)->get("{$baseUrl}/products", $request->all());
        $products = $productsResponse->json('data') ?? [];

        return view('home', compact('categories', 'stores', 'products', 'pages'));
    }

    public function show($slug)
    {
        $decodedSlug = urldecode($slug);
        $baseUrl = env('API_BASE_URL');
        $headers = ['Accept-Language' => app()->getLocale()];

        $categories = Http::withHeaders($headers)->get("{$baseUrl}/categories")->json('data') ?? [];

        // جلب الصفحات الثابتة للفوتر والهيدر
        $pages = Http::withHeaders($headers)->get("{$baseUrl}/posts", ['type' => 'page'])->json('data') ?? [];


        $response = Http::withHeaders($headers)->get("{$baseUrl}/products/{$decodedSlug}");

        if (!$response->successful()) {
            abort(404);
        }

        $product = $response->json('data');
        $localizedUrls = [];
        if (isset($product['translations']) && is_array($product['translations'])) {
            foreach ($product['translations'] as $translation) {
                if (isset($translation['locale']) && isset($translation['slug'])) {
                    $localizedUrls[$translation['locale']] = route('product.show', $translation['slug']);
                }
            }
        }
// جلب المنتجات المشابهة (نفس التصنيف)
        $relatedProducts = collect();
        $categoryId = $product['category']['id'] ?? null;
        
        if ($categoryId) {
            // جلب جميع المنتجات لفلترتها
            $productsResponse = Http::withHeaders($headers)->get("{$baseUrl}/products");
            $allProducts = $productsResponse->json('data') ?? [];

            $relatedProducts = collect($allProducts)->filter(function ($p) use ($categoryId, $product) {
                // يجب أن تكون من نفس التصنيف ولا تساوي المنتج الحالي
                $isSameCategory = isset($p['category']['id']) && $p['category']['id'] == $categoryId;
                $isNotCurrentProduct = $p['id'] != $product['id'];
                return $isSameCategory && $isNotCurrentProduct;
            })->take(30); // أخذ 30 منتج كحد أقصى
        }
        return view('product', compact('product', 'categories', 'pages', 'localizedUrls', 'relatedProducts'));
    }
    public function category($slug, Request $request)
    {
        $decodedSlug = urldecode($slug);
        $baseUrl = env('API_BASE_URL', 'http://127.0.0.1:8000/api/v1');
        $headers = ['Accept-Language' => app()->getLocale()];

        $categories = Http::withHeaders($headers)->get("{$baseUrl}/categories")->json('data') ?? [];
        $pages = Http::withHeaders($headers)->get("{$baseUrl}/posts", ['type' => 'page'])->json('data') ?? [];

        $currentCategory = null;
        $categoryIds = [];

        foreach ($categories as $cat) {
            if (isset($cat['slug']) && $cat['slug'] == $decodedSlug) {
                $currentCategory = $cat;
                $categoryIds[] = $cat['id'];
                if (isset($cat['children']) && is_array($cat['children'])) {
                    foreach ($cat['children'] as $child) {
                        if (isset($child['id'])) $categoryIds[] = $child['id'];
                    }
                }
                break;
            }

            if (isset($cat['children']) && is_array($cat['children'])) {
                foreach ($cat['children'] as $child) {
                    if (isset($child['slug']) && $child['slug'] == $decodedSlug) {
                        $currentCategory = $child;
                        $categoryIds[] = $child['id'];
                        break 2;
                    }
                }
            }
        }

        if (!$currentCategory) {
            abort(404);
        }

        $localizedUrls = [];
        if (isset($currentCategory['translations']) && is_array($currentCategory['translations'])) {
            foreach ($currentCategory['translations'] as $translation) {
                if (isset($translation['locale']) && isset($translation['slug'])) {
                    $localizedUrls[$translation['locale']] = route('category.show', $translation['slug']);
                }
            }
        }

        // جلب المنتجات وتطبيق الفلاتر الجديدة
        $productsResponse = Http::withHeaders($headers)->get("{$baseUrl}/products", $request->all());
        $allProducts = $productsResponse->json('data') ?? [];
        $products = collect($allProducts)->filter(function ($product) use ($categoryIds, $request) {
            $matchCategory = isset($product['category']['id']) && in_array($product['category']['id'], $categoryIds);

            $matchName = true;
            if ($request->filled('search_name')) {
                $matchName = stripos($product['title'] ?? '', $request->search_name) !== false;
            }

            $matchMinPrice = true;
            if ($request->filled('min_price')) {
                $matchMinPrice = ($product['price'] ?? 0) >= $request->min_price;
            }

            $matchMaxPrice = true;
            if ($request->filled('max_price')) {
                $matchMaxPrice = ($product['price'] ?? 0) <= $request->max_price;
            }

            return $matchCategory && $matchName && $matchMinPrice && $matchMaxPrice;
        });
        // 2. الترتيب حسب الأعلى تقييماً (تنازلياً)
        if ($request->top_rated == '1') {
            $products = $products->sortByDesc('rating');
        }

        // 3. الترتيب حسب الأكثر مبيعاً (تنازلياً)
        if ($request->best_sellers == '1') {
            $products = $products->sortByDesc(function ($product) {
                return $product['purchases'] ?? $product['reviews_count'] ?? 0;
            });
        }
        return view('category', compact('currentCategory', 'products', 'categories', 'pages', 'localizedUrls'));
    }
}
