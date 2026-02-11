@extends('layout')
@section('title')

<h1>تعديل المستخدم</h1>
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

<form id="clientForm" method="POST" action="{{route('update_customer', $customer->id)}}">
    @csrf
    @method('PUT')

    <div class="form-group">
        <label>نوع العميل</label>
        <select required="" name="type">
            <option value="">اختر النوع</option>
            <option value="buyer" {{$customer->type == 'buyer' ? 'selected' : ''}}>مشتري</option>
            <option value="investor" {{$customer->type == 'investor' ? 'selected' : ''}}>مستثمر</option>
            <option value="marketer" {{$customer->type == 'marketer' ? 'selected' : ''}}>مسوق</option>
        </select>
   
    </div>
    <div class="form-group">
        <label>الاسم الكامل</label>
        <input type="text" placeholder="مثال: أحمد محمد" value="{{$customer->name}}" required="" name="name"/>
    </div>
    <div class="form-group">
        <label>رقم الهوية / السجل التجاري</label>
        <input type="text" placeholder="رقم الهوية أو السجل التجاري"value="{{$customer->id_card}}" required="" name="id_card"/>
    </div>
    <div class="form-group">
        <label>رقم الجوال</label>
        <input type="tel" placeholder="05xxxxxxxx" value="{{$customer->phone}}" required="" name="phone"/>
    </div>
    <div class="form-group">
        <label>البريد الإلكتروني</label>
        <input type="email" placeholder="email@example.com" value="{{$customer->email}}" name="email"/>
    </div>
    <div class="form-group">
        <label>العنوان</label>
        <input type="text" placeholder="العنوان الكامل" value="{{$customer->address}}" name="address"/>
    </div>
    <div class="form-group">
        <label>ملاحظات</label>
        <textarea rows="3" placeholder="أي ملاحظات إضافية..." name="notes"></textarea>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn-save">حفظ </button>
    </div>
</form>
</div>
</div>
@endsection