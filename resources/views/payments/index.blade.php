@extends('layout') 

@section('title')
    <div class="page-header">
        <h1>💰 إدارة الدفعات المالية</h1>
        <p>تابع جميع عمليات الدفع، أضف دفعات جديدة، واطلع على التقارير المالية</p>
    </div>
@endsection

@section('content')
<div class="paymentsPage">
    <div class="stats-overview">
        <div class="stat-box collected">
            <span><i class="fas fa-file-invoice-dollar"></i> إجمالي قيمة العقود</span>
            <p>{{ number_format($totalPrice) }} ريال</p>
        </div>
        <div class="stat-box pending-today">
            <span><i class="fas fa-hand-holding-usd"></i> إجمالي المحصل</span>
            <p>{{ number_format($totalPaid) }} ريال</p>
        </div>
        <div class="stat-box remaining">
            <span><i class="fas fa-clock"></i> إجمالي المتبقي للتحصيل</span>
            <p>{{ number_format($remaining) }} ريال</p>
        </div>
    </div>



    <div class="filter-section">
        <form action="{{route('payments')}}" method="GET" class="filter-form">
            <div class="filter-info">
                <i class="fas fa-filter"></i>
                <div>
                    <h4>تخصيص النطاق الزمني</h4>
                    <p>استخرج تقارير لفترة محددة</p>
                </div>
            </div>
            <div class="filter-inputs">
                <div class="input-group">
                    <label>من تاريخ</label>
                    <input type="date" name="from" value="{{ request('from') }}">
                </div>
                <div class="input-group">
                    <label>إلى تاريخ</label>
                    <input type="date" name="to" value="{{ request('to') }}">
                </div>
                <button type="submit" class="btn-refresh">
                    تحديث <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </form>
    </div>
    <div class="card">
        <div class="card-header">
            <h2>سجل دفعات العملاء</h2>
            <button class="add-btn" onclick="togglePaymentModal()">
                <i class="fas fa-plus"></i> تسجيل دفعة جديدة
            </button>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>رقم العقد</th>
                        <th>الوحدة</th>
                        <th>اسم العميل</th>
                        <th>حصة العميل %</th>
                        <th>قيمة الحصة</th>
                        <th>المدفوع</th>
                        <th>المتبقي</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($saleCustomers as $saleCustomer)
                    <tr class="{{ $saleCustomer->payments()->sum('amount_paid') >= $saleCustomer->share_amount ? 'completed-payment' : 'pending-payment' }}">
                        <td><strong>#{{ $saleCustomer->contract_number }}</strong></td>
                        <td>
                            {{ $saleCustomer->unitSale->unit->type ?? '' }} - 
                            {{ $saleCustomer->unitSale->unit->unit_number ?? '' }}
                        </td>
                        <td>{{ $saleCustomer->customer->name ?? '-' }}</td>
                        <td>{{ number_format($saleCustomer->share_percentage, 2) }}%</td>
                        <td class="text-primary font-bold">
                            {{ number_format($saleCustomer->share_amount) }} ريال
                        </td>
                        <td class="text-success font-bold">
                            {{ number_format($saleCustomer->payments()->sum('amount_paid')) }} ريال
                        </td>
                        <td class="text-danger font-bold">
                            {{ number_format($saleCustomer->share_amount - $saleCustomer->payments()->sum('amount_paid')) }} ريال
                        </td>
                        <td>
                            @if($saleCustomer->payments()->sum('amount_paid') >= $saleCustomer->share_amount)
                                <span class="badge badge-success">مكتمل</span>
                            @else
                                <span class="badge badge-warning">يوجد أقساط</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{route('payments.show' , $saleCustomer->id ) }}"class="action-link view" title="عرض"><i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="empty-cell">
                            لا توجد عمليات بيع
                        </td>
                    </tr>
                    @endforelse 
                </tbody>
            </table>
        </div>
    </div>
</div>





<div id="paymentModal" class="modal">
    <div class="modal-content">

        <div class="modal-header">
            <h2><i class="fas fa-receipt"></i> تسجيل دفعة مالية جديدة</h2>
        </div>

        <form method="POST" action="{{ route('add_payment') }}">
            @csrf

            <div class="form-grid-2">

       
            <div class="form-group">
                <label>الوحدة والعميل (المستحقة)</label>
                <select name="unit_sale_customer_id" required>
                    <option value="">اختر العملية...</option>
                    @foreach($saleCustomers as $saleCustomer)
                        @php
                            $paid = $saleCustomer->payments()->sum('amount_paid');
                            $remaining = $saleCustomer->share_amount - $paid;
                        @endphp
                        @if($remaining > 0)
                            <option value="{{ $saleCustomer->id }}">
                                {{ $saleCustomer->unitSale->unit->unit_number ?? '' }} | 
                                {{ $saleCustomer->customer->name ?? '-' }} 
                                (المتبقي: {{ number_format($remaining) }})
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            
            <div class="form-group">
                <label>المبلغ المدفوع</label>
                <input type="number" name="amount_paid" step="0.01" required>
            </div>

            <div class="form-group">
                <label>تاريخ الدفع</label>
                <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="form-group">
                <label>وسيلة الدفع</label>
                <select name="payment_method" required>
                    <option value="cash">نقداً</option>
                    <option value="transfer">تحويل بنكي</option>
                    <option value="check">شيك</option>
                </select>
            </div>

            <div class="form-group">
                <label>الرقم المرجعي</label>
                <input type="text" name="reference_number">
            </div>

            <div class="form-group">
                <label>ملاحظات</label>
                <textarea name="notes" rows="2"></textarea>
            </div>
     </div>
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="togglePaymentModal()">
                    إلغاء
                </button>
                <button type="submit" class="btn-save">
                    حفظ العملية
                </button>
            </div>

        </form>
    </div>
</div>


@endsection