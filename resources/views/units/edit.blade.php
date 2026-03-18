@extends('layout')

@section('title')
    <h1 class="text-main">📝 تعديل بيانات الوحدة</h1>
@endsection

@section('content')
<div class="container edit-section">
    @if(session('success'))
        <div class="alert-unified success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <form id="clientForm" method="POST" action="{{route('update_unit' , $unit->id)}}">
    ئ    @csrf
        @method('PUT')

        <div class="form-group full-width">
            <label><i class="fas fa-building"></i> المشروع</label>
            <select name="project_id" class="searchable-select5" required>
                <option value="">اختر المشروع</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ $unit->project_id == $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label><i class="fas fa-home"></i> نوع الوحدة</label>
            <input type="text" placeholder="مثال: شقة" value="{{$unit->type}}" required name="type"/>
        </div>

        <div class="form-group">
            <label><i class="fas fa-hashtag"></i> نموذج الوحدة</label>
            <input type="text" placeholder="مثال: 5A" value="{{$unit->unit_number}}" required name="unit_number"/>
        </div>

        <div class="form-group">
            <label><i class="fas fa-ruler-combined"></i> المساحة (م²)</label>
            <input type="number" placeholder="مثال: 150" value="{{$unit->area}}" required name="area"/>
        </div>

        <div class="form-group">
            <label><i class="fas fa-layer-group"></i> الطابق</label>
            <input type="number" placeholder="مثال: 2" value="{{$unit->floor}}" required name="floor"/>
        </div>

        <div class="form-group">
            <label><i class="fas fa-bed"></i> عدد الغرف</label>
            <input type="number" placeholder="مثال: 3" value="{{$unit->rooms}}" required name="rooms"/>
        </div>

        <div class="form-group">
            <label><i class="fas fa-money-bill-wave"></i> السعر</label>
            <input type="number" placeholder="مثال: 500000" value="{{$unit->price}}" required name="price"/>
        </div>

        {{-- الحالة مخفية حسب الكود الأصلي --}}
        <input type="hidden" name="status" value="{{$unit->status}}">

        <div class="form-actions">
            <button type="submit" class="btn-save">
                <i class="fas fa-save"></i> حفظ التغييرات
            </button>
        </div>
    </form>
</div>
@endsection