@props(['unit', 'buyers', 'investors', 'marketers'])
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