@extends('layouts.app')
@section('title', $product['title'] ?? 'تفاصيل المنتج')

@section('content')
    <div class="container mb-5">

        <!-- مسار التنقل (Breadcrumb) -->
        <nav aria-label="breadcrumb" class="mb-4 mt-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}"
                        class="text-decoration-none text-muted">{{ __('ui.home') }}</a></li>
                @if (isset($product['category']['name']))
                    <li class="breadcrumb-item text-muted">{{ $product['category']['name'] }}</li>
                @endif
                <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 250px;">
                    {{ $product['title'] }}</li>
            </ol>
        </nav>

        <!-- القسم العلوي: الصور والتفاصيل الأساسية -->
        <div class="card bg-white p-4 rounded shadow-sm mb-4 border-0">
            <div class="row">
                <!-- قسم ألبوم الصور (اليمين في العربية، اليسار في الإنجليزية) -->
                <div class="col-md-5 mb-4 mb-md-0">
                    <!-- الصورة الرئيسية -->
                    <div class="border rounded p-2 text-center mb-3 position-relative bg-light"
                        style="height: 400px; display: flex; align-items: center; justify-content: center;">
                        @if (isset($product['discount_percentage']) && $product['discount_percentage'] > 0)
                            <span class="badge bg-danger position-absolute top-0 start-0 m-3 fs-5 z-1">
                                {{ $product['discount_percentage'] }}% OFF
                            </span>
                        @endif
                        <img id="mainProductImage" src="{{ $product['image_url'] }}" alt="{{ $product['title'] }}"
                            style="max-height: 100%; max-width: 100%; object-fit: contain; transition: 0.3s ease-in-out;">
                    </div>

                    <!-- الصور المصغرة (Gallery) -->
                    @php
                        $gallery =
                            isset($product['gallery_images']) && is_string($product['gallery_images'])
                                ? json_decode($product['gallery_images'], true)
                                : (isset($product['gallery_images']) && is_array($product['gallery_images'])
                                    ? $product['gallery_images']
                                    : []);

                        if (!in_array($product['image_url'], $gallery)) {
                            array_unshift($gallery, $product['image_url']);
                        }
                    @endphp

                    @if (count($gallery) > 1)
                        <div class="d-flex gap-2 overflow-auto py-2 custom-scrollbar" style="white-space: nowrap;">
                            @foreach ($gallery as $image)
                                <div class="border rounded p-1 cursor-pointer gallery-thumb bg-white {{ $loop->first ? 'border-warning border-2 shadow-sm' : '' }}"
                                    style="width: 70px; height: 70px; flex-shrink: 0; cursor: pointer;"
                                    onclick="changeMainImage(this, '{{ $image }}')">
                                    <img src="{{ $image }}" style="width: 100%; height: 100%; object-fit: contain;">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- قسم تفاصيل المنتج الأساسية -->
                <div class="col-md-7">

                    @if (isset($product['is_amazon_choice']) && $product['is_amazon_choice'])
                        <span class="badge bg-dark text-white mb-2"><span class="text-warning">Amazon's</span> Choice</span>
                    @endif

                    <h1 class="h3 fw-bold mb-3">{{ $product['title'] }}</h1>

                    <!-- التقييم -->
                    <div class="mb-3 d-flex align-items-center gap-2">
                        <div class="text-warning fs-5">
                            @php $rating = floatval($product['rating'] ?? 4.5); @endphp
                            @for ($i = 1; $i <= 5; $i++)
                                @if ($i <= $rating)
                                    <i class="fas fa-star"></i>
                                @elseif($i - 0.5 <= $rating)
                                    <i class="fas fa-star-half-alt"></i>
                                @else
                                    <i class="far fa-star"></i>
                                @endif
                            @endfor
                        </div>
                        <span class="text-primary fw-semibold">{{ $product['rating'] ?? '4.5' }}</span>
                        <span class="text-muted">({{ $product['reviews_count'] ?? '120' }} {{ __('ui.reviews') }})</span>
                    </div>

                    <hr>

                    <!-- السعر -->
                    <div class="mb-4">
                        @if (isset($product['original_price']) && $product['original_price'] > $product['price'])
                            <div class="text-danger mb-1">
                                <span class="fs-5">-{{ $product['discount_percentage'] }}%</span>
                                <span class="fs-2 fw-bold text-dark ms-2">€{{ number_format($product['price'], 2) }}</span>
                            </div>
                            <div class="text-muted fs-6">
                                {{ __('ui.old_price') }} <span
                                    class="text-decoration-line-through">€{{ number_format($product['original_price'], 2) }}</span>
                            </div>
                        @else
                            <div class="fs-2 fw-bold text-dark">€{{ number_format($product['price'], 2) }}</div>
                        @endif
                    </div>

                    <!-- حالة التوفر -->
                    <div class="mb-4">
                        <h5 class="text-success fw-bold">
                            <i class="fas fa-check-circle"></i> {{ $product['availability'] ?? __('ui.in_stock') }}
                        </h5>
                        <p class="text-muted small"><i class="fas fa-shipping-fast"></i> {{ __('ui.shipping_info') }}</p>
                    </div>

                    <!-- زر الطلب -->
                    <div class="mb-4 d-grid d-md-block">
                        <a href="{{ $product['affiliate_url'] ?? '#' }}" target="_blank"
                            class="btn btn-warning btn-lg rounded-pill fw-bold px-5 shadow-sm">
                            <i class="fas fa-shopping-cart me-2"></i> {{ __('ui.buy_now') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- القسم الأوسط: وصف المنتج (في صف جديد وبعرض كامل) -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card bg-white p-4 p-md-5 rounded shadow-sm border-0">
                    <h4 class="fw-bold mb-4 border-bottom pb-3">
                        <i class="fas fa-info-circle text-warning me-2"></i> {{ __('ui.product_details') ?? 'تفاصيل المنتج' }}
                    </h4>
                    <div class="text-secondary" style="line-height: 1.9; font-size: 1.05rem;">
                        @if (!empty($product['content']))
                            {!! $product['content'] !!}
                        @else
                            {{ __('ui.no_description') ?? 'لا يوجد وصف متاح لهذا المنتج حالياً.' }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- القسم السفلي: المنتجات المشابهة -->
        @if(isset($relatedProducts) && $relatedProducts->count() > 0)
            
            <style>
                .products-slider-wrapper { position: relative; }
                .products-slider {
                    display: flex; overflow-x: auto; scroll-behavior: smooth;
                    scroll-snap-type: x mandatory; scrollbar-width: none;
                    -ms-overflow-style: none; gap: 1rem; padding-bottom: 1rem; padding-top: 0.5rem;
                }
                .products-slider::-webkit-scrollbar { display: none; }
                .custom-scrollbar::-webkit-scrollbar { height: 6px; }
                .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #ccc; border-radius: 10px; }
                .slider-item { flex: 0 0 100%; scroll-snap-align: start; }
                @media (min-width: 576px) { .slider-item { flex: 0 0 calc(50% - 0.5rem); } }
                @media (min-width: 992px) { .slider-item { flex: 0 0 calc(25% - 0.75rem); } }
                .slider-btn {
                    position: absolute; top: 45%; transform: translateY(-50%); z-index: 10;
                    background: white; border: 1px solid #ddd; border-radius: 50%;
                    width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;
                    cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.15); transition: all 0.3s ease; color: #333;
                }
                .slider-btn:hover { background: #ffc107; color: #000; border-color: #ffc107; }
                html[dir="rtl"] .btn-next { left: -15px; }
                html[dir="rtl"] .btn-prev { right: -15px; }
                html[dir="ltr"] .btn-next { right: -15px; }
                html[dir="ltr"] .btn-prev { left: -15px; }
            </style>

            <div class="mt-4" x-data="{
                    scrollSlider(direction) {
                        let slider = this.$refs.slider;
                        let scrollAmount = slider.offsetWidth; 
                        let isRTL = document.documentElement.dir === 'rtl';
                        
                        if (direction === 'next') {
                            slider.scrollBy({ left: isRTL ? -scrollAmount : scrollAmount, behavior: 'smooth' });
                        } else {
                            slider.scrollBy({ left: isRTL ? scrollAmount : -scrollAmount, behavior: 'smooth' });
                        }
                    }
                }">
                
                <h4 class="fw-bold mb-4">
                    <i class="fas fa-layer-group text-warning me-2"></i> {{ __('ui.similar_products') ?? 'منتجات مشابهة' }}
                </h4>
                
                <div class="products-slider-wrapper">
                    <!-- زر السابق -->
                    <button class="slider-btn btn-prev d-none d-md-flex" @click="scrollSlider('prev')">
                        <i class="fas" :class="document.documentElement.dir === 'rtl' ? 'fa-chevron-right' : 'fa-chevron-left'"></i>
                    </button>
                    
                    <!-- حاوية المنتجات -->
                    <div class="products-slider" x-ref="slider">
                        @foreach($relatedProducts as $related)
                            <div class="slider-item">
                                <div class="card h-100 border-0 shadow-sm product-card">
                                    <a href="{{ !empty($related['slug']) ? route('product.show', $related['slug']) : '#' }}" class="d-block position-relative bg-light rounded text-center" style="height: 180px;">
                                        @if(isset($related['image_url']))
                                            <img src="{{ $related['image_url'] }}" class="p-2" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                                        @endif
                                        @if(isset($related['discount_percentage']) && $related['discount_percentage'] > 0)
                                            <span class="badge bg-danger position-absolute top-0 start-0 m-2 fs-6">
                                                {{ $related['discount_percentage'] }}% OFF
                                            </span>
                                        @endif
                                    </a>

                                    <div class="card-body p-2 d-flex flex-column">
                                        <a href="{{ !empty($related['slug']) ? route('product.show', $related['slug']) : '#' }}" class="text-dark text-decoration-none mb-1">
                                            <h6 class="card-title text-truncate-2 fs-6 lh-sm" title="{{ $related['title'] ?? '' }}">
                                                {{ $related['title'] ?? 'بدون اسم' }}
                                            </h6>
                                        </a>
                                        
                                        <div class="mb-1 small">
                                            @php $rating = floatval($related['rating'] ?? 4.5); @endphp
                                            @for($i=1; $i<=5; $i++)
                                                @if($i <= $rating)
                                                    <i class="fas fa-star text-warning"></i>
                                                @elseif($i - 0.5 <= $rating)
                                                    <i class="fas fa-star-half-alt text-warning"></i>
                                                @else
                                                    <i class="far fa-star text-warning"></i>
                                                @endif
                                            @endfor
                                            <span class="text-muted ms-1">({{ $related['reviews_count'] ?? rand(10,500) }})</span>
                                        </div>

                                        <div class="mt-auto d-flex align-items-baseline gap-2 mb-2">
                                            <span class="fs-5 fw-bold text-dark">€{{ number_format($related['price'] ?? 0, 2) }}</span>
                                            @if(isset($related['original_price']) && $related['original_price'] > ($related['price'] ?? 0))
                                                <span class="text-muted text-decoration-line-through" style="font-size: 0.85rem;">
                                                    €{{ number_format($related['original_price'], 2) }}
                                                </span>
                                            @endif
                                        </div>

                                        <a href="{{ $related['affiliate_url'] ?? '#' }}" target="_blank" class="btn btn-warning btn-sm w-100 rounded-pill fw-semibold shadow-sm">
                                            {{ __('ui.buy_now') ?? 'اشترِ الآن' }} <i class="fas fa-external-link-alt small"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- زر التالي -->
                    <button class="slider-btn btn-next d-none d-md-flex" @click="scrollSlider('next')">
                        <i class="fas" :class="document.documentElement.dir === 'rtl' ? 'fa-chevron-left' : 'fa-chevron-right'"></i>
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- سكريبت تبديل الصور -->
    <script>
        function changeMainImage(element, imageUrl) {
            const mainImage = document.getElementById('mainProductImage');

            mainImage.style.opacity = '0.5';
            setTimeout(() => {
                mainImage.src = imageUrl;
                mainImage.style.opacity = '1';
            }, 150);

            const thumbs = document.querySelectorAll('.gallery-thumb');
            thumbs.forEach(thumb => {
                thumb.classList.remove('border-warning', 'border-2', 'shadow-sm');
                thumb.classList.add('border');
            });

            element.classList.remove('border');
            element.classList.add('border-warning', 'border-2', 'shadow-sm');
        }
    </script>
@endsection