@extends('layout')

@section('title')
    <h1 style="color: var(--text-main)">🏠 {{ $unit->type . ' ' . $unit->unit_number }}</h1>
@endsection

@section('content')
<div class="unit_info">
    <div class="table-container">
        <div class="table-header">
            <h3>تفاصيل الوحدة السكنية</h3>
            <div class="action-icons">
                @if ($unit->status === 'available')
                <button class="btn-sell-mini"
                    data-unit-id="{{ $unit->id }}"
                    data-unit-name="{{ $unit->type }} {{ $unit->unit_number }}"
                    data-project-name="{{ $unit->project->name }}"
                    data-price="{{ $unit->price }}"
                    onclick="openSellUnitModal(this)" data-bs-target="#openSellUnitModal-{{ $unit->id }}">
                    بيع
                </button>
                <x-unit-sell-modal :unit="$unit" :buyers="$buyers" :investors="$investors" :marketers="$marketers" />
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
                'available' => ['label' => 'جاهزة للبيع', 'class' => 'available']
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
                     <th colspan="2" style="text-align: center; font-weight: bold; color:">تفاصيل البيع</th>
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
                    <td style="color: var(--danger-color)">{{number_format($remaining)}} ريال</td>
                </tr>
            

                <tr>
                    <th><i class="fas fa-user"></i> المشتري</th>
                    <td>
                        {{
                            $unit->unitSale->buyer->name  ?? '-'
                        }}
                    </td>
                </tr>

                
                <tr>
                    <th><i class="fas fa-user-tie"></i> المستثمر</th>
                    <td>{{$unit->unitSale->investor->name ?? '-'}}</td>
                </tr>
                <tr>
                    <th><i class="fas fa-user-tie"></i> المسوق الرئيسي</th>
                    <td>{{$unit->unitSale->marketer->name ?? '-'}}</td>
                </tr>
                
    
                <tr>
                    <th><i class="fas fa-money-bill-wave"></i> قيمة العمولة</th>
                    <td>{{number_format($unit->unitSale->commission) ?? 0 }} ر.س</td>
                </tr>
                
                <tr>
                    <th><i class="fas fa-user"></i> رقم العقد</th>
                    <td>{{$unit->unitSale->contract_number ?? '-'}}</td>
                </tr>
            @endif

          
        </table>
    </div>
</div>
@endsection