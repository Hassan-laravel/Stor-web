@extends('layouts.app')
@section('title', $currentCategory['name'] ?? $currentCategory['title'] ?? 'التصنيف')

@section('content')
<!-- التغليف بـ Alpine.js للفلترة والترتيب -->
<div class="container mt-4" x-data="{ 
        search_name: {{ json_encode(request('search_name', '')) }},
        min_price: {{ json_encode(request('min_price', '')) }},
        max_price: {{ json_encode(request('max_price', '')) }},
        top_rated: {{ request('top_rated') == '1' ? 'true' : 'false' }},
        best_sellers: {{ request('best_sellers') == '1' ? 'true' : 'false' }},
        isLoading: false,
        
        filterCategoryProducts() {
            this.isLoading = true;
            let url = new URL(window.location.origin + window.location.pathname);
            
            if(this.search_name) url.searchParams.set('search_name', this.search_name);
            if(this.min_price) url.searchParams.set('min_price', this.min_price);
            if(this.max_price) url.searchParams.set('max_price', this.max_price);
            if(this.top_rated) url.searchParams.set('top_rated', '1');
            if(this.best_sellers) url.searchParams.set('best_sellers', '1');

            fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.text())
                .then(html => {
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(html, 'text/html');
                    
                    let newGrid = doc.getElementById('products-grid-wrapper');
                    if(newGrid) {
                        document.getElementById('products-grid-wrapper').innerHTML = newGrid.innerHTML;
                    }
                    
                    window.history.pushState({}, '', url.toString());
                    this.isLoading = false;
                }).catch(() => {
                    this.isLoading = false;
                });
        }
    }">
    
    <!-- عنوان التصنيف -->
    <div class="d-flex align-items-center mb-4 border-bottom pb-3">
        <h3 class="fw-bold mb-0">
            <i class="fas fa-tags text-warning me-2"></i> {{ $currentCategory['name'] ?? $currentCategory['title'] ?? '' }}
        </h3>
        <span class="badge bg-secondary mx-3" id="products-count">{{ $products->count() }}</span>
    </div>

    <div class="row">
        <!-- الشريط الجانبي (الفلاتر) -->
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px; z-index: 1;">
                <div class="card-header bg-white fw-bold py-3 border-bottom">
                    <i class="fas fa-filter text-warning me-2"></i> {{ __('ui.filters') }}
                </div>
                <div class="card-body">
                    
                    <!-- فلتر الاسم -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">{{ __('ui.search_by_name') }}</label>
                        <input type="text" x-model="search_name" x-on:input.debounce.500ms="filterCategoryProducts()" class="form-control form-control-sm" placeholder="{{ __('ui.search_name_placeholder') }}">
                    </div>

                    <!-- فلتر السعر -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">{{ __('ui.price_range') }}</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="number" x-model="min_price" x-on:input.debounce.800ms="filterCategoryProducts()" class="form-control form-control-sm" placeholder="{{ __('ui.min') }}">
                            <span class="text-muted">-</span>
                            <input type="number" x-model="max_price" x-on:input.debounce.800ms="filterCategoryProducts()" class="form-control form-control-sm" placeholder="{{ __('ui.max') }}">
                        </div>
                    </div>

                    <hr class="text-muted">

                    <!-- خيارات الترتيب (Switches) -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold small mb-3">{{ __('ui.sorting_options') }}</label>
                        
                        <!-- زر التقييم الأعلى -->
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="topRatedSwitch" x-model="top_rated" x-on:change="filterCategoryProducts()">
                            <label class="form-check-label small" style="cursor: pointer;" for="topRatedSwitch">
                                <i class="fas fa-star text-warning me-1"></i> {{ __('ui.top_rated') }}
                            </label>
                        </div>

                        <!-- زر الأكثر مبيعاً -->
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="bestSellersSwitch" x-model="best_sellers" x-on:change="filterCategoryProducts()">
                            <label class="form-check-label small" style="cursor: pointer;" for="bestSellersSwitch">
                                <i class="fas fa-fire text-danger me-1"></i> {{ __('ui.best_sellers') }}
                            </label>
                        </div>
                    </div>

                    <!-- زر تفريغ الفلاتر -->
                    <button type="button" class="btn btn-light btn-sm w-100 border mt-2" 
                        x-on:click="search_name=''; min_price=''; max_price=''; top_rated=false; best_sellers=false; filterCategoryProducts()">
                        <i class="fas fa-times me-1"></i> {{ __('ui.clear_filters') }}
                    </button>

                </div>
            </div>
        </div>

        <!-- شبكة المنتجات -->
        <div class="col-lg-9 position-relative" id="products-grid-wrapper">
            
            <!-- طبقة التحميل -->
            <div x-show="isLoading" style="display: none;" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 z-3 rounded">
                <div class="d-flex justify-content-center pt-5 w-100 h-100">
                    <div class="spinner-border text-warning" role="status"></div>
                </div>
            </div>

            @if($products->count() > 0)
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 mb-5">
                    @foreach($products as $product)
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm product-card">
                                
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
                                            {{ $product['title'] ?? '' }}
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
                                        {{ __('ui.buy_now') ?? 'Buy' }} <i class="fas fa-external-link-alt small"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-secondary text-center py-5 shadow-sm">
                    <i class="fas fa-search fs-1 text-muted mb-3"></i>
                    <h5>{{ __('ui.no_products_found') }}</h5>
                    <button type="button" class="btn btn-outline-secondary mt-3" x-on:click="search_name=''; min_price=''; max_price=''; top_rated=false; best_sellers=false; filterCategoryProducts()">
                        {{ __('ui.clear_and_try_again') }}
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection