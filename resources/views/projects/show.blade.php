@extends('layout')

@section('title')
    <h1>🏠 {{$project->name}}</h1>
@endsection

@section('content')
<div class="project-details-page">

    <div class="export-container">
        <a href="{{ route('projects.info.export', $project->id) }}" class="btn-export">
            <i class="fas fa-file-export"></i> تصدير تقرير المشروع
        </a>
        <a href="{{route('project.preview', $project)}}"  class="btn btn-primary">معاينة</a>

    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <h3>قيمة المشروع</h3>
            <div class="amount profit">{{number_format($project->price)}} ريال</div>
        </div>
        <div class="summary-card">
            <h3>إجمالي قيمة الوحدات</h3>
            <div class="amount profit">{{number_format($totalPrice)}} ريال</div>
        </div>

        <div class="summary-card">
            <h3>إجمالي المبيعات (عقود)</h3>
            <div class="amount income">{{number_format($totalSoldPrice)}} ريال</div>
        </div>

        <div class="summary-card">
            <h3>التحصيل الفعلي</h3>
            <div class="amount income">{{number_format($totalPaid)}} ريال</div>
        </div>

        <div class="summary-card">
            <h3>المبالغ المتبقية بالذمة</h3>
            <div class="amount expense">{{number_format($totalRemaining)}} ريال</div>
        </div>

        <div class="summary-card">
            <h3>وحدات مباعة</h3>
            <div class="amount">{{$soldUnitsCount}}</div>
        </div>

        <div class="summary-card">
            <h3>وحدات محجوزة</h3>
            <div class="amount">{{$reservedUnitsCount}}</div>
        </div>

        <div class="summary-card">
            <h3>وحدات متاحة</h3>
            <div class="amount">{{$availableUnitsCount}}</div>
        </div>
    </div>

    <div class="unit_info">
        <div class="table-container">
            <div class="table-header">
                <h3><i class="fas fa-info-circle"></i> معلومات المشروع الأساسية</h3>
                <div class="action-icons">
                    <a href="{{route('edit_project', $project)}}" class="edit"><i class="fa-solid fa-pen-to-square"></i></a>
                    <span>|</span>
                    <a href="{{route('delete_project', $project)}}" class="delete"><i class="fa fa-trash"></i></a>
                </div>
            </div>
            <table class="property-table">
                <tr>
                    <th><i class="fas fa-layer-group"></i> عدد الطوابق</th>
                    <td>{{$project->floors}}</td>
                    <th><i class="fas fa-door-open"></i> عدد الوحدات</th>
                    <td>{{$project->total_units}}</td>
                </tr>
                <tr>
                    <th><i class="fas fa-ruler-combined"></i> نطاق المساحات</th>
                    <td>{{$project->aria_range}}</td>
                    <th><i class="fas fa-map-marker-alt"></i> الموقع</th>
                    <td>{{$project->location}}</td>
                </tr>
                <tr>
                    <th><i class="fas fa-star"></i> حالة المشروع</th>
                    <td colspan="3">
                        @php
                            $pStatus = match($project->status) {
                                'planning' => 'تحت الإنشاء',
                                'active' => 'نشط',
                                default => 'مكتمل'
                            };
                        @endphp
                        {{ $pStatus }}
                    </td>
                </tr>
            </table>
        </div>

         <div class="data-card" style="margin-top:20px;">
            <div class="card-header">
                <h2><i class="fas fa-building"></i>ملخص الدفعات المالية</h2>
            </div>
            <div class="card-body">
                <div id="paymentDonut" style="min-height:400px;"></div>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h3><i class="fas fa-list"></i> قائمة جرد الوحدات</h3>
            </div>
            <div style="overflow-x: auto;">
                <table class="property-table">
                    <thead>
                        <tr>
                            <th>النوع</th>
                            <th>رقم الوحدة</th>
                            <th>المساحة</th>
                            <th>الطابق</th>
                            <th>السعر</th>
                            <th>المشتري</th>
                            <th>المسوق</th>
                            <th>المدفوع</th>
                            <th>المتبقي</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($project->units as $unit)
                            @php
                                $amountPaid = $unit->unitSale?->payments->sum('amount_paid') ?? 0;
                                $unitPrice = $unit->price ?? 0;
                                $remaining = $unitPrice - $amountPaid;
                                
                                $statusClass = match($unit->status) {
                                    'sold' => 'sold',
                                    'reserved' => 'reserved',
                                    default => 'available'
                                };
                                $statusLabel = match($unit->status) {
                                    'sold' => 'مباعة',
                                    'reserved' => 'محجوزة',
                                    default => 'متاحة'
                                };
                            @endphp 
                            <tr>
                                <td>{{$unit->type}}</td>
                                <td><strong>{{$unit->unit_number}}</strong></td>
                                <td>{{$unit->area}} م²</td>
                                <td>{{$unit->floor}}</td>
                                <td>{{number_format($unitPrice)}}</td> 
                                <td>
                                    @if($unit->unitSale && $unit->unitSale->saleCustomers->count() > 0)
                                        {{ $unit->unitSale->customer_names }}
                                    @else
                                        {{ $unit->unitSale?->buyer?->name ?? '-' }}
                                    @endif
                                </td>
                                <td>{{$unit->unitSale?->marketer?->name ?? '-'}}</td>
                                <td class="income">{{number_format($amountPaid)}}</td> 
                                <td class="expense">{{number_format($remaining)}}</td> 
                                <td>
                                    <span class="status-badge {{$statusClass}}">{{$statusLabel}}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

  

<script>
    const totalPaid = Number({{$totalPaid}}) ; 
    const remaining = Number({{$totalRemaining}}) ;
        const options = {
        chart: {
            type: 'donut',
            height: 350
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