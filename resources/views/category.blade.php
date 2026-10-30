@extends('layouts.app')
@section('title', $currentCategory['name'] ?? $currentCategory['title'] ?? 'التصنيف')

@section('content')
<div class="container mt-4">
    
    <!-- عنوان واسم التصنيف -->
    <div class="d-flex align-items-center mb-4 border-bottom pb-3">
        <h3 class="fw-bold mb-0">
            <i class="fas fa-tags text-warning me-2"></i> {{ $currentCategory['name'] ?? $currentCategory['title'] ?? '' }}
        </h3>
        <span class="badge bg-secondary ms-3">{{ $products->count() }} منتج</span>
    </div>

    <!-- شبكة المنتجات -->
    @if($products->count() > 0)
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3 mb-5">
            @foreach($products as $product)
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm product-card">
                        
                        <!-- صورة المنتج -->
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
                            <!-- العنوان -->
                            <a href="{{ !empty($product['slug']) ? route('product.show', $product['slug']) : '#' }}" class="text-dark text-decoration-none mb-1">
                                <h6 class="card-title text-truncate-2 fs-6 lh-sm" title="{{ $product['title'] ?? '' }}">
                                    {{ $product['title'] ?? 'بدون اسم' }}
                                </h6>
                            </a>
                            
                            <!-- التقييم -->
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

                            <!-- السعر -->
                            <div class="mt-auto d-flex align-items-baseline gap-2 mb-2">
                                <span class="fs-5 fw-bold text-dark">€{{ number_format($product['price'] ?? 0, 2) }}</span>
                                @if(isset($product['original_price']) && $product['original_price'] > ($product['price'] ?? 0))
                                    <span class="text-muted text-decoration-line-through" style="font-size: 0.85rem;">
                                        €{{ number_format($product['original_price'], 2) }}
                                    </span>
                                @endif
                            </div>

                            <!-- زر الطلب -->
                            <a href="{{ $product['affiliate_url'] ?? '#' }}" target="_blank" class="btn btn-warning btn-sm w-100 rounded-pill fw-semibold shadow-sm">
                                {{ __('ui.buy_now') ?? 'اشترِ الآن' }} <i class="fas fa-external-link-alt small"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-secondary text-center py-5 shadow-sm">
            <i class="fas fa-box-open fs-1 text-muted mb-3"></i>
            <h5>عذراً، لا توجد منتجات حالياً في هذا التصنيف.</h5>
        </div>
    @endif
</div>
@endsection