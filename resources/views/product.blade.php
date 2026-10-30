@extends('layouts.app')
@section('title', $product['title'] ?? 'تفاصيل المنتج')

@section('content')
    <div class="container bg-white p-4 rounded shadow-sm mb-5">

        <!-- مسار التنقل (Breadcrumb) -->
        <nav aria-label="breadcrumb" class="mb-4">
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

        <div class="row">
            <!-- قسم ألبوم الصور (اليمين في العربية، اليسار في الإنجليزية) -->
            <div class="col-md-5 mb-4">
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
                    // فك تشفير مصفوفة الصور إذا كانت موجودة، أو استخدام الصورة الرئيسية كصورة وحيدة
                    $gallery =
                        isset($product['gallery_images']) && is_string($product['gallery_images'])
                            ? json_decode($product['gallery_images'], true)
                            : (isset($product['gallery_images']) && is_array($product['gallery_images'])
                                ? $product['gallery_images']
                                : []);

                    // دمج الصورة الرئيسية في بداية الألبوم إذا لم تكن موجودة فيه
                    if (!in_array($product['image_url'], $gallery)) {
                        array_unshift($gallery, $product['image_url']);
                    }
                @endphp

                @if (count($gallery) > 1)
                    <div class="d-flex gap-2 overflow-auto py-2" style="white-space: nowrap;">
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

            <!-- قسم تفاصيل المنتج -->
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

                <!-- زر الطلب (رابط الأفيليت) -->
                <div class="mb-4 d-grid d-md-block">
                    <a href="{{ $product['affiliate_url'] ?? '#' }}" target="_blank"
                        class="btn btn-warning btn-lg rounded-pill fw-bold px-5 shadow">
                        <i class="fas fa-shopping-cart"></i> {{ __('ui.buy_now') }}
                    </a>
                </div>

                <hr>

                <!-- وصف المنتج (HTML Content) -->
                <div class="mt-4">
                    <h4 class="fw-bold mb-3">{{ __('ui.product_details') }}</h4>
                    <div class="text-secondary" style="line-height: 1.8;">
                        @if (!empty($product['description']))
                            {!! $product['description'] !!}
                        @else
                            {{ __('ui.no_description') }}
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- سكريبت تبديل الصور -->
    <script>
        function changeMainImage(element, imageUrl) {
            // تغيير الصورة الرئيسية
            const mainImage = document.getElementById('mainProductImage');

            // تأثير اختفاء بسيط
            mainImage.style.opacity = '0.5';
            setTimeout(() => {
                mainImage.src = imageUrl;
                mainImage.style.opacity = '1';
            }, 150);

            // إزالة الإطار البرتقالي من كل الصور المصغرة
            const thumbs = document.querySelectorAll('.gallery-thumb');
            thumbs.forEach(thumb => {
                thumb.classList.remove('border-warning', 'border-2', 'shadow-sm');
                thumb.classList.add('border');
            });

            // إضافة الإطار للصورة المحددة
            element.classList.remove('border');
            element.classList.add('border-warning', 'border-2', 'shadow-sm');
        }
    </script>
@endsection
