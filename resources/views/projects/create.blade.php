@extends('layout')

<style>
    
</style>
@section('content')
<div class="container edit-section">
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

 
<form id="projectForm" action="{{ route('add_project') }}" method="POST">
    <h1 style="text-align: center">إضافة مشروع</h1>
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
        <div class="form-actions">
            <button type="submit" class="btn-save">حفظ</button>
        </div>
    </form>
</div>
</div>
@endsection