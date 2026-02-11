@extends('layout') 

@section('title')
    <h1><i class="fas fa-user-tie"></i> {{$unitSale->buyer->name}}</h1>
@endsection

@section('content')

<div class="paymentsPage">
    <div class="card">
        <div class="card-header">
            <h2>
                <i class="fas fa-history"></i> سجل دفعات العميل: 
                <span>{{$unitSale->buyer->name}}</span>
            </h2>
            <a href="{{route('paymentCustomer.export' , $unitSale->id)}}" class="export-btn">
                <i class="fas fa-file-excel"></i> تصدير التقرير
            </a>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-money-bill-wave"></i> قيمة الدفعة</th>
                        <th><i class="fas fa-calendar-alt"></i> تاريخ الاستلام</th>
                        <th><i class="fas fa-credit-card"></i> طريقة الدفع</th>
                        <th><i class="fas fa-hashtag"></i> الرقم المرجعي</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                    <tr>
                        <td class="amount-cell">
                            {{number_format($payment->amount_paid)}} ريال
                        </td>
                        <td>
                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('Y/m/d') }}
                        </td>
                        <td>
                            @if($payment->payment_method == 'cash')
                                <span class="method-badge"><i class="fas fa-money-bill-alt"></i> نقداً</span>
                            @elseif($payment->payment_method == 'transfer')
                                <span class="method-badge"><i class="fas fa-university"></i> تحويل بنكي</span>
                            @else
                                <span class="method-badge"><i class="fas fa-money-check"></i> شيك</span>
                            @endif
                        </td>
                        <td>
                            <code style="background: var(--input-bg); padding: 2px 6px; border-radius: 4px;">
                                {{$payment->reference_number ?? '---'}}
                            </code>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                            لا يوجد دفعات مسجلة لهذا العميل حتى الآن.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection