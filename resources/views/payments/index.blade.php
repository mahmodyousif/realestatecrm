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

    <div class="card">
        <div class="card-header">
            <h2>سجل دفعات العملاء</h2>
            <button class="add-btn" onclick="toggleModal()">
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
                        <th>إجمالي السعر</th>
                        <th>المدفوع</th>
                        <th>المتبقي</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($unitSales as $unit)
                    <tr class="{{ $unit->remaining > 0 ? 'pending-payment' : 'completed-payment' }}">
                        <td><strong>#{{ $unit->contract_number }}</strong></td>
                        <td>{{ $unit->unit->type }} - {{ $unit->unit->unit_number }}</td>
                        <td>{{ $unit->buyer->name }}</td>
                        <td>{{ number_format($unit->total_price) }} ريال</td>
                        <td style="color: var(--success-color); font-weight: 700;">{{ number_format($unit->total_paid) }}</td>
                        <td style="color: var(--danger-color); font-weight: 700;">{{ number_format($unit->remaining) }}</td>
                        <td>
                            @if($unit->remaining > 0)
                                <span class="badge" style="background: var(--warning-light); color: var(--warning-dark);">يوجد أقساط</span>
                            @else
                                <span class="badge" style="background: var(--success-light); color: var(--success-dark);">مكتمل</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{route('payments.show' , $unit->id ) }}" class="btn-sm" style="color: var(--primary-color); text-decoration: none;">
                                <i class="fas fa-eye"></i> التفاصيل
                            </a>
                        </td>
                    </tr>
                    @endforeach 
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
                <select name="unit_sale_id" required>
                    <option value="">اختر العملية...</option>
                    @foreach($remainingUnits as $sale)
                        <option value="{{ $sale->unitSale->id }}">
                            {{ $sale->unit_number }} |
                            {{ $sale->unitSale->buyer->name }}
                            (المتبقي: {{ number_format($sale->unitSale->remaining) }})
                        </option>
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
                <button type="button" class="btn-cancel" onclick="toggleModal()">
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