@extends('layouts.app')
@section('title', __('ui.home'))

@section('content')

<!-- تغليف منطقة الفلترة والمنتجات بـ Alpine.js -->
<div x-data="{ 
        search: {{ json_encode(request('search', '')) }}, 
        store_id: {{ json_encode(request('store_id', '')) }}, 
        category_id: {{ json_encode(request('category_id', '')) }},
        isLoading: false,
        
        filterProducts() {
            this.isLoading = true;
            
            let url = new URL(window.location.origin + window.location.pathname);
            if(this.search) url.searchParams.set('search', this.search);
            if(this.store_id) url.searchParams.set('store_id', this.store_id);
            if(this.category_id) url.searchParams.set('category_id', this.category_id);

            fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.text())
                .then(html => {
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(html, 'text/html');
                    
                    let newGrid = doc.getElementById('products-grid');
                    if(newGrid) {
                        document.getElementById('products-grid').innerHTML = newGrid.innerHTML;
                    }
                    
                    window.history.pushState({}, '', url.toString());
                    this.isLoading = false;
                }).catch(() => {
                    this.isLoading = false;
                });
        }
    }">

    <!-- شريط الفلترة -->
    <div class="card border-0 shadow-sm mb-4 position-relative">
<!-- مؤشر التحميل يظهر فوق الشريط عند التحديث -->
        <div x-show="isLoading" style="display: none;" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 z-3 rounded">
            <div class="d-flex justify-content-center align-items-center w-100 h-100">
                <div class="spinner-border text-warning" role="status"></div>
            </div>
        </div>

        <div class="card-body p-2 p-md-3">
            <div class="row g-2 align-items-center">
                
                <div class="col-md-4">
                    <input type="text" x-model="search" x-on:input.debounce.500ms="filterProducts()" class="form-control" placeholder="{{ __('ui.search_placeholder') ?? 'بحث...' }}">
                </div>
                
                {{-- <div class="col-md-3">
                    <select x-model="store_id" x-on:change="filterProducts()" class="form-select">
                        <option value="">{{ __('ui.all_stores') ?? 'كل المخازن' }}</option>
                        @foreach($stores ?? [] as $store)
                            <option value="{{ $store['id'] ?? '' }}">
                                {{ $store['name'] ?? 'بدون اسم' }}
                            </option>
                        @endforeach
                    </select>
                </div> --}}

                <div class="col-md-3">
                    <select x-model="category_id" x-on:change="filterProducts()" class="form-select">
                        <option value="">{{ __('ui.all_categories') ?? 'كل التصنيفات' }}</option>
                        @foreach($categories ?? [] as $category)
                            <option value="{{ $category['id'] ?? '' }}" class="fw-bold">
                                {{ $category['name'] ?? $category['title'] ?? '' }}
                            </option>
                            
                            @if(isset($category['children']) && is_array($category['children']))
                                @foreach($category['children'] as $child)
                                    <option value="{{ $child['id'] ?? '' }}">
                                        &nbsp;&nbsp;-- {{ $child['name'] ?? $child['title'] ?? '' }}
                                    </option>
                                @endforeach
                            @endif
                        @endforeach
                    </select>
                </div>
                
                {{-- <div class="col-md-2 d-grid">
                    <button type="button" x-on:click="filterProducts()" class="btn btn-warning fw-bold">
                        {{ __('ui.search_btn') ?? 'بحث' }}
                    </button>
                </div> --}}
                
            </div>
        </div>
    </div>

    <!-- شبكة المنتجات -->
    <div id="products-grid">
        @php
            $hasProducts = false;
        @endphp

        @foreach($categories ?? [] as $category)
            @php
                // جمع المعرفات بشكل آمن جداً
                $categoryIds = [];
                if(isset($category['id'])) {
                    $categoryIds[] = $category['id'];
                }
                
                if(isset($category['children']) && is_array($category['children'])) {
                    foreach($category['children'] as $child) {
                        if(isset($child['id'])) {
                            $categoryIds[] = $child['id'];
                        }
                    }
                }

                // فلترة المنتجات بشكل آمن
                $categoryProducts = collect($products ?? [])->filter(function ($product) use ($categoryIds) {
                    return isset($product['category']['id']) && in_array($product['category']['id'], $categoryIds);
                })->take(6);
            @endphp

            @if($categoryProducts->count() > 0)
                @php $hasProducts = true; @endphp
                <div class="bg-white p-4 rounded shadow-sm mb-4">
                    <h4 class="mb-4 border-bottom pb-2">
                        <i class="fas fa-tags text-warning"></i> {{ $category['name'] ?? $category['title'] ?? '' }}
                    </h4>
                    
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3">
                        @foreach($categoryProducts as $product)
                            <div class="col">
                                <div class="card h-100 border-0 product-card">
                                    <a href="{{ !empty($product['slug']) ? route('product.show', $product['slug']) : '#' }}" class="d-block position-relative bg-light rounded text-center" style="height: 180px;">
                                        @if(isset($product['image_url']))
                                            <img src="{{ $product['image_url'] }}" class="p-2" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                                        @endif
                                        
                                        @if(isset($product['discount_percentage']) && $product['discount_percentage'] > 0)
                                            <span class="badge bg-danger position-absolute top-0 start-0 m-2 fs-6">
                                                {{ $product['discount_percentage'] }}% OFF
                                            </span>
                                        @endif
                                    </a>

                                    <div class="card-body p-2 d-flex flex-column">
                                        <a href="{{ !empty($product['slug']) ? route('product.show', $product['slug']) : '#' }}" class="text-dark text-decoration-none mb-1">
                                            <h6 class="card-title text-truncate-2 fs-6 lh-sm" title="{{ $product['title'] ?? '' }}">
                                                {{ $product['title'] ?? 'منتج بدون اسم' }}
                                            </h6>
                                        </a>
                                        
                                        <div class="mb-1 small">
                                            @php $rating = floatval($product['rating'] ?? 4.5); @endphp
                                            @for($i=1; $i<=5; $i++)
                                                @if($i <= $rating)
                                                    <i class="fas fa-star text-warning"></i>
                                                @elseif($i - 0.5 <= $rating)
                                                    <i class="fas fa-star-half-alt text-warning"></i>
                                                @else
                                                    <i class="far fa-star text-warning"></i>
                                                @endif
                                            @endfor
                                            <span class="text-muted ms-1">({{ $product['reviews_count'] ?? rand(10,500) }})</span>
                                        </div>

                                        <div class="mt-auto d-flex align-items-baseline gap-2 mb-2">
                                            <span class="fs-5 fw-bold text-dark">€{{ number_format($product['price'] ?? 0, 2) }}</span>
                                            @if(isset($product['original_price']) && $product['original_price'] > ($product['price'] ?? 0))
                                                <span class="text-muted text-decoration-line-through" style="font-size: 0.85rem;">
                                                    €{{ number_format($product['original_price'], 2) }}
                                                </span>
                                            @endif
                                        </div>

                                        <a href="{{ $product['affiliate_url'] ?? '#' }}" target="_blank" class="btn btn-warning btn-sm w-100 rounded-pill fw-semibold shadow-sm">
                                            {{ __('ui.buy_now') ?? 'اشترِ الآن' }} <i class="fas fa-external-link-alt small"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach

        @if(!$hasProducts)
            <div class="alert alert-secondary text-center py-5 shadow-sm">
                <i class="fas fa-box-open fs-1 text-muted mb-3"></i>
                <h5>عذراً، لم نتمكن من العثور على منتجات تطابق بحثك.</h5>
                <p class="text-muted">جرب استخدام كلمات مختلفة أو تغيير إعدادات الفلترة.</p>
            </div>
        @endif
    </div>
</div>

@endsection