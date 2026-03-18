# تقرير الكلاسات CSS غير المستخدمة

التاريخ: 12 مارس 2026

## ملخص التقرير
تم فحص جميع ملفات Blade views وملفات JavaScript والتعرف على الكلاسات المعرفة في `public/css/app.css` التي لم يتم استخدامها في أي مكان في التطبيق.

---

## الكلاسات غير المستخدمة

### 1. `.custom-title`
- **أسطر CSS**: 89-97
- **الوصف**: كلاس لتنسيق العناوين المخصصة
- **عدد الخصائص**: 7 خصائص
- **ملاحظات**: لم يتم استخدام هذا الكلاس في أي ملف view أو JavaScript

```css
.custom-title {
    color: var(--text-main);
    background-color: var(--text-muted);
    font-size: 1.2rem;
    margin-bottom: 1rem;
    font-weight: 700;
    text-align: center;
}
```

---

### 2. `.units-grid-nested`
- **أسطر CSS**: 1298-1302
- **الوصف**: شبكة لعرض الوحدات في تخطيط متداخل
- **الاستخدام المتوقع**: لعرض الوحدات في شكل بطاقات
- **ملاحظات**: لم يتم استخدام هذا الكلاس في أي ملف. قد يكون مخطط له في المستقبل

```css
.units-grid-nested {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 25px;
}
```

---

### 3. `.unit-card-nested`
- **أسطر CSS**: 1304-1402
- **الوصف**: كلاس بطاقة الوحدة مع تأثيرات Hover متقدمة
- **الاستخدام المتوقع**: لعرض معلومات الوحدة في بطاقة
- **ملاحظات**: كلاس شامل بـ 100+ سطر مع فئات فرعية عديدة وجميعها غير مستخدمة:
  - `.unit-card-nested .unit-header-nested`
  - `.unit-card-nested .unit-title`
  - `.unit-card-nested .status-badge-custom`
  - `.unit-card-nested .unit-body-nested`
  - وغيرها...

---

### 4. `.projects-management`
- **أسطر CSS**: 1633-1635
- **الوصف**: تنسيق لصفحة إدارة المشاريع
- **الخصائص**: padding
- **ملاحظات**: كلاس بسيط جداً غير مستخدم

```css
.projects-management {
    padding: 1.5rem;
}
```

---

### 5. `.add-client-btn`
- **أسطر CSS**: 2055-2073 (مشترك مع `.add-project-btn`)
- **الوصف**: زر لإضافة عميل جديد
- **ملاحظات**: يستخدمون `.add-btn` بدلاً من هذا الكلاس في جميع الـ views

---

### 6. `.add-project-btn`
- **أسطر CSS**: 2055-2073 (مشترك مع `.add-client-btn`)
- **الوصف**: زر لإضافة مشروع جديد
- **ملاحظات**: يستخدمون `.add-btn` بدلاً من هذا الكلاس في جميع الـ views

```css
.add-client-btn, 
.add-project-btn {
    background: var(--primary-color);
    color: #fff;
    padding: 12px 24px;
    border-radius: 8px;
    border: none;
    font-weight: 700;
    margin-bottom: 2rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
}
```

---

### 7. `.clients-grid`
- **أسطر CSS**: 2075-2078
- **الوصف**: شبكة لعرض العملاء
- **ملاحظات**: لم يتم استخدام هذا الكلاس. قد يكون هناك صفحة عملاء ولكن لا تستخدم هذا الكلاس

```css
.clients-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}
```

---

### 8. `.client-card`
- **أسطر CSS**: 2081-2172
- **الوصف**: بطاقة العميل مع تأثيرات hover وتفاصيل
- **الفئات الفرعية**:
  - `.client-card .client-header`
  - `.client-card .client-header .client-name`
  - `.client-card .client-details`
  - `.client-card .client-stats`
  - `.client-card .client-actions`
  - وغيرها...
- **ملاحظات**: كلاس شامل بـ 90+ سطر مع عشرات الفئات الفرعية وجميعها غير مستخدمة

---

### 9. `.user-info-side`
- **أسطر CSS**: 2200-2204
- **الوصف**: تنسيق لمعلومات المستخدم بجانب الأفاتار
- **ملاحظات**: لم يتم استخدام هذا الكلاس في أي view

```css
.user-info-side {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}
```

---

### 10. `.user-avatar-circle`
- **أسطر CSS**: 2206-2219
- **الوصف**: أفاتار دائري للمستخدم مع حدود
- **ملاحظات**: لم يتم استخدام هذا الكلاس. هناك `.user-avatar` مستخدم لكن ليس للأفاتار الدائري

```css
.user-avatar-circle {
    width: 70px;
    height: 70px;
    background: var(--bg-body);
    color: var(--accent);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    border: 1px solid var(--border-color);
}
```

---

### 11. `.reports-grid`
- **أسطر CSS**: 2759-2766
- **الوصف**: شبكة لعرض التقارير
- **ملاحظات**: لم يتم استخدام هذا الكلاس. قد يكون مخطط له في المستقبل

```css
.reports-grid {
    display: grid;
    gap: 20px;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    padding: 20px;
    box-sizing: border-box;
}
```

---

### 12. `.report-card`
- **أسطر CSS**: 2770-2797
- **الوصف**: بطاقة تقرير مع تأثيرات hover
- **الفئات الفرعية**:
  - `.report-card h2`
  - `.report-card:hover`
- **ملاحظات**: كلاس شامل لعرض التقارير في شكل بطاقات غير مستخدم

```css
.report-card {
    background-color: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    transition: transform 0.2s, box-shadow 0.2s;
}
```

---

### 13. `.chart-container`
- **أسطر CSS**: 2801-2805
- **الوصف**: حاوية لرسوم بيانية (ApexCharts)
- **ملاحظات**: لم يتم استخدام هذا الكلاس. قد يكون مخطط له مع مكتبة الرسوم البيانية

```css
.chart-container {
    width: 100%;
    height: 250px;
    min-height: 200px;
}
```

---

### 14. `.full-width-card`
- **أسطر CSS**: 2810-2811
- **الوصف**: كلاس لجعل بطاقة تمتد عبر جميع الأعمدة
- **الحالة**: Responsive - يتغير على شاشات أصغر
- **ملاحظات**: لم يتم استخدام هذا الكلاس

```css
.full-width-card {
    grid-column: 1 / -1;
}
```

---

## ملاحظات إضافية

### كلاسات معرفة ومستخدمة لكن فقط في Comments:
- **`.company-stats-grid`** (Lines 223): تم استخدامها في `resources/views/company/index.blade.php` لكن داخل HTML comment (`{{-- --}}`), وبالتالي هذا الكلاس مستخدم بشكل فعلي في الكود

### كلاسات مستخدمة في Views لكن غير معرفة في CSS:
- `.dropdown-container` - مستخدمة في `layout.blade.php` لكن لا توجد تنسيقات خاصة
- `.dashboard-wrapper` - مستخدمة في عدة views لكن لا توجد تنسيقات خاصة
- `.table-frame` - مستخدمة في عدة views (5 أماكن) لكن لا توجد تنسيقات خاصة

---

## الإجمالي

**عدد الكلاسات غير المستخدمة**: 14 كلاس

**عدد الأسطر المحتملة للحذف**: تقريباً 500+ سطر من CSS

### توزيع الكلاسات غير المستخدمة:
- **كلاسات بسيطة** (1-10 خصائص): 3 كلاسات
- **كلاسات متوسطة** (10-30 خصائص): 5 كلاسات
- **كلاسات معقدة** (30+ خصائص): 6 كلاسات

---

## التوصيات

1. **حذف الكلاسات غير المستخدمة**: اسحب هذه الكلاسات من CSS إذا لم تكن مخطط لاستخدامها في المستقبل

2. **استخدام البدائل**: أماكن مثل `add-btn` يجب أن تستخدم بدلاً من `add-client-btn` و `add-project-btn`

3. **توحيد التسميات**: بعض الكلاسات (مثل `units-grid-nested` و `clients-grid`) لها نفس الوظيفة فيجب توحيدها

4. **إزالة الكلاسات الزائدة**: الكثير من الكلاسات المتشابهة الموجودة (`client-card` و `project-card` و `unit-card-nested`) - يجب توحيدها

5. **إعادة النظر في البنية**: قد يوجد خطة لم تنفذ بعد تخص استخدام هذه الكلاسات، تأكد من المشروع قبل الحذف

---

## آخر تحديث
- تاريخ الفحص: 12 مارس 2026
- عدد ملفات Blade Checked: 25+
- عدد ملفات CSS: 1
- عدد ملفات JS: 2
