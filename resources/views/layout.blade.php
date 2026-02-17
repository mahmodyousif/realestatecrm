    <!DOCTYPE html>
    <html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="format-detection" content="telephone=no">
        <title>إدارة العقارات الاحترافي</title>
        
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('css/app2.css') }}">
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        @stack('styles')
    </head>
    <body>
        <div class="app-layout">
            <aside class="sidebar">
                <div class="logo">نظام إدارة المشاريع العقارية
                    <div class="menu-toggle" aria-label="فتح القائمة"></div>
                </div>
                <nav class="nav-links">
                    <a href="{{route('dashboard')}}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-line icon"></i> <span>لوحة التحكم</span>
                    </a>
                
                    @can('manager')
                        <div class="dropdown-container">
                            <button class="dropdown-btn" onclick="toggleDropdown(event)">
                                <i class="fa-solid fa-building icon"></i> <span>الشركات</span>
                                <i class="fa-solid fa-chevron-down" style="margin-right: auto; font-size: 12px;"></i>
                            </button>
                            <div id="company-menu" class="dropdown-content" style="display: none; padding-right: 30px;">
                                @foreach($companies as $company)
                                    <a href="{{ route('company', $company->id) }}">{{$company->name}}</a>
                                @endforeach
                            </div>
                        </div>
                        <a href="{{route('projects')}}" class="{{ request()->routeIs('projects') ? 'active' : '' }}"><i class="fa-solid fa-trowel-bricks icon"></i> <span>المشاريع</span></a>
                    
                        <a href="{{route('customers')}}" class="{{ request()->routeIs('customers') ? 'active' : '' }}">
                            <i class="fa-solid fa-users icon"></i> <span>العملاء</span>
                        </a>
                        <a href="{{route('users')}}" class="{{ request()->routeIs('users') ? 'active' : '' }}">
                            <i class="fa-solid fa-user-group icon"></i> <span>المستخدمين</span>
                        </a>
                    @endcan

                    @can('units-sell')
                        <a href="{{route('units')}}" class="{{ request()->routeIs('units') ? 'active' : '' }}"><i class="fa-solid fa-house-chimney icon"></i> <span>الوحدات</span></a>
                    @endcan

                    @can('payments')
                        <a href="{{route('reports')}}" class="{{ request()->routeIs('reports') ? 'active' : '' }}"><i class="fa-solid fa-file-invoice-dollar icon"></i> <span>التقارير</span></a>
                        <a href="{{route('payments')}}" class="{{ request()->routeIs('payments') ? 'active' : '' }}"><i class="fa-solid fa-credit-card icon"></i> <span>إدارة الدفعات</span></a>
                    @endcan
                </nav>
            </aside>
            <main class="main-wrapper">


                <nav>
                    <div class="user-actions" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                        <button class="theme-toggle" onclick="toggleTheme()" title="تبديل الوضع">
                            <i class="fa-solid fa-moon" id="theme-icon"></i>
                        </button>

                        @auth
                            <div class="user-profile" style="background: var(--bg-card); padding: 5px 15px; border-radius: 12px; border: 1px solid var(--border-color); display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 14px; font-weight: 600; white-space: nowrap;">{{ auth()->user()->name }}</span>
                                <form method="POST" action="{{ route('logout') }}" style="display: inline; margin: 0;">
                                    @csrf
                                    <button type="submit" style="border: none; background: none; color: #ff5b5b; cursor: pointer; padding: 5px; min-height: auto;" title="تسجيل الخروج"><i class="fa-solid fa-power-off"></i></button>
                                </form>
                            </div>
                        @endauth
                    </div>
                </nav>
                <header>
                    <div class="page-info">
                        @yield('title')
                    </div>
                    
                   
                </header>

                <div class="content-area">
                    @yield('content')
                </div>
            </main>
        </div>

        <script>
            
        </script>
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script src="{{ asset('js/app.js') }}"></script>
    </body>
    </html>