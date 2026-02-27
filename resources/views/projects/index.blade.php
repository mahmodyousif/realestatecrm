@extends('layout')


@section('title')
    <div class="page-title-main">
        <h1><i class="fas fa-building"></i> إدارة المشاريع</h1>
        <p>عرض وتنظيم جميع المشاريع ومتابعة حالتها وتفاصيلها داخل النظام</p>
    </div>
@endsection

@section('content')
<style>
    
</style>
<div>
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif




    {{-- <div class="action-bar-nested"> --}}
        <div class="export-group projects-btn" >
            
            <a href="{{ route('projects.create') }}" class="add-btn">
                <i class="fas fa-plus-circle"></i> إضافة مشروع جديد
            </a>
        
        
            <div>
                <a href="{{route('projects_export')}}" target="_blank"  class="btn-export success" >
                    <i class="fas fa-file-excel"></i> تصدير المشاريع
                </a>
                <form action="{{ route('projects.import') }}" method="POST" enctype="multipart/form-data" accept=".xlsx,.xls,.csv"  id="importForm2">
                    @csrf
                    <input type="file" name="file" id="importInput2" style="display: none;" onchange="submitImport2()">
                    <button type="button" class="btn-import btn-accent-custom" onclick="document.getElementById('importInput2').click()">
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
                            @foreach($data as $project)
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
{{-- </div> --}}
</div>

{{-- <!-- <form action="{{ route('unit.import') }}" accept=".xlsx,.xls,.csv" method="POST" enctype="multipart/form-data" id="importForm"> --}}
    {{-- @csrf
    <input type="file" name="file" id="importInput" style="display: none;" onchange="submitImport()">
    <button type="button" class="btn-accent-custom" onclick="document.getElementById('importInput').click()">
        <i class="fas fa-cloud-upload-alt"></i> استيراد من Excel
    </button> --
</form> --}}


<div class="projects-grid">

    @if(@isset($data) and !@empty($data))
        @foreach ($data as $project)
        <div class="project-card">
            <div class="project-header">
                <div class="project-name">{{$project->name}}</div>                
                <div class="project-status 
                    @if($project->status === 'completed') status-completed
                    @elseif($project->status === 'active')status-active
                    @else status-planning
                    @endif  ">
                    
                    @if($project->status === 'completed') مكتمل
                    @elseif($project->status === 'active')نشط
                    @else تحت الانشاء
                    @endif    
            </div>
            </div>
            <div class="project-details">
                <div class="detail-row">
                    <span class="detail-label"> الشركة:</span>
                    <span class="detail-value">{{$project->company->name}} </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">عدد الطوابق:</span>
                    <span class="detail-value">{{$project->floors}} طابق</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">إجمالي الوحدات:</span>
                    <span class="detail-value">{{$project->total_units}} وحدة</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">المساحات:</span>
                    <span class="detail-value">{{$project->aria_range}} م²</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">الموقع:</span>
                    <span class="detail-value">{{$project->location}}</span>
                </div>
            </div>
          
            <div class="project-actions">
                <a href="{{route('project.show', $project)}}" class="action-btn btn-view">عرض التفاصيل</a>
                <a href="{{route('edit_project', $project)}}" class="action-btn btn-edit">تعديل</a>
            </div>
        </div>
        @endforeach
    @endif
  
</div>
</div>

<!-- Modal لإضافة مشروع جديد -->
<div id="addProjectModal" class="modal">
<div class="modal-content">
    <button class="modal-close" onclick="closeAddProjectModal()">✕</button>

    <h2 style="margin-bottom: 25px; color: #2c3e50;">إضافة مشروع جديد</h2>
    <form id="projectForm" action="{{ route('add_project') }}" method="POST">

        @csrf

            <div class="form-group">
                <label>الشركة</label>
                <select name="company" id="">
                    <option value="">اختر الشركة</option>
                    @foreach($companies as $company)
                    <option value="{{$company->id}}">{{$company->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>اسم المشروع</label>
                <input type="text" name="name"placeholder="مثال: برج النخيل السكني" required=""/>
            </div>
            <div class="form-group">
                <label>قيمة المشروع</label>
                <input type="number" placeholder="قيمة المشروع" required="" name="price"/>
            </div>
            <div class="form-group">
                <label>عدد الطوابق</label>
                <input type="number" placeholder="15" required="" name="floors"/>
            </div>
            <div class="form-group">
                <label>إجمالي الوحدات</label>
                <input type="number" placeholder="60" required="" name="total_units"/>
            </div>
            <div class="form-group">
                <label>نطاق المساحات (م²)</label>
                <input type="text" placeholder="100 - 200" required="" name="aria_range"/>
            </div>
            <div class="form-group">
                <label>الموقع</label>
                <input type="text" placeholder="حي المروج" required="" name="location"/>
            </div>
            <div class="form-group">
                <label>حالة المشروع</label>
                <select required="" name="status">
                    <option value="">اختر الحالة</option>
                    <option value="active">نشط</option>
                    <option value="completed">مكتمل</option>
                    <option value="planning">تحت الإنشاء</option>
                </select>
            </div>
        
            <div class="form-group">
                <label>ملاحظات</label>
                <textarea rows="3" placeholder="أي ملاحظات إضافية..." name="notes"></textarea>
            </div>
            <div class="form-actions ">
                <button type="submit" class="btn-save">حفظ المشروع</button>
                <button type="button" class="btn-cancel" onclick="closeAddProjectModal()">إلغاء</button>
            </div>
        
    </form>
</div>
</div>
</div>


@endsection