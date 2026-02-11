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
                <a href="{{route('edit_unit', $unit)}}" class="edit"><i class="fa-solid fa-pen-to-square"></i></a>
                <span class="divider">|</span>
                <a href="{{route('delete_unit', $unit)}}" class="delete"><i class="fa fa-trash"></i></a>
            </div>
        </div>

        @php 
            $statusData = [
                'sold' => ['label' => 'مباعة', 'class' => 'sold'],
                'reserved' => ['label' => 'محجوزة', 'class' => 'reserved'],
                'available' => ['label' => 'متاح', 'class' => 'available']
            ];
            $currentStatus = $statusData[$unit->status] ?? $statusData['available'];
        @endphp

        <table class="property-table">
            <tr>
                <th><i class="fas fa-building"></i> المشروع</th>
                <td>{{$unit->project->name}}</td>
            </tr>
            <tr>
                <th><i class="fas fa-home"></i> نوع الوحدة</th>
                <td>{{$unit->type}}</td>
            </tr>
            <tr>
                <th><i class="fas fa-hashtag"></i> رقم الوحدة</th>
                <td>{{$unit->unit_number}}</td>
            </tr>
            <tr>
                <th><i class="fas fa-expand"></i> مساحة الوحدة</th>
                <td>{{$unit->area}} م²</td>
            </tr>
            <tr>
                <th><i class="fas fa-money-bill-wave"></i> السعر</th>
                <td class="price-val">{{number_format($unit->price)}} ريال</td>
            </tr>
            <tr>
                <th><i class="fas fa-info-circle"></i> الحالة</th>
                <td><span class="badge {{$currentStatus['class']}}">{{$currentStatus['label']}}</span></td>
            </tr>

            @if(in_array($unit->status, ['sold', 'reserved']))
            <tr class="highlight-row">
                <th><i class="fas fa-cash-register"></i> المبلغ المدفوع</th>
                <td>{{ number_format($totalPaid) }} ريال</td>
            </tr>
            @endif

            @if($unit->status === 'reserved')
            <tr class="highlight-row">
                <th><i class="fas fa-clock"></i> المبلغ المتبقي</th>
                <td style="color: var(--danger-color)">{{number_format($remaining)}} ريال</td>
            </tr>
            @endif

            <tr>
                <th><i class="fas fa-user"></i> المشتري</th>
                <td>{{$unit->unitSale->buyer->name ?? '-'}}</td>
            </tr>
        </table>
    </div>
</div>
@endsection