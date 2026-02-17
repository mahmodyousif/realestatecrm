@extends('layout')

@section('title')
        <div class="page-title-main">
            <h1><i class="fas fa-chart-line"></i> لوحة التحكم</h1>
            <p>نظرة عامة على أداء النظام والإحصائيات الرئيسية</p>
        </div>
    
@endsection

@section('content')
<div class="dashboard-wrapper">

    <button class="btn add-btn" onclick="openAddCompany()">
        <i class="fas fa-plus"></i> إضافة شركة
    </button>
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="filters-card-nested">
        <form method="GET" action="">
            <div class="filters-grid-nested">
                <div class="filter-group-nested">
                    <label>الشركة</label>
                    <select name="company_id" id="companySelect">
                        <option value="">جميع الشركات</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group-nested">
                    <label>المشروع</label>
                    <select name="project_id"  id="projectSelect">
                        <option value="">جميع المشاريع</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                            
                        @endforeach
                    </select>
                </div>

                <div class="filter-group-nested">
                    <button class="filter-btn-custom">🔍 تصفية</button>
                </div>
            </div>
        </form>
    </div>

    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="card-icon"><i class="fa-solid fa-city"></i></div>
            <div class="card-info">
                <h3>إجمالي المشاريع</h3>
                <h1 class="count">{{ $projectsCount }}</h1>
                <span class="trend">+{{$projectCountThisMonth}} هذا الشهر</span>
            </div>
        </div>

        <div class="stat-card green">
            <div class="card-icon"><i class="fa-solid fa-door-open"></i></div>
            <div class="card-info">
                <h3>الوحدات المتاحة</h3>
                <h1 class="count">{{ $availableUnitsCount }}</h1>
                <span class="trend">+{{$unitCountThisMonth}} وحدات هذا الشهر</span>
            </div>
        </div>

        <div class="stat-card red">
            <div class="card-icon"><i class="fa-solid fa-hand-holding-dollar"></i></div>
            <div class="card-info">
                <h3>الوحدات المباعة</h3>
                <h1 class="count">{{ $soldUnitsCount }}</h1>
                <span class="trend">+ {{$soldUnitThisMonth}} وحدات مباعة هذا الشهر </span>
            </div>
        </div>

        <div class="stat-card orange">
            <div class="card-icon"><i class="fa-solid fa-key"></i></div>
            <div class="card-info">
                <h3>الوحدات المحجوزة</h3>
                <h1 class="count">{{ $reservedUnitsCount }}</h1>
                <span class="trend">قيد المعالجة</span>
            </div>
        </div>
    </div>



    


    <div class="main-dashboard-grid">
        
        <div class="content-card">
            <div class="card-header">
                <h2><i class="fa-solid fa-trowel-bricks"></i> أحدث المشاريع</h2>
                <a href="{{ route('projects') }}" class="btn-link">عرض الكل</a>
            </div>
            <div class="items-list">
                @foreach($projects as $project)
                <div class="item-row">
                    <div class="item-details">
                        <h4>{{ $project->name }}</h4>
                        <p>{{ $project->floors }} طابق • {{ $project->total_units }} وحدة سكنية</p>
                    </div>
                    <span class="badge available">{{ $project->units->count() }} متاحة</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="content-card">
            <div class="card-header">
                <h2><i class="fa-solid fa-receipt"></i> آخر الوحدات المباعة</h2>
            </div>
            <div class="items-list">

                @foreach($latestUnitSold as $unit)
                <div class="item-row">
                    <div class="item-details">
                        <h4> {{$unit->type . " ". $unit->unit_number}} - {{$unit->project->name}} </h4>
                        <p>المساحة: {{$unit->area}} م² • الطابق: {{$unit->floor}}</p>
                    </div>
                    <span class="badge sold">مباعة</span>
                </div>
                @endforeach
            </div>
        </div>

    </div>

            <div class="data-card" style="margin-top:20px;">
            <div class="card-header">
                <h2><i class="fas fa-building"></i> إحصائيات الوحدات حسب المشاريع</h2>
            </div>
            <div class="card-body">
                <div id="projectsUnitsChart" style="min-height:400px;"></div>
            </div>
        </div>

</div>


        <script>
            (function(){
                const pLabels = @json($projectLabels ?? []);
                const pAvailable = @json($projectAvailable ?? []);
                const pReserved = @json($projectReserved ?? []);
                const pSold = @json($projectSold ?? []);

                if(!pLabels.length) return;

                const options = {
                    chart: { type: 'bar', height: 420, toolbar: { show: true } },
                    plotOptions: { bar: { horizontal: false, columnWidth: '100%' } },
                    dataLabels: { enabled: false },
                    stroke: { show: true, width: 1, colors: ['#fff'] },
                    series: [
                        { name: 'محجوزة', data: pReserved },
                        { name: 'مباعة', data: pSold },
                        { name: 'متاحة', data: pAvailable }
                    ],
                    colors: ['#f6c23e', '#4e73df', '#1cc88a'],
                    xaxis: { categories: pLabels, title: { text: 'المشروع' } },
                    yaxis: { title: { text: 'عدد الوحدات' } },
                    legend: { position: 'top' },
                    tooltip: { y: { formatter: function (val) { return parseInt(val); } } }
                };

                const chart = new ApexCharts(document.querySelector('#projectsUnitsChart'), options);
                chart.render();
            })();
        </script>

<div id="addCompanyModal" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <h2>إضافة شركة جديدة</h2>
            <button class="close-btn" onclick="closeAddCompanyModal()">✕</button>
        </div>
        <form id="addCompanyForm" method="POST" action="{{ route('add_company') }}">
            @csrf
            <div class="form-body">
                <div class="input-group">
                    <label>اسم الشركة</label>
                    <input type="text" name="name" placeholder="أدخل اسم الشركة هنا..." required>
                </div>
            </div>
            <div class="form-footer">
                <button type="submit" class="btn-save">حفظ البيانات</button>
                <button type="button" class="btn-cancel" onclick="closeAddCompanyModal()">إلغاء</button>
            </div>
        </form>
    </div>
</div>

@endsection