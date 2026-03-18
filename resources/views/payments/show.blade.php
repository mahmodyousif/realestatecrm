@extends('layout') 

@section('title')
    <h1><i class="fas fa-user-tie"></i> {{$saleCustomer->customer->name ?? 'العميل'}}</h1>
@endsection

@section('content')

<div class="paymentsPage">


      <!-- ملخص الدفعات -->
    @php
        $totalPaid = $payments->sum('amount_paid');
        $remaining = max(0, $saleCustomer->share_amount - $totalPaid);
    @endphp
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-top: 2rem;">
        <div class="card" style="padding: 1.5rem;">
            <p style="color: var(--text-muted); margin: 0 0 0.5rem 0;">إجمالي المبلغ المستحق</p>
            <h3 style="margin: 0; color: var(--primary-color);">{{ number_format($saleCustomer->share_amount) }} ريال</h3>
        </div>
        <div class="card" style="padding: 1.5rem;">
            <p style="color: var(--text-muted); margin: 0 0 0.5rem 0;">المبلغ المدفوع</p>
            <h3 style="margin: 0; color: var(--success-color);">{{ number_format($totalPaid) }} ريال</h3>
        </div>
        <div class="card" style="padding: 1.5rem;">
            <p style="color: var(--text-muted); margin: 0 0 0.5rem 0;">المبلغ المتبقي</p>
            <h3 style="margin: 0; color: {{ $remaining > 0 ? 'var(--danger-color)' : 'var(--success-color)' }};">{{ number_format($remaining) }} ريال</h3>
        </div>
    
    </div>
    <!-- معلومات الوحدة والعملية -->
    <div class="card" style="margin: 2rem 0 ;">
        <div class="card-header">
            <h2><i class="fas fa-info-circle"></i> معلومات العملية</h2>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                <div>
                    <label style="color: var(--text-muted); font-weight: 600;">الوحدة</label>
                    <p style="margin: 0.5rem 0;">{{ $unitSale->unit->unit_number }} ({{ $unitSale->unit->type }})</p>
                </div>
                <div>
                    <label style="color: var(--text-muted); font-weight: 600;">المشروع</label>
                    <p style="margin: 0.5rem 0;">{{ $unitSale->unit->project->name }}</p>
                </div>
                <div>
                    <label style="color: var(--text-muted); font-weight: 600;">العميل المشتري</label>
                    <p style="margin: 0.5rem 0;">{{ $saleCustomer->customer->name }}</p>
                </div>
                <div>
                    <label style="color: var(--text-muted); font-weight: 600;">نسبة التمليك</label>
                    <p style="margin: 0.5rem 0;">{{ number_format($saleCustomer->share_percentage, 2) }}%</p>
                </div>
                <div>
                    <label style="color: var(--text-muted); font-weight: 600;">مبلغ الحصة</label>
                    <p style="margin: 0.5rem 0;">{{ number_format($saleCustomer->share_amount) }} ريال</p>
                </div>
                <div>
                    <label style="color: var(--text-muted); font-weight: 600;">رقم العقد</label>
                    <p style="margin: 0.5rem 0;">{{ $saleCustomer->contract_number }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- سجل الدفعات -->
    <div class="card">
        <div class="card-header">
            <h2>
                <i class="fas fa-history"></i> سجل الدفعات: 
                <span>{{ $saleCustomer->customer->name }}</span>
            </h2>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-money-bill-wave"></i> قيمة الدفعة</th>
                        <th><i class="fas fa-calendar-alt"></i> تاريخ الاستلام</th>
                        <th><i class="fas fa-credit-card"></i> طريقة الدفع</th>
                        <th><i class="fas fa-hashtag"></i> الرقم المرجعي</th>
                        <th><i class="fa fa-pen"></i> ملاحظات</th>
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
                        <td>
                            {{$payment->notes ?? '---'}}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem; color: var(--text-muted);">
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