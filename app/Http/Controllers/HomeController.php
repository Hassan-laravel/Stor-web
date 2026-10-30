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
        $baseUrl = env('API_BASE_URL');
        $headers = ['Accept-Language' => app()->getLocale()];

        $categories = Http::withHeaders($headers)->get("{$baseUrl}/categories")->json('data') ?? [];
        
        // جلب الصفحات الثابتة للفوتر والهيدر
        $pages = Http::withHeaders($headers)->get("{$baseUrl}/posts", ['type' => 'page'])->json('data') ?? [];

        $response = Http::withHeaders($headers)->get("{$baseUrl}/products/{$slug}");
        
        if (!$response->successful()) {
            abort(404);
        }

        $product = $response->json('data');

        return view('product', compact('product', 'categories', 'pages'));
    }
  public function category($slug, Request $request)
    {
        // 1. فك تشفير الرابط (خطوة هامة جداً لعمل الروابط العربية)
        $decodedSlug = urldecode($slug);

        $baseUrl = env('API_BASE_URL', 'http://127.0.0.1:8000/api/v1');
        $headers = ['Accept-Language' => app()->getLocale()];

        // جلب البيانات الأساسية
        $categories = Http::withHeaders($headers)->get("{$baseUrl}/categories")->json('data') ?? [];
        $pages = Http::withHeaders($headers)->get("{$baseUrl}/posts", ['type' => 'page'])->json('data') ?? [];

        $currentCategory = null;
        $categoryIds = [];

        foreach ($categories as $cat) {
            // 2. استخدام $decodedSlug بدلاً من $slug
            if (isset($cat['slug']) && $cat['slug'] == $decodedSlug) {
                $currentCategory = $cat;
                $categoryIds[] = $cat['id'];
                if (isset($cat['children']) && is_array($cat['children'])) {
                    foreach ($cat['children'] as $child) {
                        if(isset($child['id'])) $categoryIds[] = $child['id'];
                    }
                }
                break;
            }

            if (isset($cat['children']) && is_array($cat['children'])) {
                foreach ($cat['children'] as $child) {
                    // 2. استخدام $decodedSlug هنا أيضاً
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

        $productsResponse = Http::withHeaders($headers)->get("{$baseUrl}/products", $request->all());
        $allProducts = $productsResponse->json('data') ?? [];

        $products = collect($allProducts)->filter(function ($product) use ($categoryIds) {
            return isset($product['category']['id']) && in_array($product['category']['id'], $categoryIds);
        });

        return view('category', compact('currentCategory', 'products', 'categories', 'pages'));
    }
}