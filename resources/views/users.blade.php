@extends('layout')

@section('title')
    <div class="page-title-main">
        <h1><i class="fas fa-users-shield"></i> طاقم الإدارة والتشغيل</h1>
        <p>عرض شامل لكافة المستخدمين وصلاحياتهم داخل النظام</p>
    </div>
@endsection

@section('content')
<div class="dashboard-wrapper">


@if(session('success'))
    <div class="alert alert-success ">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert-error ">
        <i class="fas fa-check-circle"></i> {{ session('error') }}
    </div>
@endif
    {{-- شريط الإجراءات العلوي --}}
    <div class="action-bar-nested mb-4">
        <button class="add-btn" id="openUserModal">
            <i class="fas fa-user-plus"></i> إضافة مستخدم جديد
        </button>
    </div>

    {{-- إطار الجدول --}}
    <div class="main-content-card table-container-premium">
        <table class="premium-table">
            <thead>
                <tr>
                    <th>المستخدم</th>
                    <th>البريد الإلكتروني</th>
                    <th>الدور الوظيفي</th>
                    <th>تاريخ الإنضمام</th>
                    <th class="text-center">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <div class="user-info-cell">
                            <div class="user-avatar">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div class="user-text">
                                <span class="u-name">{{ $user->name }}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="email-text"><i class="far fa-envelope"></i> {{ $user->email ?? 'غير متوفر' }}</span>
                    </td>
                    <td>
                        <span class="role-badge {{ $user->role == 'admin' ? 'admin' : 'staff' }}">
                            {{ $user->role ?? 'موظف' }}
                        </span>
                    </td>
                    <td>
                        <span class="date-text">{{ $user->created_at->format('Y-m-d') }}</span>
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="{{route('users' , $user->id)}}" class="t-btn btn-view" title="عرض الملف">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <a href="#" class="t-btn btn-edit"
                                data-id="{{ $user->id }}"
                                data-name="{{ $user->name }}"
                                data-email="{{ $user->email }}"
                                data-role="{{ $user->role }}"
                                onclick="openEditModal(this)"  
                                title="تعديل بيانات المستخدم">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="t-btn btn-delete" 
                                        onclick="return confirm('هل أنت متأكد من حذف المستخدم؟')"
                                        title="حذف المستخدم">
                                    <i class="fas fa-user-minus"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div id="addUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 style="margin: 0;"><i class="fas fa-user-plus"></i> إضافة عضو جديد للطاقم</h3>
            <span class="close-modal" id="closeX">&times;</span>
        </div>
        
        <div class="modal-body">
            <form id="clientForm" method="POST" action="{{route('add_user')}}">
                @csrf
                <div class="form-grid" style="display: grid; gap: 20px;">
                    <div class="form-group">
                        <label>الاسم الكامل</label>
                        <input type="text" placeholder="مثال: أحمد محمد" required name="name">
                    </div>
                 
                    <div class="form-group">
                        <label>الدور الوظيفي</label>
                        <select required name="role">
                            <option value="">اختر الدور الوظيفي...</option>
                            <option value="admin">مدير نظام (Admin)</option>
                            <option value="accountant">محاسب (Accountant)</option>
                            <option value="seller">موظف مبيعات (Seller)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>البريد الإلكتروني</label>
                        <input type="email" placeholder="example@mail.com" name="email" required>
                    </div>

                    <div class="form-group">
                        <label>كلمة المرور</label>
                        <input type="password" placeholder="********" name="password" required>
                    </div>
                </div>
                
                <div class="form-actions" style="margin-top: 30px; display: flex; gap: 12px;">
                    <button type="submit" class="btn-accent-custom" style="flex: 2; justify-content: center;">
                        <i class="fas fa-check-circle"></i> حفظ البيانات
                    </button>
                    <button type="button" class="btn-cancel" id="closeBtn" style="flex: 1; background: #eaecf4; border: none; border-radius: 12px; cursor: pointer; color: #5a5c69; font-weight: 600;">
                        إلغاء
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>




<div id="editUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-edit"></i> تعديل بيانات المستخدم</h3>
            <span class="close-modal" onclick="closeEditModal()">&times;</span>
        </div>

        <form method="POST" id="editUserForm">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>الاسم</label>
                <input type="text" name="name" id="editName" required>
            </div>

            <div class="form-group">
                <label>البريد الإلكتروني</label>
                <input type="email" name="email" id="editEmail" required>
            </div>

            <div class="form-group">
                <label>الدور الوظيفي</label>
                <select name="role" id="editRole" required>
                    <option value="admin">Admin</option>
                    <option value="accountant">Accountant</option>
                    <option value="seller">Seller</option>
                </select>
            </div>

            <div class="form-group">
                <label>كلمة المرور (اختياري)</label>
                <input type="password" name="password">
            </div>

            <button type="submit" class="btn-accent-custom">حفظ التعديلات</button>
        </form>
    </div>
</div>

<script>

</script>


@endsection
