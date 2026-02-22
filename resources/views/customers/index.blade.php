@extends('layout')




@section('title')
    <div class="page-title-main">
        <h1><i class="fas fa-users"></i> إدارة العملاء</h1>
        <p>عرض وتنظيم بيانات العملاء ومتابعة تعاملاتهم داخل النظام</p>
    </div>
@endsection
@section('content')



@if(session('success'))
    <div class= "alert-success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif


@if (isset($errors) && count($errors) > 0)
    <div class="alert-error">
        <ul>
            @foreach ($errors as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="filters-card-nested">
    <form method="GET" action="">
        <div class="filters-grid-nested">
            <div class="filter-group-nested">
                <label>الشركة</label>
                <select name="company_id" id="companySelect"  class="searchable-select4">
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
                <select name="project_id"  id="projectSelect" class="searchable-select4">
                    <option value="">جميع المشاريع</option>
                    @foreach($allProjects as $project)
                        <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                        
                    @endforeach
                </select>
            </div>

            
            <div class="filter-group-nested">
                <button class="filter-btn-custom">🔍 تصفية</button>
            </div>
        </div>
    </form>
</div>
<div class="container">
    <div class="client-tabs">
        <button class="tab-btn active" onclick="showTab(event, 'buyers')">👤 المشترون</button>
        <button class="tab-btn" onclick="showTab(event, 'investors')">🏢 المستثمرون</button>
        <button class="tab-btn" onclick="showTab(event, 'marketers')">📢 المسوقون</button>
    </div>

    <div class="export-group">

        <button class="add-btn" onclick="openAddClientModal()">
            <i class="fas fa-plus"></i> إضافة عميل جديد
        </button>
        
        <form action="{{ route('customers.import') }}" accept=".xlsx,.xls,.csv" method="POST" enctype="multipart/form-data" id="importForm3">
            @csrf
            <input type="file" name="file" id="importInput3" style="display: none;" onchange="submitImport3()">
            <button type="button" class="btn-import btn-accent-custom" onclick="document.getElementById('importInput3').click()">
                <i class="fas fa-cloud-upload-alt"></i> استيراد من Excel
            </button>
        </form>
    </div>


    <div id="buyers" class="tab-content active-content">
        <div class="clients-grid">
            @foreach ($data as $customer)
                @if($customer->type == 'buyer')
                <div class="client-card">
                    <div class="client-header">
                        <div class="client-name">{{$customer->name}}</div>
                        <div class="client-type type-buyer">مشتري</div>
                    </div>
                    <div class="client-details">
                        <div class="detail-row">
                            <span class="detail-label">رقم الهوية:</span>
                            <span class="detail-value">{{$customer->id_card}}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">رقم الجوال:</span>
                            <span class="detail-value">{{$customer->phone}}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">البريد الإلكتروني:</span>
                            <span class="detail-value text-truncate" title="{{$customer->email}}">{{$customer->email ?? 'غير متوفر'}}</span>
                        </div>
                    </div>
                    <div class="client-actions">
                        <a href="{{ route('customer.show', $customer->id) }}" class="action-btn btn-view">
                            <i class="fas fa-eye"></i> عرض
                        </a>
                    
                        <a href="{{ route('edit_customer', $customer->id) }}" class="action-btn btn-edit">
                            <i class="fas fa-edit"></i>
                        </a>
                    
                        <a href="tel:{{ $customer->phone }}" class="action-btn btn-contact">
                            <i class="fas fa-phone"></i>
                        </a>
                    
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>

    <div id="investors" class="tab-content" style="display: none;">
        <div class="clients-grid">
            @foreach($data as $customer)
                @if($customer->type === 'investor')
                <div class="client-card">
                    <div class="client-header">
                        <div class="client-name">{{$customer->name}}</div>
                        <div class="client-type type-investor">مستثمر</div>
                    </div>
                    <div class="client-details">
                        <div class="detail-row">
                            <span class="detail-label">رقم السجل:</span>
                            <span class="detail-value">{{$customer->id_card}}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">رقم الجوال:</span>
                            <span class="detail-value">{{$customer->phone}}</span>
                        </div>
                    </div>
                    <div class="client-stats">
                        <div class="stat-row">
                            <span class="stat-label">إجمالي الاستثمار:</span>
                            <span class="stat-value" style="color: var(--primary-color);">جاري الحساب...</span>
                        </div>
                    </div>
                    <div class="client-actions">
                        <a href="{{route('customer.show' , $customer->id)}}" class="action-btn btn-view">ملف المستثمر</a>
                        <a href="{{route('edit_customer' , $customer->id )}}" class="action-btn btn-edit">تعديل</a>
                        <a href="https://wa.me/{{$customer->phone}}" class="action-btn btn-contact" style="background: #25D366;"><i class="fab fa-whatsapp"></i> واتساب</a>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>

    <div id="marketers" class="tab-content" style="display: none;">
        <div class="clients-grid">
            @foreach($data as $customer)
                @if($customer->type === 'marketer')
                <div class="client-card">
                    <div class="client-header">
                        <div class="client-name">{{$customer->name}}</div>
                        <div class="client-type type-marketer">مسوق</div>
                    </div>
                    <div class="client-details">
                        <div class="detail-row">
                            <span class="detail-label">رقم الهوية:</span>
                            <span class="detail-value">{{$customer->id_card}}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">رقم الجوال:</span>
                            <span class="detail-value">{{$customer->phone}}</span>
                        </div>
                    </div>
                    <div class="client-actions">
                        <a href="{{route('marketer.show' , $customer->id)}}" class="action-btn btn-view">الأداء المالي</a>
                        <a href="{{route('edit_customer' , $customer->id )}}" class="action-btn btn-edit">تعديل</a>
                        <a href="tel:{{$customer->phone}}" class="action-btn btn-contact">اتصال</a>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
</div>

<div id="addClientModal" class="modal">
    <div class="modal-content">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0; color: var(--text-main);"><i class="fas fa-user-plus"></i> إضافة عميل جديد</h2>
            <span class="close-modal" onclick="closeAddClientModal()" style="cursor: pointer; font-size: 24px;">&times;</span>
        </div>
        
        <form id="clientForm" method="POST" action="{{route('add_customer')}}">
            @csrf
            <div class="form-grid" >

        
                <div class="form-group" style="grid-column: span 2;">
                    <label>نوع العميل</label>
                    <select required name="type">
                        <option value="">اختر النوع...</option>
                        <option value="buyer">مشتري</option>
                        <option value="investor">مستثمر</option>
                        <option value="marketer">مسوق</option>
                    </select>
                </div>
                
                <div class="form-group" style="grid-column: span 2;">
                    <label>الاسم الكامل</label>
                    <input type="text" placeholder="مثال: أحمد محمد" required name="name">
                </div>
                
                <div class="form-group">
                    <label>الهوية / السجل</label>
                    <input type="text" placeholder="10XXXXXXXX" required name="id_card">
                </div>
                
                <div class="form-group">
                    <label>رقم الجوال</label>
                    <input type="tel" placeholder="05XXXXXXXX" required name="phone">
                </div>
                
                <div class="form-group" >
                    <label>البريد الإلكتروني</label>
                    <input type="email" placeholder="example@mail.com" name="email">
                </div>

                <div class="form-group" >
                    <label>العنوان</label>
                    <input type="text" placeholder="المدينة، الحي، الشارع" name="address">
                </div>

            </div>
            
            <div class="form-actions" style="margin-top: 20px; display: flex; gap: 10px;">
                <button type="submit" class="btn-save" style="flex: 2; background: var(--primary-color); color: white; border: none; padding: 12px; border-radius: 8px; font-weight: bold; cursor: pointer;">حفظ البيانات</button>
                <button type="button" class="btn-cancel" onclick="closeAddClientModal()" style="flex: 1; background: var(--secondary-bg); color: var(--text-main); border: 1px solid var(--border-color); padding: 12px; border-radius: 8px; cursor: pointer;">إلغاء</button>
            </div>
        </form>
    </div>
</div>

<script>
// دالة تبديل الأقسام
function showTab(event, tabId) {
    // إخفاء كل المحتويات
    const contents = document.getElementsByClassName("tab-content");
    for (let i = 0; i < contents.length; i++) {
        contents[i].style.display = "none";
    }

    // إزالة Active من الأزرار
    const buttons = document.getElementsByClassName("tab-btn");
    for (let i = 0; i < buttons.length; i++) {
        buttons[i].classList.remove("active");
    }

    // إظهار القسم المختار وتنشيط الزر
    document.getElementById(tabId).style.display = "block";
    event.currentTarget.classList.add("active");
}

// التحكم في المودال
const modal = document.getElementById("addClientModal");

function openAddClientModal() {
    modal.style.display = "flex";
}

function closeAddClientModal() {
    modal.style.display = "none";
}

// إغلاق المودال عند الضغط خارجه
window.onclick = function(event) {
    if (event.target == modal) {
        closeAddClientModal();
    }
}
</script>

@endsection