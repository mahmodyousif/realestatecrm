@extends('layout')

@section('title')
<div class="page-title-main">
    <h1><i class="fas fa-handshake"></i> إدارة عمليات البيع</h1>
    <p>عرض وإدارة كافة عمليات البيع</p>
</div>
@endsection

@section('content')


<div class="main-content-card">
    
        <div class="card-title-area">
            <h2><i class="fas fa-handshake"></i> جدول عمليات البيع</h2>
            <p>عرض جميع العمليات وحالتها المالية والإدارية</p>
        </div>

        <div class="table-frame">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>تفاصيل الوحدة</th>
                        <th>اسم المشتري</th>
                        <th>قيمة الوحدة</th>
                        <th>قيمة الخصم</th>
                        <th>المبلغ المدفوع</th>
                        <th>المتبقي</th>
                        <th>تاريخ العقد</th>
                        <th>الحالة</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                @php $a = 0 @endphp
                @foreach($data as $unit)
                    <tr class="@if($unit->remaining == 0) fully-paid @elseif($unit->remaining < $unit->unit->price) partially-paid @else unpaid @endif">
                        <td>{{ ++$a }}</td>
                        <td>
                        <div class="unit-details">
                            <div>المشروع:
                               <span> {{ $unit->unit->project->name }}</span>
                            </div>
                            <div> النوع:
                                <span>{{ $unit->unit->type }}</span>
                            </div>
                            <div>النموذج:
                                <span>{{ $unit->unit->unit_number }}</span>
                            </div>
                            <div>الطابق:
                                <span>{{ $unit->unit->floor }}</span>

                            </div>
                        </div>
                        </td>
                        <td>{{ $unit->customer_names ?? '-' }}</td>
                        <td>{{ number_format($unit->unit->price) ?? 0 }}</td>
                        <td>{{ number_format($unit->unit->unitSale->discount ?? 0) }}</td>
                        <td>{{ number_format($unit->total_paid ?? 0) }} </td>
                        <td>{{number_format($unit->remaining)}}</td>
                        <td>{{ $unit->unit->unitSale->sale_date }}</td>
                        <td>
                            @if ($unit->unit->status === 'sold')          مباعة
                            @elseif ($unit->unit->status === 'reserved')   محجوزة
                            @elseif ($unit->unit->status === 'partially_paid') دفع جزئي
                            @else جاهزة للبيع
                            @endif 
                      
                        </td>
                        <td>
                            <div class="unit-actions-nested">
                                <a href="{{ route('units.show', $unit->unit) }}" class="action-link view" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('manager')
                                    {{-- <a href="{{ route('edit_sell', $unit) }}" class="action-link edit" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a> --}}
                                    
                                    <a href="{{ route('delete_sell', $unit) }}" class="action-link delete" title="حذف" onclick="return confirm('هل أنت متأكد من حذف عملية البيع هذه ؟')">
                                         <i class="fas fa-trash-alt"></i>
                                    </a>
                                @endcan
                                
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="pagination-wrapper">
                {{ $data->links('pagination.custom') }}
            </div>
        </div>
    </div>


@endsection


