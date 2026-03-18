@extends('layout')

@section('title')
    <h1 class="text-main">📝 تعديل بيانات الشركة</h1>
@endsection

@section('content')
<div class="container edit-section">
    @if(session('success'))
        <div class="alert-unified success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{route('company.update' , $company->id)}}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label><i class="fa fa-building icon" ></i> اسم الشركة</label>
            <input type="text" placeholder="مثال: شقة" value="{{$company->name}}" required name="name"/>
        </div>

      

        <div class="form-actions">
            <button type="submit" class="btn-save">
                <i class="fas fa-save"></i> حفظ التغييرات
            </button>
        </div>
    </form>
</div>
@endsection