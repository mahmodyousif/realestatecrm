@extends('layout')

@section('title')
    <div class="page-title-main">
        <h1>🏠 إدارة الوحدات السكنية</h1>
        <p>تصفية وعرض وإدارة كافة الوحدات العقارية</p>
    </div>
@endsection

@section('content')
    <div class="container edit-section">
        @if (session('success'))
            <div class="alert-unified success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert-unified error">
                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i> يرجى تصحيح الأخطاء التالية:
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="unit-summary-mini">
            <p>الوحدة: <strong>{{ $unit->type }} - {{ $unit->unit_number }}</strong></p>
            <p>المشروع: <strong>{{ $unit->project->name }}</strong></p>
            <p>السعر الأساسي: <strong>{{ $unit->price }} ريال</strong></p>
        </div>

        <form id="sellUnitForm" method="POST" action="{{ route('unit_sell') }}">
            @csrf
            <input type="hidden" name="unit_id" value="{{ $unit->id }}">
            
            <script>
                const buyer = @json($buyers);
                const investor = @json($investors);
            </script>
            <!-- الحقول العامة -->
            <div class="form-group">
                <label><i class="fas fa-dollar-sign"></i> قيمة الوحدة الأساسية</label>
                <input type="number" name="unit_price" value="{{ $unit->price }}" id="unit_price" readonly>
            </div>

            <div class="form-group">
                <label><i class="fas fa-dollar-sign"></i> قيمة الخصم الإجمالي</label>
                <input type="number" name="discount" id="discount" min="0" step="0.01">
            </div>

            <div class="form-group">
                <label><i class="fas fa-dollar-sign"></i> السعر النهائي الإجمالي</label>
                <input type="number" name="total_price" id="total_price" readonly>
            </div>



            <div class="form-group">
                <label><i class="fas fa-credit-card"></i> طريقة الدفع العامة</label>
                <select name="payment_method" id="payment_method" required>
                    <option value="cash">كاش</option>
                    <option value="installment">تقسيط</option>
                    <option value="mortgage">رهن عقاري</option>
                    <option value="transfer">تحويل بنكي</option>
                </select>
            </div>

            <div class="form-group">
                <label><i class="fas fa-calendar-alt"></i> تاريخ البيع</label>
                <input type="date" name="sale_date" id="sale_date" required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-bullhorn"></i> المسوق</label>
                <select name="marketer_id">
                    <option value="">اختر مسوق</option>
                    @foreach ($marketers as $marketer)
                        <option value="{{ $marketer->id }}">{{ $marketer->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label><i class="fas fa-percentage"></i> قيمة العمولة</label>
                <input type="number" name="commission" min="0" step="0.01">
            </div>

            <!-- قسم المشترين بحصص -->
            <h3 class="unit-summary-mini">تفاصيل المشترين والحصص</h3>
            <div id="buyers-container">
                <div class="buyer-row" data-index="0">
                    <h4>المشتري 1</h4>
                    <div class="form-group">
                        <label><i class="fas fa-user-tag"></i> نوع المشتري</label>
                        <select name="customers[0][type]" class="customer-type-select" required>
                            <option value="customer">مشتري مباشر</option>
                            <option value="investor">مستثمر</option>
                        </select>
                    </div>

                    <div class="form-group buyer-select" id="buyer-customer-0">
                        <label><i class="fas fa-user"></i> العميل</label>
                        <select name="customers[0][id]" class="searchable-select2">
                            <option value="">اختر عميل</option>
                            @foreach ($buyers as $buyer)
                                <option value="{{ $buyer->id }}">{{ $buyer->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group buyer-select" id="buyer-investor-0" style="display: none;">
                        <label><i class="fas fa-hand-holding-usd"></i> المستثمر</label>
                        <select class="searchable-select5">
                            <option value="">اختر مستثمر</option>
                            @foreach ($investors as $investor)
                                <option value="{{ $investor->id }}">{{ $investor->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-percentage"></i> نسبة البيع (%)</label>
                        <input type="number" name="customers[0][share]" class="percentage-input" min="0"
                            max="100" step="0.01" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-dollar-sign"></i> قيمة البيع</label>
                        <input type="number" class="sale-value-input" readonly>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-money-bill-wave"></i> المبلغ المدفوع</label>
                        <input type="number" name="customers[0][amount_paid]" min="0" step="0.01">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-file-contract"></i> رقم العقد</label>
                        <input type="text" name="customers[0][contract_number]">
                    </div>

                    <button type="button" class="btn-remove-buyer" style="display: none;"
                        onclick="removeBuyer(this)">إزالة هذا المشتري</button>
                </div>
            </div>

            <div class="form-group">
                <button type="button" id="add-buyer-btn" class="btn-add">إضافة مشتري آخر</button>
            </div>

            <div class="form-group">
                <label>إجمالي النسب: <span id="total-percentage">0</span>%</label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save" id="submit-btn">
                    <i class="fas fa-cart-arrow-down"></i> إتمام البيع
                </button>
            </div>
        </form>
    </div>

@endsection
