@extends('layout')

@section('title')
    <h1 class="text-main">🏠 {{ $unit->type . ' ' . $unit->unit_number }}</h1>
@endsection

@section('content')
<div class="unit_info">
    <div class="table-container">
        <div class="table-header">
            <h3>تفاصيل الوحدة السكنية</h3>
            <div class="action-icons">
                @if ($unit->status === 'available')
                    <a href='{{ route('unit.sell', $unit) }}' class="sell btn-sell-mini">بيع</a>
                     <span class="divider">|</span>
                @endif
                <a href="{{route('edit_unit', $unit)}}" class="edit"><i class="fa-solid fa-pen-to-square"></i></a>
                <span class="divider">|</span>
                <a href="{{route('delete_unit', $unit)}}" class="delete"><i class="fa fa-trash"></i></a>
            </div>
        </div>

        @php 
            $statusData = [
                'sold' => ['label' => 'مباعة', 'class' => 'sold'],
                'reserved' => ['label' => 'محجوزة', 'class' => 'reserved'],
                'available' => ['label' => 'جاهزة للبيع', 'class' => 'available'],
                'partially_paid' => ['label' => 'مدفوعة جزئياً', 'class' => 'partially_paid']
            ];
            $currentStatus = $statusData[$unit->status] ?? $statusData['available'];
        @endphp

        <table class="property-table">
            <tr>
                <th><i class="fas fa-building"></i> الشركة</th>
                <td>{{$unit->project->company->name}}</td>
            </tr>
            <tr>
                <th><i class="fas fa-building"></i> المشروع</th>
                <td>{{$unit->project->name}}</td>
            </tr>
            <tr>
                <th><i class="fas fa-hashtag"></i> نموذج الوحدة</th>
                <td>{{$unit->unit_number}}</td>
            </tr>
            <tr>
                <th><i class="fas fa-home"></i> نوع الوحدة</th>
                <td>{{$unit->type}}</td>
            </tr>
            <tr>
                <th><i class="fas fa-building"></i> الطابق </th>
                <td>{{$unit->floor}}</td>
            </tr>

            <tr>
                <th><i class="fas fa-map-marker-alt"></i> الزون </th>
                <td>{{$unit->zone}}</td>
            </tr>
            <tr>
                <th><i class="fas fa-expand"></i> مساحة الوحدة</th>
                <td>{{$unit->area}} م²</td>
            </tr>
            <tr>
                <th><i class="fas fa-money-bill-wave"></i> قيمة الوحدة</th>
                <td class="price-val">{{number_format($unit->price)}} ريال</td>
            </tr>
            <tr>
                <th><i class="fas fa-info-circle"></i> الحالة</th>
                <td><span class="badge {{$currentStatus['class']}}">{{$currentStatus['label']}}</span></td>
            </tr>

            @if($unit->unitSale)

                <tr >
                     <th colspan="2" class="table-header-centered">تفاصيل البيع</th>
                </tr>

                <tr class="highlight-row">
                    <th><i class="fas fa-cash-register"></i> قيمة الخصم</th>
                    <td>{{ number_format($unit->unitSale->discount) }} ريال</td>
                </tr>

                <tr class="highlight-row">
                    <th><i class="fas fa-cash-register"></i> السعر النهائي</th>
                    <td>{{ number_format($unit->unitSale->total_price) }} ريال</td>
                </tr>
            
            
                <tr class="highlight-row">
                    <th><i class="fas fa-cash-register"></i> المبلغ المدفوع</th>
                    <td>{{ number_format($totalPaid) }} ريال</td>
                </tr>
            

            
                <tr class="highlight-row">
                    <th><i class="fas fa-clock"></i> المبلغ المتبقي</th>
                    <td class="text-danger">{{number_format($remaining)}} ريال</td>
                </tr>
            

                <tr>
                    <th><i class="fas fa-users"></i> المشترون</th>
                    <td>
                        @if($unit->unitSale && $unit->unitSale->saleCustomers->count() > 0)
                            <div class="customers-list">
                                @foreach($unit->unitSale->saleCustomers as $saleCustomer)
                                    <div class="customer-item">
                                        <strong>{{ $saleCustomer->customer->name }}</strong>
                                        <br>
                                        <small>
                                            الحصة: {{ $saleCustomer->share_percentage }}% |
                                            العقد: {{ $saleCustomer->contract_number }} |
                                            المبلغ: {{ number_format($saleCustomer->share_amount) }} ريال
                                        </small>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            {{ $unit->unitSale->buyer->name ?? '-' }}
                        @endif
                    </td>
                </tr>

                <tr>
                    <th><i class="fas fa-user-tie"></i> المسوق الرئيسي</th>
                    <td>{{$unit->unitSale->marketer->name ?? '-'}}</td>
                </tr>

                <tr>
                    <th><i class="fas fa-money-bill-wave"></i> قيمة العمولة</th>
                    <td>{{number_format($unit->unitSale->commission) ?? 0 }} ر.س</td>
                </tr>
            @endif

          
        </table>
    </div>
</div>
@endsection