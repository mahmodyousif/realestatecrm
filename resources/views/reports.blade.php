@extends('layout')

@section('title')
<div class="page-header">
    <h1>📈 التقارير والإحصائيات التحليلية</h1>
    <p>مراقبة المبيعات، التدفقات النقدية، وأداء المشاريع</p>
</div>
@endsection

@section('content')

<div class="reports-container">
    <div class="filters-card-nested">
        <form method="GET" action="">
            <div class="filters-grid-nested">
                <div class="filter-group-nested">
                    <label>الشركة</label>
                    <select name="company_id"  id="companySelect">
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
                    <select name="project_id" id="projectSelect">
                        <option value="">جميع المشاريع</option>
                        @foreach($allProjects as $project)
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
    
    <div class="filter-section">
        <form action="{{route('reports')}}" method="GET" class="filter-form">
            <div class="filter-info">
                <i class="fas fa-filter"></i>
                <div>
                    <h4>تخصيص النطاق الزمني</h4>
                    <p>استخرج تقارير لفترة محددة</p>
                </div>
            </div>
            <div class="filter-inputs">
                <div class="input-group">
                    <label>من تاريخ</label>
                    <input type="date" name="from" value="{{ request('from') }}">
                </div>
                <div class="input-group">
                    <label>إلى تاريخ</label>
                    <input type="date" name="to" value="{{ request('to') }}">
                </div>
                <button type="submit" class="btn-refresh">
                    تحديث <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </form>
    </div>
{{-- </div> --}}
</div>
    <div class="summary-grid">
        <div class="summary-card primary">
            <div class="card-icon"><i class="fas fa-chart-line"></i></div>
            <div class="card-content">
                <h3>إجمالي المبيعات</h3>
                <div class="amount">{{ number_format($totalPrice) }} <small>ريال</small></div>
                <div class="trend positive">+{{ number_format($currentMonthSalesPayment) }}  عن الشهر الماضي</div>
            </div>
        </div>
        
        <div class="summary-card success">
            <div class="card-icon"><i class="fas fa-cash-register"></i></div>
            <div class="card-content">
                <h3>الإيرادات المحققة</h3>
                <div class="amount">{{ number_format($totalPaid) }} <small>ريال</small></div>
                <div class="trend positive">+{{ number_format($currentMonthSalesPayment) }} عن الشهر الماضي</div>
            </div>
        </div>

        <div class="summary-card danger">
            <div class="card-icon"><i class="fas fa-file-invoice-dollar"></i></div>
            <div class="card-content">
                <h3>المبالغ المتبقية</h3>
                <div class="amount">{{ number_format($remaining) }} <small>ريال</small></div>
                <div class="trend">قيد التحصيل</div>
            </div>
        </div>

        <div class="summary-card info">
            <div class="card-icon"><i class="fas fa-home"></i></div>
            <div class="card-content">
                <h3>عدد الوحدات المباعة</h3>
                <div class="amount">{{ number_format($unitSalesCount) }} <small>وحدة</small></div>
                <div class="trend positive">+{{ number_format($currentMonthSalesCount) }} هذا الشهر</div>
            </div>
        </div>
    </div>

    <div class="performance-grid">
        <div class="performance-card">
            <div class="card-head">
                <h4><i class="far fa-calendar-check"></i> أداء اليوم</h4>
            </div>
            <div class="mini-stats">
                <div class="mini-item">
                    <span class="label">وحدات مباعة</span>
                    <span class="val">{{ number_format($todaySales) }}</span>
                </div>
                <div class="mini-item">
                    <span class="label">مدفوعات اليوم</span>
                    <span class="val highlight">{{ number_format($todayPayments) }} ريال</span>
                </div>
            </div>
        </div>

        <div class="performance-card">
            <div class="card-head">
                <h4><i class="far fa-calendar-alt"></i> أداء الشهر الحالي</h4>
            </div>
            <div class="mini-stats">
                <div class="mini-item">
                    <span class="label">وحدات مباعة</span>
                    <span class="val">{{ number_format($currentMonthSalesCount) }}</span>
                </div>
                <div class="mini-item">
                    <span class="label">إجمالي التحصيل</span>
                    <span class="val highlight">{{ number_format($currentMonthSalesPayment) }} ريال</span>
                </div>
            </div>
        </div>
    </div>


    <div class="data-card">
        <div class="card-header">
            <h2><i class="fas fa-list-ul"></i> تقرير المبيعات التفصيلي</h2>
            <a href="{{route('reports.export')}}" class="btn-export">
                <i class="fas fa-file-export"></i> تصدير التقرير
            </a>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>الشركة</th>
                        <th>المشروع</th>
                        <th>نموذج الوحدة</th>
                        <th>نوع الوحدة</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $unit)
                    @php
                        if($unit->unit->status === 'sold') {
                            $status = 'مباعة';
                        } elseif($unit->unit->status === 'reserved') {  
                            $status = 'محجوزة';
                        } else {
                            $status = 'جاهزة للبيع';
                        }
                    @endphp
                        <tr>
                            <td>{{ $unit->unit->project->company->name }}</td>
                            <td>{{$unit->unit->project->name}}</td>
                            <td class="bold">{{ $unit->unit->unit_number }}</td>
                            <td>{{ $unit->unit->type }}</td>
                            <td>
                                <span class="badge {{ $status === 'جاهزة للبيع' ? 'available' : ($status === 'محجوزة' ? 'reserved' : 'sold') }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td>
                                <a href="{{route('units.show' ,$unit->unit->id)}}"class="action-link view" title="عرض"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="data-card">
    <div class="card-header">
        <h2><i class="fas fa-chart-line"></i> نمو المبيعات هذا الشهر</h2>
    </div>
    <div class="card-body">
        <div id="monthlySalesChart" style="min-height:300px;"></div>
    </div>
</div>

<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    (function(){
        const labels = @json($salesLabels ?? []);
        const values = @json($salesData ?? []);

        if(!labels.length || !values.length) return;

        const options = {
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: true }
            },
            series: [{
                name: 'المبيعات (ريال)',
                data: values
            }],
            stroke: { curve: 'smooth' },
            xaxis: {
                categories: labels,
                title: { text: 'اليوم' }
            },
            yaxis: {
                title: { text: 'المبيعات (ريال)' , colors: '#fff'}
            },
            tooltip: {
                y: { formatter: function (val) { return new Intl.NumberFormat().format(val) + ' ريال' ; } },
                style: {
                    color: '#003d82'
                }
            },
            dataLabels: { enabled: false },
            fill: { opacity: 0.3 }
        };

        const chart = new ApexCharts(document.querySelector('#monthlySalesChart'), options);
        chart.render();
    })();
</script>


        <div class="data-card" style="margin-top:20px;">
            <div class="card-header">
                <h2><i class="fas fa-building"></i> إحصائيات الوحدات حسب المشاريع</h2>
            </div>
            <div class="card-body">
                <div id="projectsUnitsChart" style="min-height:400px;"></div>
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

@endsection