@extends('layout')

@section('title')
    <h1 class="text-main">📝 تعديل عملية بيع الوحدة</h1>
@endsection

@section('content')
<div class="container edit-section">
    @if(session('success'))
        <div class="alert-unified success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <p>الوحدة: <strong>{{ $sale->unit->type }} - {{ $sale->unit->unit_number }}</strong></p>
    <p>المشروع: <strong>{{ $sale->unit->project->name }}</strong></p>

    <script>
        const buyersData = @json($buyers);
        const investorsData = @json($investors);
    </script>

    <form id="clientForm" method="POST" action="{{ route('update_sell', $sale->id) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label><i class="fas fa-discount"></i> الخصم</label>
            <input type="number" name="discount" value="{{ $sale->discount }}">
        </div>

        <div class="form-group">
            <label><i class="fas fa-hashtag"></i> السعر النهائي</label>
            <input type="text" placeholder="مثال: 5A" value="{{ $sale->total_price }}" required name="total_price"/>
        </div>

        <div class="form-group">
            <label><i class="fas fa-ruler-combined"></i>طريقة الدفع</label>
            <select name="payment_method">
                <option value="cash" {{ $sale->payment_method == 'cash' ? 'selected' : '' }}>كاش</option>
                <option value="installment" {{ $sale->payment_method == 'installment' ? 'selected' : '' }}>تقسيط</option>
            </select>
        </div>

        <div class="form-group">
            <label><i class="fas fa-layer-group"></i> تاريخ البيع</label>
            <input type="date" name="sale_date" value="{{ $sale->sale_date }}">
        </div>

        <div class="form-group">
            <label><i class="fas fa-money-bill-wave"></i> السعر</label>
            <input type="number" placeholder="مثال: 500000" value="{{ $sale->unit_price }}" required name="unit_price"/>
        </div>

        <div class="form-group">
            <h3 class="unit-summary-mini">تفاصيل المشترين والحصص</h3>
        </div>

        <div id="buyers-container">
            @foreach($sale->saleCustomers as $index => $sc)
                <div class="buyer-row" data-index="{{ $index }}">
                    <h4>المشتري {{ $index + 1 }}</h4>

                    <div class="form-group">
                        <label>نوع المشتري</label>
                        <select name="customers[{{ $index }}][type]" class="customer-type-select" onchange="toggleBuyerType(this)" required>
                            <option value="customer" {{ $sc->type == 'customer' ? 'selected' : '' }}>مشتري</option>
                            <option value="investor" {{ $sc->type == 'investor' ? 'selected' : '' }}>مستثمر</option>
                        </select>
                    </div>

                    <div class="form-group buyer-select" id="buyer-customer-{{ $index }}" style="{{ $sc->type == 'customer' ? '' : 'display:none;' }}">
                        <label>العميل</label>
                        <select name="customers[{{ $index }}][id]" class="searchable-select2" {{ $sc->type != 'customer' ? 'disabled' : '' }}>
                            <option value="">اختر عميل</option>
                            @foreach ($buyers as $buyer)
                                <option value="{{ $buyer->id }}" {{ $sc->customer_id == $buyer->id ? 'selected' : '' }}>
                                    {{ $buyer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group buyer-select" id="buyer-investor-{{ $index }}" style="{{ $sc->type == 'investor' ? '' : 'display:none;' }}">
                        <label>المستثمر</label>
                        <select name="customers[{{ $index }}][id]" class="searchable-select5" {{ $sc->type != 'investor' ? 'disabled' : '' }}>
                            <option value="">اختر مستثمر</option>
                            @foreach ($investors as $investor)
                                <option value="{{ $investor->id }}" {{ $sc->type == 'investor' && $sc->customer_id == $investor->id ? 'selected' : '' }}>
                                    {{ $investor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>النسبة</label>
                        <input type="number" name="customers[{{ $index }}][share]" value="{{ $sc->share_percentage }}">
                    </div>

                    <div class="form-group">
                        <label>المبلغ المدفوع</label>
                        <input type="number" name="customers[{{ $index }}][amount_paid]" value="{{ $sc->amount_paid }}">
                    </div>

                    <div class="form-group">
                        <label>رقم العقد</label>
                        <input type="text" name="customers[{{ $index }}][contract_number]" value="{{ $sc->contract_number }}">
                    </div>

                    <input type="hidden" name="customers[{{ $index }}][id_row]" value="{{ $sc->id }}">
                </div>
            @endforeach
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-save">
                <i class="fas fa-save"></i> حفظ التغييرات
            </button>
        </div>
    </form>
</div>

<script>
    function toggleBuyerType(select) {
        const row = select.closest('.buyer-row');
        const index = row.dataset.index;
        const customerDiv = document.getElementById(`buyer-customer-${index}`);
        const investorDiv = document.getElementById(`buyer-investor-${index}`);
        const customerSelect = customerDiv.querySelector('select');
        const investorSelect = investorDiv.querySelector('select');

        if (select.value === 'investor') {
            customerDiv.style.display = 'none';
            investorDiv.style.display = 'block';
            customerSelect.disabled = true;
            investorSelect.disabled = false;
        } else {
            customerDiv.style.display = 'block';
            investorDiv.style.display = 'none';
            customerSelect.disabled = false;
            investorSelect.disabled = true;
        }
    }
</script>
@endsection