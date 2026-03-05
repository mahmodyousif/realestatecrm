@extends('layout')

@section('title')
<div class="page-header">
    <div class="header-info">
        <h1>🏢 {{ $company->name }}</h1>
        <p>عرض شامل لإحصائيات الشركة، المشاريع، والوحدات التابعة</p>
    </div>
</div>
@endsection

@section('content')
<div class="company-detail-wrapper">
    <div class="header-actions" style="justify-content: space-between">
        <a href="{{ route('company.export', $company->id) }}" class="btn-export">
            <i class="fas fa-file-excel"></i> تصدير Excel
        </a>   
        <div class="unit-actions-nested">
            <a href="{{ route('company.edit', $company)}}" class="action-link edit" title="تعديل"><i class="fas fa-edit"></i></a>
            <form action="{{ route('company.destroy' , $company)}}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="action-link delete"
                        title="حذف"
                        onclick="return confirm('هل أنت متأكد من حذف هذه الشركة؟سيتم حذف الشركة بكافة مشاريعها ووحداتها، لا يمكن التراجع عن العملية');">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </div>
    @if (session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif


    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="card-icon"><i class="fa-solid fa-city"></i></div>
            <div class="card-info">
                <h3>إجمالي المشاريع</h3>
                <h2 class="count">{{ $company->projects_count }}</h2>

                <span class="trend">+{{$projectCountThisMonth}} هذا الشهر</span>
            </div>
        </div>

        <div class="stat-card green">
            <div class="card-icon"><i class="fa-solid fa-door-open"></i></div>
            <div class="card-info">
                <h3>الوحدات المتاحة</h3>
                <h2 class="count">{{ $company->available_units_count }}</h2>
                <div class="trend neutral">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>متاحة للبيع</span>
                </div>
            </div>
        </div>

        <div class="stat-card red">
            <div class="card-icon"><i class="fa-solid fa-hand-holding-dollar"></i></div>
            <div class="card-info">
                <h3>الوحدات المباعة</h3>
                <h2 class="count">{{ $company->sold_units_count }}</h2>
                <div class="trend neutral">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>تم البيع</span>
                </div>
             </div>
        </div>

        <div class="stat-card orange">
            <div class="card-icon"><i class="fa-solid fa-key"></i></div>
            <div class="card-info">
                <h3>الوحدات المحجوزة</h3>
                <h2 class="count">{{ $company->reserved_units_count }}</h2>
                <div class="trend neutral">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>تم الحجز</span>
                </div>
            </div>
        </div>
    </div>
    {{-- <div class="company-stats-grid">
        <div class="stat-card project">
            <div class="card-inner">
                <div class="icon-box">
                    <i class="fa-solid fa-city"></i>
                </div>
                <div class="data-box">
                    <span class="label">إجمالي المشاريع</span>
                    <h2 class="value">{{ $company->projects_count }}</h2>
                    <div class="trend positive">
                        <i class="fa-solid fa-arrow-up-right"></i>
                        <span>+{{$projectCountThisMonth}} هذا الشهر</span>
                    </div>
                </div>
            </div>
        </div>
    
        <div class="stat-card available">
            <div class="card-inner">
                <div class="icon-box">
                    <i class="fa-solid fa-door-open"></i>
                </div>
                <div class="data-box">
                    <span class="label">الوحدات المتاحة</span>
                    <h2 class="value">{{ $company->available_units_count }}</h2>
                    <div class="trend neutral">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>متاحة للبيع</span>
                    </div>
                </div>
            </div>
        </div>
    
        <div class="stat-card sold">
            <div class="card-inner">
                <div class="icon-box">
                    <i class="fa-solid fa-handshake"></i>
                </div>
                <div class="data-box">
                    <span class="label">الوحدات المباعة</span>
                    <h2 class="value">{{ $company->sold_units_count }}</h2>
                    <div class="trend positive">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>نمو 12%</span>
                    </div>
                </div>
            </div>
        </div>
    
        <div class="stat-card reserved">
            <div class="card-inner">
                <div class="icon-box">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div class="data-box">
                    <span class="label">الوحدات المحجوزة</span>
                    <h2 class="value">{{ $company->reserved_units_count }}</h2>
                    <div class="trend warning">
                        <i class="fa-solid fa-hourglass-half"></i>
                        <span>قيد الإجراء</span>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    <div class="financial-grid">
        <div class="fin-card blue">
            <div class="label">إجمالي المبيعات</div>
            <div class="value">{{ number_format($totalSalesPrice) }} <small>ر.س</small></div>
        </div>
        <div class="fin-card green">
            <div class="label">الإيرادات المحققة</div>
            <div class="value">{{ number_format($amountPaid) }} <small>ر.س</small></div>
        </div>
        <div class="fin-card red">
            <div class="label">المبالغ المتبقية</div>
            <div class="value">{{ number_format($remainingAmount) }} <small>ر.س</small></div>
        </div>
           <div class="fin-card yellow">
            <div class="label">إجمالي العمولات</div>
            <div class="value">{{ number_format($totalCommission) }} <small>ر.س</small></div>
        </div>
    </div>

    <div class="performance-row">
        <div class="perf-box">
            <h4><i class="far fa-calendar-check"></i> أداء اليوم</h4>
            <div class="mini-grid">
                <div class="mini-item">
                    <span>وحدات مباعة</span>
                    <strong>{{ number_format($todaySalesCount) }}</strong>
                </div>
                <div class="mini-item">
                    <span>مدفوعات اليوم</span>
                    <strong class="highlight">{{ number_format($amountPaidToday) }} ر.س</strong>
                </div>
            </div>
        </div>
        <div class="perf-box">
            <h4><i class="far fa-calendar-alt"></i> أداء الشهر الحالي</h4>
            <div class="mini-grid">
                <div class="mini-item">
                    <span>وحدات مباعة</span>
                    <strong>{{ number_format($currentMonthSalesCount) }}</strong>
                </div>
                <div class="mini-item">
                    <span>إجمالي التحصيل</span>
                    <strong class="highlight">{{ number_format($currentMonthSalesPayment) }} ر.س</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="data-section">
        <div class="table-card">
            <div class="card-header">
                <h3><i class="fas fa-building"></i> قائمة المشاريع</h3>
            </div>
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>المشروع</th>
                            <th>عدد الطوابق</th>
                            <th>المنطقة</th>
                            <th>المساحة</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allProjects as $project)
                            <tr>
                                <td class="bold">{{ $project->name }}</td>
                                <td>{{ $project->floors }} طابق</td>
                                <td>{{ $project->location }}</td>
                                <td>{{ $project->aria_range }} م2</td>
                                <td>
                                    <span class="badge {{ $project->status }}">
                                        {{ $project->status === 'completed' ? 'مكتمل' : ($project->status === 'active' ? 'نشط' : 'تحت الإنشاء') }}
                                    </span>
                                </td>
                                <td>
                                   <div class="unit-actions-nested">
                                        <a href="{{route('project.show', $project)}}"  class="action-link view" title="عرض"><i class="fas fa-eye"></i></a>
                                        @can('manager')
                                            <a href="{{route('edit_project', $project)}}" class="action-link edit" title="تعديل"><i class="fas fa-edit"></i></a>
                                        @endcan
                                        
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="pagination-wrapper">
                    {{ $allProjects->links('pagination.custom') }}
                </div>
            </div>
        </div>

        <div class="table-card mt-30">
            <div class="card-header">
                <h3><i class="fas fa-th-large"></i> قائمة الوحدات</h3>
            </div>
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>رقم الوحدة</th>
                            <th>النوع</th>
                            <th>المشروع</th>
                            <th>المساحة</th>
                            <th>الطابق</th>
                            <th>الحالة</th>
                            <th>السعر</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allUnits as $unit)
                            <tr>
                                <td class="bold">{{ $unit->unit_number }}</td>
                                <td>{{ $unit->type }}</td>
                                <td>{{ $unit->project->name }}</td>
                                <td>{{ $unit->area }} م2</td>
                                <td>{{ $unit->floor }}</td>
                                <td>
                                    <span class="badge unit-{{ $unit->status }}">
                                        {{ $unit->status === 'available' ? 'جاهزة للبيع' : ($unit->status === 'reserved' ? 'محجوزة' : 'مباعة') }}
                                    </span>
                                </td>
                                <td class="price">{{ number_format($unit->price) }} ر.س</td>
                                <td>
                                 <div class="unit-actions-nested">
                                    <a href="{{route('units.show' ,$unit)}}" class="action-link view" title="عرض"><i class="fas fa-eye"></i></a>
                                    @can('manager')
                                        <a href="{{route('edit_unit', $unit)}}" class="action-link edit" title="تعديل"><i class="fas fa-edit"></i></a>
                                    @endcan
                                    
                                    @if ($unit->status === 'available')
                                        <a href="{{route('unit.sell', $unit)}}" class="btn-sell-mini">بيع</a>
                                    @endif
                                </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="pagination-wrapper">
                    {{ $allUnits->links('pagination.custom') }}
                </div>
            </div>
        </div>
    </div>
</div>


@endsection