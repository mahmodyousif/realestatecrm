@extends('layout')

@section('title')
<div class="page-title-main">
    <h1>🏠 إدارة الوحدات السكنية</h1>
    <p>تصفية وعرض وإدارة كافة الوحدات العقارية</p>
</div>
@endsection

@section('content')
<div class="dashboard-wrapper units-page">
    @php
        $warnings = session('warnings', []);
    @endphp

    {{-- ✅ رسالة النجاح --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- ✅ رسالة الخطأ العام (لا توجد عمليات) --}}
    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    {{-- ✅ تحذيرات الاستيراد --}}
    @if(!empty($warnings))

        {{-- وحدات مباعة مسبقاً --}}
        @if(count($warnings['duplicate_units'] ?? []) > 0)
            <div class="alert alert-error">
                تم تخطي {{ count($warnings['duplicate_units']) }} وحدة لأنها مباعة أو مسجلة مسبقاً في النظام.
            </div>
        @endif

        {{-- مشاريع غير موجودة --}}
        @if(count($warnings['missing_projects'] ?? []) > 0)
            <div class="alert alert-error">
                تم تخطي {{ count($warnings['missing_projects']) }} وحدة لأن المشروع المرتبط بها غير موجود في النظام.
            </div>
        @endif

        {{-- وحدات غير موجودة --}}
        @if(count($warnings['missing_units'] ?? []) > 0)
            <div class="alert alert-error">
                تم تخطي {{ count($warnings['missing_units']) }} وحدة لأن الوحدة غير موجودة في النظام.
            </div>
        @endif

        {{-- ✅ إصلاح: رسالة price_mismatch واضحة — سعر الإكسل ≠ سعر النظام فقط --}}
        @if(count($warnings['price_mismatch'] ?? []) > 0)
            <div class="alert alert-error">
                تم تخطي {{ count($warnings['price_mismatch']) }} وحدة لأن سعرها في ملف الإكسل يختلف عن السعر المسجل في النظام.
            </div>
        @endif

        {{-- ✅ جديد: إجمالي الدفعات يتجاوز السعر الكلي (منفصل عن price_mismatch) --}}
        @if(count($warnings['overpaid'] ?? []) > 0)
            <div class="alert alert-error">
                تم تخطي {{ count($warnings['overpaid']) }} وحدة لأن إجمالي الدفعات المدخلة يتجاوز السعر الكلي للوحدة.
            </div>
        @endif

        {{-- عقود مكررة --}}
        @if(count($warnings['contract_isExisting'] ?? []) > 0)
            <div class="alert alert-error">
                تم تخطي {{ count($warnings['contract_isExisting']) }} وحدة لأن رقم العقد مستخدم مسبقاً في النظام.
            </div>
        @endif

        {{-- ✅ جديد: عملاء غير موجودين --}}
        @if(count($warnings['missing_customer'] ?? []) > 0)
            <div class="alert alert-error">
                تم تخطي {{ count($warnings['missing_customer']) }} وحدة لأن اسم المشتري غير موجود في قائمة العملاء أو المستثمرين.
            </div>
        @endif

        {{-- ✅ جديد: مجموع الحصص لا يساوي 100% --}}
        @if(count($warnings['share_not_100'] ?? []) > 0)
            <div class="alert alert-error">
                تم تخطي {{ count($warnings['share_not_100']) }} وحدة لأن مجموع حصص الشركاء لا يساوي 100%.
            </div>
        @endif

        {{-- ✅ جديد: نوع مشتري غير صالح --}}
        @if(count($warnings['invalid_customer_type'] ?? []) > 0)
            <div class="alert alert-error">
                تم تخطي {{ count($warnings['invalid_customer_type']) }} وحدة لأن نوع المشتري غير صحيح (المقبول: customer أو investor).
            </div>
        @endif

        {{-- ✅ جديد: طريقة دفع غير صالحة --}}
        @if(count($warnings['invalid_payment_method'] ?? []) > 0)
            <div class="alert alert-error">
                تم تخطي {{ count($warnings['invalid_payment_method']) }} وحدة لأن طريقة الدفع غير معروفة (المقبول: كاش، تقسيط، رهن عقاري، تحويل بنكي).
            </div>
        @endif

    @endif

    {{-- ─── شريط الإجراءات ─── --}}
    <div class="action-bar-nested">
        <div class="export-group">
            <div>
                <a href="{{ route('unit_export') }}" class="btn-export success">
                    <i class="fas fa-file-excel"></i> تصدير الوحدات
                </a>
                <a href="{{ route('reports.export') }}" class="btn-export info">
                    <i class="fas fa-file-invoice-dollar"></i> تصدير المبيعات
                </a>
            </div>

            <div class="import-group">
                <form action="{{ route('unit.import') }}" accept=".xlsx,.xls,.csv" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <input type="file" name="file" id="importInput" style="display: none;" onchange="submitImport()">
                    <button type="button" class="btn-import btn-accent-custom" onclick="document.getElementById('importInput').click()">
                        <i class="fas fa-cloud-upload-alt"></i> استيراد من Excel
                    </button>
                </form>

                <form action="{{ route('unitSell.import') }}" accept=".xlsx,.xls,.csv" method="POST" enctype="multipart/form-data" id="soldForm">
                    @csrf
                    <input type="file" name="file" id="importSoldInput" style="display: none;" onchange="submitSoldImport()">
                    <button type="button" class="btn-import btn-accent-custom soldInputBtn" onclick="document.getElementById('importSoldInput').click()">
                        <i class="fas fa-cloud-upload-alt"></i> استيراد المبيعات
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ─── فلاتر البحث ─── --}}
    <div class="filters-card-nested">
        <form method="GET" action="">
            <div class="filters-grid-nested">
                <div class="filter-group-nested">
                    <label>الشركة</label>
                    <select name="company_id" id="companySelect">
                        <option value="">جميع الشركات</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group-nested">
                    <label>المشروع</label>
                    <select name="project_id" id="projectSelect">
                        <option value="">جميع المشاريع</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group-nested">
                    <label>حالة الوحدة</label>
                    <select name="status">
                        <option value="">جميع الحالات</option>
                        <option value="available"     {{ request('status') == 'available'     ? 'selected' : '' }}>متاحة</option>
                        <option value="sold"          {{ request('status') == 'sold'          ? 'selected' : '' }}>مباعة</option>
                        <option value="reserved"      {{ request('status') == 'reserved'      ? 'selected' : '' }}>محجوزة</option>
                        <option value="partially_paid"{{ request('status') == 'partially_paid'? 'selected' : '' }}>دفع جزئي</option>
                    </select>
                </div>

                <div class="filter-group-nested">
                    <label>رقم الطابق</label>
                    <input type="number" name="floor" value="{{ request('floor') }}" placeholder="أدخل رقم الطابق">
                </div>

                <div class="filter-group-nested">
                    <button class="filter-btn-custom">🔍 تصفية</button>
                </div>
            </div>
        </form>
    </div>

    {{-- ─── جدول الوحدات ─── --}}
    <div class="main-content-card">
        <div class="card-title-area">
            <h2><i class="fa-solid fa-house-chimney icon"></i> جدول الوحدات</h2>
            <p>عرض جميع الوحدات وحالتها المالية والإدارية</p>
        </div>

        <div class="table-frame">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>نوع الوحدة</th>
                        <th>نموذج الوحدة</th>
                        <th>المشروع</th>
                        <th>الشركة</th>
                        <th>قيمة الوحدة</th>
                        <th>الحالة</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                @php $a = 0 @endphp
                @foreach($data as $unit)
                    <tr>
                        <td>{{ ++$a }}</td>
                        <td>{{ $unit->type }}</td>
                        <td>{{ $unit->unit_number }}</td>
                        <td>{{ $unit->project->name }}</td>
                        <td>{{ $unit->project->company->name }}</td>
                        <td>{{ $unit->price }}</td>
                        <td>
                            @if ($unit->status === 'sold')          مباعة
                            @elseif ($unit->status === 'reserved')   محجوزة
                            @elseif ($unit->status === 'partially_paid') دفع جزئي
                            @else جاهزة للبيع
                            @endif
                        </td>
                        <td>
                            <div class="unit-actions-nested">
                                <a href="{{ route('units.show', $unit) }}" class="action-link view" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('manager')
                                    <a href="{{ route('edit_unit', $unit) }}" class="action-link edit" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @if ($unit->status === 'available')
                                    <a href="{{ route('unit.sell', $unit) }}" class="action-link btn-sell-mini" title="بيع">
                                        <i class="fas fa-tag"></i>
                                    </a>
                                @endif
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

</div>
@endsection