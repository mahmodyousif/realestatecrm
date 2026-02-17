@extends('layout')
@section('title')

<h1>تعديل المشروع</h1>
@endsection
<style>
    
</style>
@section('content')
<div class="container edit-section">
    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

    <form id="clientForm" method="POST" action="{{route('update_project' , $project['id'])}}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>اسم المشروع</label>
            <input type="text" placeholder="مثال: شقة" value="{{$project->name}}" required="" name="name"/>
        </div>

        <div class="form-group">
            <label>قيمة المشروع</label>
            <input type="text"value="{{$project->price}}" required="" name="price"/>
        </div>
      
        <div class="form-group">
            <label>عدد الطوابق</label>
            <input type="text" placeholder="مثال: 5A" value="{{$project->floors}}"required="" name="floors"/>
        </div>
        <div class="form-group">
            <label>عدد الوحدات</label>
            <input type="number" placeholder="مثال: 500م" value="{{$project->total_units}}"required="" name="total_units"/>
        </div>

        <div class="form-group">
            <label>نطاق المساحات</label>
            <input type="number" placeholder="الطابق"value="{{$project->aria_range}}" required="" name="aria_range"/>
        </div>
        <div class="form-group">
            <label>الموقع</label>
            <input type="text" placeholder="مثال: 5" value="{{$project->location}}"required="" name="location"/>
        </div>

        <div class="form-group">
            <label>الحالة</label>
            <input type="text" placeholder="مثال: 5000" value="{{$project->status}}" required="" name="status"/>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-save">حفظ</button>
        </div>
    </form>
</div>
</div>
@endsection