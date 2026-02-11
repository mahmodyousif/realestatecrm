@extends('layout')

@section('content')
<div class="customer-profile-container">
    {{-- رأس الصفحة --}}
    <div class="profile-header-premium">
        <div class="header-main-info">
            <div class="avatar-box">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="text-content">
                <div class="badge-group">
                    <span class="type-badge">
                        @if($customer->type =='buyer') مشتري @elseif($customer->type =='marketer') مسوق @else مستثمر @endif
                    </span>
                    <span class="date-badge"><i class="far fa-calendar-alt"></i> عضو منذ: {{ $customer->created_at->format('Y-m-d') }}</span>
                </div>
                <h1>{{ $customer->name }}</h1>
                <div class="contact-links">
                    <span><i class="fas fa-phone"></i> {{ $customer->phone }}</span>
                    <span><i class="fas fa-envelope"></i> {{ $customer->email }}</span>
                </div>
            </div>
            
        </div>
        <div id="paymentDonut"></div>
       
    </div>
        <div class="header-actions">
            <a href="{{ route('customers.exportFull', $customer->id) }}" class="btn-export">
                <i class="fas fa-file-export"></i> تصدير تقرير العميل
            </a>
        </div>
    {{-- الإحصائيات السريعة --}}
    <div class="dashboard-stats-grid">
        <div class="mini-stat-card">
            <div class="icon-wrap accent-bg"><i class="fas fa-home"></i></div>
            <div class="stat-data">
                <span class="label">الوحدات المملوكة</span>
                <strong class="value">{{ $customer->purchases_count }} <small>وحدات</small></strong>
            </div>
        </div>
        <div class="mini-stat-card">
            <div class="icon-wrap blue-bg"><i class="fas fa-money-bill-wave"></i></div>
            <div class="stat-data">
                <span class="label">إجمالي الاستحقاق</span>
                <strong class="value">{{ number_format($customer->totalPrice) }} <small>ريال</small></strong>
            </div>
        </div>
        <div class="mini-stat-card">
            <div class="icon-wrap green-bg"><i class="fas fa-check-circle"></i></div>
            <div class="stat-data">
                <span class="label">إجمالي المدفوع</span>
                <strong class="value">{{ number_format($customer->totalPaid) }} <small>ريال</small></strong>
            </div>
        </div>
        <div class="mini-stat-card">
            <div class="icon-wrap {{ $remaining > 0 ? 'red-bg' : 'gold-bg' }}"><i class="fas fa-clock"></i></div>
            <div class="stat-data">
                <span class="label">المتبقي المطلوب</span>
                <strong class="value">
                    @if($remaining == 0) خلاص تام @else {{ number_format($remaining) }} <small>ريال</small> @endif
                </strong>
            </div>
        </div>
    </div>



    {{-- جدول المشتريات --}}
    <div class="main-content-card">
        <div class="card-title-area">
            <h2><i class="fas fa-shopping-cart"></i> سجل الحيازة العقارية</h2>
            <p>قائمة بجميع الوحدات المرتبطة بهذا العميل وحالتها المالية</p>
        </div>

        <div class="table-frame">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>تفاصيل الوحدة</th>
                        <th>القيمة الإجمالية</th>
                        <th>تم سداد</th>
                        <th>الرصيد المتبقي</th>
                        <th>تاريخ العملية</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customer->purchases as $index => $pur)
                    <tr>
                        <td><span class="index-num">{{ $index + 1 }}</span></td>
                        <td>
                            <div class="unit-info-cell">
                                <span class="u-type">{{ $pur->unit->type }}</span>
                                <span class="u-id">رقم {{ $pur->unit->unit_number }}</span>
                            </div>
                        </td>
                        <td><span class="price-text">{{ number_format($pur->total_price) }} ريال</span></td>
                        <td><span class="paid-text">{{ number_format($pur->payments->sum('amount_paid')) }} ريال</span></td>
                        <td>
                            <span class="remaining-text {{ ($pur->total_price - $pur->payments->sum('amount_paid')) > 0 ? 'active' : '' }}">
                                {{ number_format($pur->total_price - $pur->payments->sum('amount_paid')) }} ريال
                            </span>
                        </td>
                        <td><span class="date-text">{{ $pur->sale_date }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>


    
<script>

    
    const totalPaid = Number({{$customer->totalPaid}}) ; 
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