@extends('layout')

@section('title')
    <div class="page-title-main">
        <h1><i class="fas fa-user-tag"></i> تفاصيل المسوّق {{ $marketer->name}}</h1>
        <p>عرض البيانات التفصيلية للمسوّق ومتابعة المبيعات والعمولات والمستحقات المالية</p>
    </div>
@endsection
@section('content')
<div class="marketer-profile-wrapper">
    
    {{-- هيدر البروفايل --}}
    <div class="profile-header-premium">
        <div class="header-main-info">
            <div class="avatar-box">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="text-content">
                <div class="badge-group">
                    <span class="type-badge">
                        @if($marketer->type =='buyer') مشتري @elseif($marketer->type =='marketer') مسوق @else مستثمر @endif
                    </span>
                    <span class="date-badge"><i class="far fa-calendar-alt"></i> عضو منذ: {{ $marketer->created_at->format('Y-m-d') }}</span>
                </div>
                <h1>{{ $marketer->name }}</h1>
                <div class="contact-links">
                    <span><i class="fas fa-phone"></i> {{ $marketer->phone }}</span>
                    <span><i class="fas fa-envelope"></i> {{ $marketer->email }}</span>
                </div>
            </div>
            
        </div>
        <div id="paymentDonut"></div>
       
    </div>
        <div class="header-actions">
            <a href="{{ route('marketer.export', $marketer->id) }}"class="btn-export">
                <i class="fas fa-file-export"></i> تصدير تقرير العميل
            </a>
        </div>
    {{-- كروت الإحصائيات السريعة --}}
    <div class="stats-dashboard">

        <div class="stat-card-modern">
            <div class="icon bg-soft-blue"><i class="fas fa-key"></i></div>
            <div class="data">
                <span class="label">العمولة المستحقة</span>
                <strong class="value">{{ number_format($commission) }} <small>ر.س</small></strong>
            </div>
        </div>
        <div class="stat-card-modern">
            <div class="icon bg-soft-blue"><i class="fas fa-key"></i></div>
            <div class="data">
                <span class="label">الوحدات المباعة</span>
                <strong class="value">{{ $marketer->sales_count }} <small>وحدة</small></strong>
            </div>
        </div>
        <div class="stat-card-modern">
            <div class="icon bg-soft-purple"><i class="fas fa-chart-line"></i></div>
            <div class="data">
                <span class="label">إجمالي المبيعات</span>
                <strong class="value">{{ number_format($marketer->totalPrice ?? 0) }} <small>ريال</small></strong>
            </div>
        </div>
        <div class="stat-card-modern">
            <div class="icon bg-soft-green"><i class="fas fa-check-double"></i></div>
            <div class="data">
                <span class="label">المبالغ المحصلة</span>
                <strong class="value">{{ number_format($marketer->totalPaid ?? 0) }} <small>ريال</small></strong>
            </div>
        </div>
        <div class="stat-card-modern">
            <div class="icon bg-soft-red"><i class="fas fa-hand-holding-usd"></i></div>
            <div class="data">
                <span class="label">المبالغ المتبقية</span>
                <strong class="value {{ ($remaining ?? 0) > 0 ? 'text-red' : '' }}">
                    {{ number_format($remaining ?? 0) }} <small>ريال</small>
                </strong>
            </div>
        </div>
    </div>

    {{-- جدول العمليات --}}
    <div class="content-card-modern">
        <div class="card-header-flex">
            <h2><i class="fas fa-file-invoice"></i> سجل المبيعات والعمليات</h2>
        </div>
        
        <div class="table-responsive-custom">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>الوحدة</th>
                        <th>قيمة العمولة</th>
                        <th>المشتري</th>
                        <th>قيمة البيع</th>
                        {{-- <th>الحالة المالية</th> --}}
                        <th>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($marketer->marketedSales as $sale)
                    <tr>
                        <td>
                            <div class="unit-cell">
                                <span class="u-type">{{ $sale->unit->type }}</span>
                                <span class="u-num">#{{ $sale->unit->unit_number }}</span>
                            </div>
                        </td>
                        
                        <td><span class="buyer-name">{{ $sale->commission ?? '-' }} ر.س</span></td>
                        <td><span class="buyer-name">{{ $sale->buyer->name ?? '-' }}</span></td>
                        <td><span class="price-bold">{{ number_format($sale->total_price) }} ريال</span></td>
                        {{-- <td>
                            <div class="payment-progress-mini">
                                @php 
                                    $paid = $sale->payments->sum('amount_paid');
                                    $percent = ($sale->total_price > 0) ? ($paid / $sale->total_price) * 100 : 0;
                                @endphp
                                <div class="progress-bar-container">
                                    <div class="bar-fill" style="width: {{ $percent }}%"></div>
                                </div>
                                <span class="percent-text">{{ number_format($percent, 0) }}% محصل</span>
                            </div>
                        </td> --}}
                        <td><span class="date-text">{{ $sale->sale_date }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>


<script>
       const totalPaid = Number({{$marketer->totalPaid}}) ; 
    const remaining = Number({{$remaining}}) ;
        const options = {
        chart: {
            type: 'donut',
            height: 250
        },

        series: [totalPaid , remaining],

        labels: ['المدفوع', 'المتبقي'],

        colors: ['#16a34a', '#dc2626'], // أخضر + أحمر

        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return val.toFixed(1) + '%';
            }
        },

        legend: {
            position: 'bottom'
        },

        tooltip: {
            y: {
                formatter: function (value) {
                    return value.toLocaleString() + 'ر.س';
                }
            }
        },

        plotOptions: {
            pie: {
                donut: {
                    size: '65%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'إجمالي المبيعات',
                            formatter: function () {
                                return (totalPaid + remaining).toLocaleString() + ' ر.س';
                            }
                        }
                    }
                }
            }
        }
    };

    const chart = new ApexCharts(
        document.querySelector("#paymentDonut"),
        options
    );

    chart.render();
</script>
@endsection