@extends('layout')

@section('title')
<div class="page-title-main">
    <h1>🏠 إدارة الوحدات السكنية</h1>
    <p>تصفية وعرض وإدارة كافة الوحدات العقارية</p>
</div>
@endsection

@section('content')
<div class="dashboard-wrapper units-page">
    
    @if(session('success'))
        <div class="alert-success ">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <div class="action-bar-nested">
        <div class="export-group">
            <div>

                <a href="{{route('unit_export')}}" class="btn-export success">
                    <i class="fas fa-file-excel"></i> تصدير الوحدات
                </a>
                <a href="{{route('sales_export')}}" class="btn-export info">
                    <i class="fas fa-file-invoice-dollar"></i> تصدير المبيعات
                </a>
            </div>
            
            <form action="{{ route('unit.import') }}" accept=".xlsx,.xls,.csv" method="POST" enctype="multipart/form-data" id="importForm">
                @csrf
                <input type="file" name="file" id="importInput" style="display: none;" onchange="submitImport()">
                <button type="button" class="btn-import btn-accent-custom" onclick="document.getElementById('importInput').click()">
                    <i class="fas fa-cloud-upload-alt"></i> استيراد من Excel
                </button>
            </form>
        </div>


    </div>

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
                    <select name="project_id"  id="projectSelect">
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
                        <option value="available" {{ request('status')=='available'?'selected':'' }}>متاحة</option>
                        <option value="sold" {{ request('status')=='sold'?'selected':'' }}>مباعة</option>
                        <option value="reserved" {{ request('status')=='reserved'?'selected':'' }}>محجوزة</option>
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

    <div class="units-grid-nested">
        @foreach($data as $unit)
        <div class="unit-card-nested">
            <div class="unit-header-nested">
                <div class="unit-title">
                    <span class="type">{{$unit->type}}</span>
                    <span class="number">#{{$unit->unit_number}}</span>
                    <p class="project-name">{{$unit->project->name}}</p>
                </div>
                <div class="status-badge-custom 
                    @if($unit->status === 'available') available
                    @elseif($unit->status === 'reserved') reserved
                    @else sold @endif">
                    @if ($unit->status === 'available') جاهزة للبيع
                    @elseif ($unit->status === 'reserved') محجوزة
                    @else مباعة @endif
                </div>
            </div>

            <div class="unit-body-nested">
                <div class="spec-item">
                    <i class="fas fa-ruler-combined"></i>
                    <span>المساحة: <strong>{{$unit->area}} م²</strong></span>
                </div>
                <div class="spec-item">
                    <i class="fas fa-layer-group"></i>
                    <span>الطابق: <strong>{{$unit->floor}}</strong></span>
                </div>
                <div class="spec-item">
                    <i class="fas fa-door-open"></i>
                    <span>الغرف: <strong>{{$unit->rooms}}</strong></span>
                </div>

                @if(isset($unit->unitSale->buyer))
                    <div class="buyer-info">
                        <i class="fas fa-user"></i>
                        <span>المشتري: <strong>{{$unit->unitSale->buyer->name ?? '-' }}</strong></span>
                    </div>
                @endif
                @if(isset($unit->unitSale->investor))
                    <div class="investor-info">
                        <i class="fas fa-user"></i>
                        <span>المستثمر: <strong>{{$unit->unitSale->investor->name ?? '-' }}</strong></span>
                    </div>
                @endif
            </div>

            <div class="unit-footer-nested">
                <div class="price-box">
                    <span class="label">السعر:</span>
                    <span class="value">{{number_format($unit->price)}} <small>ريال</small></span>
                </div>
                <div class="unit-actions-nested">
                    <a href="{{route('units.show' ,$unit)}}" class="action-link view" title="عرض"><i class="fas fa-eye"></i></a>
                    @can('manager')
                        <a href="{{route('edit_unit', $unit)}}" class="action-link edit" title="تعديل"><i class="fas fa-edit"></i></a>
                    @endcan
                    @if ($unit->status === 'available')
                        <button class="btn-sell-mini"
                            data-unit-id="{{ $unit->id }}"
                            data-unit-name="{{ $unit->type }} {{ $unit->unit_number }}"
                            data-project-name="{{ $unit->project->name }}"
                            data-price="{{ $unit->price }}"
                            onclick="openSellUnitModal(this)">
                            بيع
                        </button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>


<div id="sellUnitModal" class="modal-nested" style="display:none;">
    <div class="modal-content-card">
        <div class="modal-header">
            <h2>اتمام عملية البيع</h2>
            <button class="close-btn" onclick="closeSellUnitModal()">✕</button>
        </div>
        <form id="sellUnitForm" method="POST" action="{{route('unit_sell')}}">
            @csrf
            <input type="hidden" name="unit_id" id="sale_unit_id">
            
            <div class="unit-summary-mini">
                <p>الوحدة: <strong id="sale_unit_name"></strong></p>
                <p>المشروع: <strong id="sale_project_name"></strong></p>
            </div>

            <div class="form-grid-2">
                <div class="form-group-nested">
                    <label>المشتري</label>
                    <select name="buyer_id" class="searchable-select2" >
                        <option value="">اختر مشتري</option>

                        @foreach($buyers as $buyer)
                            <option value="{{$buyer->id}}">{{$buyer->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group-nested">
                    <label>المستثمر</label>
                    <select name="investor_id" class="searchable-select5" >
                        <option value="">الشركة مباشرة</option>

                        @foreach($investors as $investor)
                            <option value="{{$investor->id}}">{{$investor->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group-nested">
                    <label>المسوق</label>
                    <select name="marketer_id" class="searchable-select3" >
                        <option value="">الشركة مباشرة</option>
                        @foreach($marketers as $marketer)
                            <option value="{{ $marketer->id }}">{{ $marketer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group-nested">
                    <label>السعر الكلي</label>
                    <input type="number" name="total_price" id="sale_total_price" readonly>
                </div>
                <div class="form-group-nested">
                    <label>طريقة الدفع</label>
                    <select name="payment_method" required>
                        <option value="cash">كاش</option>
                        <option value="installment">تقسيط</option>
                        <option value="mortgage">رهن عقاري</option>
                        <option value="transfer">تحويل بنكي</option>
                    </select>
                </div>
                <div class="form-group-nested">
                    <label>المبلغ المدفوع</label>
                    <input type="number" name="amount_paid" min="1" required>
                </div>
                <div class="form-group-nested">
                    <label>تاريخ البيع</label>
                    <input type="date" name="sale_date" required>
                </div>
                <div class="form-group-nested">
                    <label>رقم العقد</label>
                    <input type="text" name="contract_number" required>
                </div>
                <div class="form-group-nested">
                    <label>قيمة العمولة</label>
                    <input type="number" name="commission" min="0">
                </div>
            </div>
          
            <div class="modal-actions">
                <button type="submit" class="save-btn sell">إتمام البيع</button>
                <button type="button" class="cancel-btn" onclick="closeSellUnitModal()">إلغاء</button>
            </div>
        </form>
    </div>
</div>

@endsection
