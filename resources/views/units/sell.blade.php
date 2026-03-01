@extends('layout')

@section('title')
<div class="page-title-main">
    <h1>🏠 إدارة الوحدات السكنية</h1>
    <p>تصفية وعرض وإدارة كافة الوحدات العقارية</p>
</div>
@endsection


@section('content')
<div class="container edit-section">
    @if(session('success'))
        <div class="alert alert-success" style="background: var(--success-light); color: var(--success-dark); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i> يرجى تصحيح الأخطاء التالية:
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="unit-summary-mini">
        <p>الوحدة: <strong>{{$unit->type}} - {{$unit->unit_number}}</strong></p>
        <p>المشروع: <strong>{{$unit->project->name}}</strong></p>
    </div>
    <form id="clientForm" method="POST" action="{{route('unit_sell')}}">
        @csrf
        <input type="hidden" name="unit_id" value="{{$unit->id}}">
        
   
        <div class="form-group">
            <label><i class="fas fa-user-tag"></i> نوع المشتري</label>
            <select name="customerType" >
                <option value="customer">مشتري مباشر</option>
                <option value="investor">مستثمر</option>
            </select>

        </div>
        <div class="form-group" id="customer">
            <label><i class="fas fa-user"></i> العميل</label>
                <select name="buyer_id">
                    <option value="">اختر عميل</option>
                    @foreach($buyers as $buyer)
                        <option value="{{$buyer->id}}">{{$buyer->name}}</option>
                    @endforeach
                </select>
        </div>
        <div class="form-group" id="investor">
            <label><i class="fas fa-hand-holding-usd"></i> المستثمر</label>
                <select name="investor_id" class="">
                    <option value="">اختر مستثمر</option>
                    @foreach($investors as $investor)
                        <option value="{{$investor->id}}">{{$investor->name}}</option>
                    @endforeach
                </select>
        </div>

        <div class="form-group" id="marketer">
            <label><i class="fas fa-bullhorn"></i> المسوق</label>
                <select name="marketer_id" class="">
                    <option value="">اختر مسوق</option>
                    @foreach($marketers as $marketer)
                        <option value="{{$marketer->id}}">{{$marketer->name}}</option>
                    @endforeach
                </select>
        </div>


        <div class="form-group">
            <label><i class="fas fa-dollar-sign"></i> قيمة الوحدة </label>
            <input type="number" name="unit_price" value="{{$unit->price}}" id="unit_price" readonly>
        </div>

        
        <div class="form-group">
            <label><i class="fas fa-dollar-sign"></i> قيمة الخصم </label>
            <input type="number" name="discount" id="discount">
        </div>

        
        <div class="form-group">
            <label><i class="fas fa-dollar-sign"></i> السعر النهائي </label>
            <input type="number" name="total_price" id="total_price" readonly>
        </div>

        <script>
  
        </script>
        <div class="form-group">
            <label><i class="fas fa-credit-card"></i> طريقة الدفع</label>
            <select name="payment_method" required>
                <option value="cash">كاش</option>
                <option value="installment">تقسيط</option>
                <option value="mortgage">رهن عقاري</option>
                <option value="transfer">تحويل بنكي</option>
            </select>
        </div>

        <div class="form-group">
            <label><i class="fas fa-money-bill-wave"></i>  المبلغ المدفوع</label>
            <input type="number" name="amount_paid" min="1" required>
         </div>

    

        <div class="form-group">
            <label><i class="fas fa-file-contract"></i> رقم العقد</label>
            <input type="text" name="contract_number" required>   
        </div>

        <div class="form-group">
            <label><i class="fas fa-calendar-alt"></i> تاريخ العقد</label>
            <input type="date" name="sale_date" value="" required>   
        </div>
        <div class="form-group">
            <label><i class="fas fa-percentage"></i> قيمة العمولة</label>
            <input type="number" name="commission" min="0" >    
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-save">
                <i class="fas fa-cart-arrow-down"></i> إتمام البيع
            </button>
        </div>
    </form>
</div>
@endsection