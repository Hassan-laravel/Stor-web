<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $dir ?? (app()->getLocale() == 'ar' ? 'rtl' : 'ltr') }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My E-Commerce - @yield('title')</title>

    <!-- تضمين مكتبة Alpine.js للفلترة الديناميكية -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @if (isset($dir) && $dir === 'rtl' || app()->getLocale() == 'ar')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Cairo', sans-serif;
                background-color: #f3f4f6;
            }
        </style>
    @else
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background-color: #f3f4f6;
            }
        </style>
    @endif
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* كلاس مخصص لقص العناوين الطويلة في سطرين فقط */
        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 2.8em;
        }

        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: 0.3s;
        }

        /* تنسيقات شريط التصنيفات للديسكتوب */
        @media all and (min-width: 992px) {
            .desktop-categories-bar .dropdown:hover .dropdown-menu {
                display: block;
                margin-top: 0;
                border-radius: 0 0 0.5rem 0.5rem;
                border-top: 3px solid #ffc107;
                box-shadow: 0 10px 15px rgba(0,0,0,0.1);
            }
            .desktop-categories-bar .dropdown-toggle::after {
                transition: transform 0.2s ease-in-out;
            }
            .desktop-categories-bar .dropdown:hover .dropdown-toggle::after {
                transform: rotate(180deg);
            }
        }
        
        .desktop-categories-bar .nav-link:hover {
            color: #ffc107 !important;
        }
    </style>
</head>

<body>

    <!-- الطبقة العلوية: الشعار وتبديل اللغة -->
    <header class="bg-dark text-white p-2">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="{{ route('home') }}" class="text-white text-decoration-none fs-4 fw-bold">
                <i class="fas fa-shopping-cart text-warning"></i> StoreLogo
            </a>

            <div class="dropdown">
                <button class="btn btn-dark btn-sm dropdown-toggle border-secondary" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-globe"></i> {{ strtoupper(app()->getLocale()) }}
                </button>
<ul class="dropdown-menu dropdown-menu-end">
                    @php
                        $availableLocales = [
                            'en' => 'English',
                            'nl' => 'Nederlands',
                            'tr' => 'Türkçe',
                            'ar' => 'العربية'
                        ];
                    @endphp

                    @foreach($availableLocales as $code => $name)
                        <li>
                            <!-- 
                              نمرر رمز اللغة (locale) 
                              وإذا كان هناك رابط مخصص لهذه اللغة ($localizedUrls) نمرره في redirect_to
                            -->
                            <a class="dropdown-item" href="{{ route('lang.switch', [
                                'locale' => $code, 
                                'redirect_to' => $localizedUrls[$code] ?? null
                            ]) }}">
                                {{ $name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </header>

    <!-- الطبقة السفلية: شريط التصفح (الصفحات + تصنيفات الموبايل) -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm border-bottom">
        <div class="container">
<button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenuOffcanvas" aria-controls="mobileMenuOffcanvas">
    <span class="navbar-toggler-icon"></span>
</button>
            
<!-- القائمة الجانبية للموبايل (Offcanvas) -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenuOffcanvas" aria-labelledby="mobileMenuLabel" style="max-width: 75vw;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="mobileMenuLabel">
            <i class="fas fa-shopping-cart text-warning me-2"></i> القائمة
        </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">

        <!-- روابط الصفحات -->
        <ul class="navbar-nav me-auto fw-semibold mb-4">
            <li class="nav-item">
                <a class="nav-link py-2 border-bottom" href="{{ route('home') }}">{{ __('ui.home') }}</a>
            </li>
            @foreach($pages ?? [] as $page)
                <li class="nav-item">
                    <a class="nav-link py-2 border-bottom" href="/page/{{ $page['slug'] ?? '' }}">{{ $page['title'] ?? '' }}</a>
                </li>
            @endforeach
        </ul>

        <!-- روابط التصنيفات -->
        <h6 class="text-warning fw-bold mb-3 px-2">{{ __('ui.categories') }}</h6>
        <ul class="navbar-nav ms-auto fw-semibold">
            @foreach($categories ?? [] as $category)
                @if(isset($category['children']) && is_array($category['children']) && count($category['children']) > 0)
                    <li class="nav-item dropdown mb-2">
                        <a class="nav-link dropdown-toggle bg-light rounded px-3 py-2" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ $category['name'] ?? $category['title'] ?? '' }}
                        </a>
                        <ul class="dropdown-menu border-0 bg-white ps-3">
                            <li><a class="dropdown-item py-2 fw-bold text-warning" href="{{ route('category.show', $category['slug'] ?? '') }}">الكل في {{ $category['name'] ?? $category['title'] ?? '' }}</a></li>
                            @foreach($category['children'] as $child)
                                <li><a class="dropdown-item py-2" href="{{ route('category.show', $child['slug'] ?? '') }}">{{ $child['name'] ?? $child['title'] ?? '' }}</a></li>
                            @endforeach
                        </ul>
                    </li>
                @else
                    <li class="nav-item mb-2">
                        <a class="nav-link bg-light rounded px-3 py-2" href="{{ route('category.show', $category['slug'] ?? '') }}">
                            {{ $category['name'] ?? $category['title'] ?? '' }}
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>
    </div>
</div>
        </div>
    </nav>

    <!-- شريط التصنيفات للديسكتوب فقط (مخفي في الموبايل d-none d-lg-block) -->
    <div class="desktop-categories-bar bg-white shadow-sm mb-4 d-none d-lg-block">
        <div class="container">
            <ul class="nav fw-semibold">
                <li class="nav-item d-flex align-items-center me-3 text-dark fw-bold">
                    <i class="fas fa-bars text-warning me-2"></i> {{ __('ui.categories') }} :
                </li>

                @foreach($categories ?? [] as $category)
                    @if(isset($category['children']) && is_array($category['children']) && count($category['children']) > 0)
                        <!-- تصنيف رئيسي له فروع يفتح عند الـ Hover -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-dark py-3 px-3" href="{{ route('category.show', $category['slug'] ?? '') }}">
                                {{ $category['name'] ?? $category['title'] ?? '' }}
                            </a>
                            <ul class="dropdown-menu border-0 shadow-lg mt-0" style="min-width: 220px;">
                                @foreach($category['children'] as $child)
                                    <li>
                                        <a class="dropdown-item py-2" href="{{ route('category.show', $child['slug'] ?? '') }}">
                                            <i class="fas fa-angle-right text-warning ms-1 me-2" style="font-size: 0.8rem;"></i> 
                                            {{ $child['name'] ?? $child['title'] ?? '' }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        <!-- تصنيف رئيسي بدون فروع -->
                        <li class="nav-item">
                            <a class="nav-link text-dark py-3 px-3" href="{{ route('category.show', $category['slug'] ?? '') }}">
                                {{ $category['name'] ?? $category['title'] ?? '' }}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </div>

    <!-- محتوى الصفحة الرئيسية -->
    <main class="container">
        @yield('content')
    </main>

    <!-- الفوتر الاحترافي -->
    <footer class="bg-dark text-white mt-5 pt-5 pb-3">
        <div class="container">
            <div class="row">
                <!-- قسم الوصف -->
                <div class="col-md-4 mb-3">
                    <h5 class="text-warning">StoreLogo</h5>
                    <p class="text-secondary small mt-3">{{ __('ui.store_description') ?? 'متجرك المفضل للتسوق بأفضل الأسعار. نوفر لك منتجات من أمازون وبول مباشرة.' }}</p>
                </div>
                
                <!-- قسم الروابط (الصفحات الديناميكية) -->
                <div class="col-md-4 mb-3">
                    <h5>{{ __('ui.important_links') ?? 'روابط هامة' }}</h5>
                    <ul class="list-unstyled text-secondary small line-height-lg">
                        @foreach($pages ?? [] as $page)
                            <li>
                                <a href="/page/{{ $page['slug'] ?? '' }}" class="text-secondary text-decoration-none">
                                    {{ $page['title'] ?? '' }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                
                <!-- قسم التواصل -->
                <div class="col-md-4 mb-3">
                    <h5>{{ __('ui.contact_us_footer') ?? 'تواصل معنا' }}</h5>
                    <p class="text-secondary small"><i class="fas fa-envelope"></i> support@store.com</p>
                </div>
            </div>
            
            <!-- حقوق النشر -->
            <div class="text-center text-secondary small border-top border-secondary pt-3 mt-3">
                &copy; {{ date('Y') }} {{ __('ui.all_rights_reserved') ?? 'جميع الحقوق محفوظة.' }}
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>