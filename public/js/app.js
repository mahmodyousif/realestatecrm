// ============================
// مودالات المشاريع، الشركات، الوحدات، العملاء، المستخدمين
// ============================

// تحديث قائمة المشاريع بناءً على الشركة المختارة
document.getElementById('companySelect')?.addEventListener('change', function() {
    let companyId = this.value;
    let projectSelect = document.getElementById('projectSelect');

    if (!projectSelect) return;

    fetch(`/projects-by-company/${companyId}`)
        .then(response => response.json())
        .then(data => {
            projectSelect.innerHTML = '<option value="">جميع المشاريع</option>';
            data.forEach(project => {
                let option = document.createElement('option');
                option.value = project.id;
                option.textContent = project.name;
                projectSelect.appendChild(option);
            });
        })
        .catch(err => console.error('Error fetching projects:', err));
});

// --- مودال إضافة مشروع ---
function openAddProjectModal() {
    const modal = document.getElementById('addProjectModal');
    if (modal) modal.style.display = 'flex';
}

function closeAddProjectModal() {
    const modal = document.getElementById('addProjectModal');
    if (modal) modal.style.display = 'none';
}

// --- مودال إضافة شركة ---
function openAddCompany() {
    const modal = document.getElementById('addCompanyModal');
    if (modal) modal.style.display = 'flex';
}

function closeAddCompanyModal() {
    const modal = document.getElementById('addCompanyModal');
    if (modal) modal.style.display = 'none';
}

// --- مودال إضافة وحدة ---
function openAddUnitModal() {
    const modal = document.getElementById('addUnitModal');
    if (modal) modal.style.display = 'block';
}

function closeAddUnitModal() {
    const modal = document.getElementById('addUnitModal');
    if (modal) modal.style.display = 'none';
}

// --- مودال بيع الوحدة ---
function openSellUnitModal(button) {
    const unitId = button.dataset.unitId;
    const unitName = button.dataset.unitName;
    const projectName = button.dataset.projectName;
    const price = button.dataset.price;

    const modal = document.getElementById('sellUnitModal');
    if (!modal) return;

    document.getElementById('sale_unit_id').value = unitId;
    document.getElementById('sale_unit_name').innerText = unitName;
    document.getElementById('sale_project_name').innerText = projectName;
    document.getElementById('sale_total_price').value = price;

    const today = new Date().toISOString().split('T')[0];
    const saleDateInput = document.querySelector('input[name="sale_date"]');
    if (saleDateInput) saleDateInput.value = today;

    modal.style.display = 'flex';
}

function closeSellUnitModal() {
    const modal = document.getElementById('sellUnitModal');
    if (modal) modal.style.display = 'none';
}

// --- مودال إضافة عميل ---
function openAddClientModal() {
    const modal = document.getElementById('addClientModal');
    if (modal) modal.style.display = 'flex';
}

function closeAddClientModal() {
    const modal = document.getElementById('addClientModal');
    if (modal) modal.style.display = 'none';
}

// --- مودال إضافة مستخدم جديد ---
function openAddUserModal() {
    const modal = document.getElementById('addUserModal');
    if (modal) modal.style.display = 'flex';
}

function closeAddUserModal() {
    const modal = document.getElementById('addUserModal');
    if (modal) modal.style.display = 'none';
}

// --- مودال الدفعات المالية ---
function togglePaymentModal() {
    const modal = document.getElementById('paymentModal');
    if (modal) modal.style.display = (modal.style.display === 'flex') ? 'none' : 'flex';
}

// --- مودال تعديل المستخدم ---
function openEditModal(el) {
    const modal = document.getElementById('editUserModal');
    if (!modal) return;

    modal.style.display = 'flex';

    document.getElementById('editName').value = el.dataset.name;
    document.getElementById('editEmail').value = el.dataset.email;
    document.getElementById('editRole').value = el.dataset.role;

    const form = document.getElementById('editUserForm');
    if (form) form.action = `/users/${el.dataset.id}`;
}

function closeEditModal() {
    const modal = document.getElementById('editUserModal');
    if (modal) modal.style.display = 'none';
}

// ============================
// استيراد Excel
// ============================
function submitImport() {
    const fileInput = document.getElementById('importInput');
    const btn = document.querySelector('.btn-accent-custom');

    if (fileInput && fileInput.files.length > 0 && btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الرفع...';
        document.getElementById('importForm').submit();
    }
}

function submitSoldImport() {
    const fileInput = document.getElementById('importSoldInput');
    const btn = document.querySelector('.soldInputBtn');

    if (fileInput && fileInput.files.length > 0 && btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الرفع...';
        document.getElementById('soldForm').submit();
    }
}

function submitImport2() {
    const fileInput = document.getElementById('importInput2');
    const btn = document.querySelector('.btn-accent-custom');

    if (fileInput && fileInput.files.length > 0 && btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الرفع...';
        document.getElementById('importForm2').submit();
    }
}

function submitImport3() {
    const fileInput = document.getElementById('importInput3');
    const btn = document.querySelector('.btn-accent-custom');

    if (fileInput && fileInput.files.length > 0 && btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الرفع...';
        document.getElementById('importForm3').submit();
    }
}

// ============================
// التعامل مع النوافذ عند الضغط خارجها
// ============================
window.addEventListener('click', function(event) {
    const modals = [
        { id: 'addProjectModal', close: closeAddProjectModal },
        { id: 'sellUnitModal', close: closeSellUnitModal },
        { id: 'addCompanyModal', close: closeAddCompanyModal },
        { id: 'addUnitModal', close: closeAddUnitModal },
        { id: 'addClientModal', close: closeAddClientModal },
        { id: 'addUserModal', close: closeAddUserModal },
        { id: 'editUserModal', close: closeEditModal },
        { id: 'paymentModal', close: togglePaymentModal }
    ];

    modals.forEach(({ id, close }) => {
        const modal = document.getElementById(id);
        if (event.target === modal) {
            close();
        }
    });
});

// ============================
// Event Listeners للأزرار
// ============================
document.addEventListener('DOMContentLoaded', function() {
    // فتح مودال المستخدم
    const openUserBtn = document.getElementById('openUserModal');
    if (openUserBtn) openUserBtn.addEventListener('click', openAddUserModal);

    // بيع الوحدة
    document.querySelectorAll('.btn-sell').forEach(btn => {
        btn.addEventListener('click', function() { openSellUnitModal(this); });
    });

    // إغلاق المودالات عند الإرسال
    const sellForm = document.getElementById('sellUnitForm');
    if (sellForm) sellForm.addEventListener('submit', closeSellUnitModal);

    const projectForm = document.getElementById('projectForm');
    if (projectForm) projectForm.addEventListener('submit', closeAddProjectModal);
});

// ============================
// Select2 (بحث داخل الـ select)
// ============================
document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.searchable-select').select2({
            width: '100%',
            dir: 'rtl',
            placeholder: "اختر مشروع",
            dropdownParent: $('#addUnitModal')
        }).on('select2:open', function () {
            document.querySelector('.select2-search__field').placeholder = 'ابحث عن مشروع...';
        });

        $('.searchable-select2').select2({
            width: '100%',
            dir: 'rtl',
            placeholder: "اختر مشتري",
            dropdownParent: $('#sellUnitModal')
        }).on('select2:open', function () {
            document.querySelector('.select2-search__field').placeholder = 'ابحث عن مشتري...';
        });

        $('.searchable-select3').select2({
            width: '100%',
            dir: 'rtl',
            placeholder: "اختر مسوق",
            dropdownParent: $('#sellUnitModal')
        }).on('select2:open', function () {
            document.querySelector('.select2-search__field').placeholder = 'ابحث عن مسوق...';
        });

        $('.searchable-select4').select2({
            width: '100%',
            dir: 'rtl',
            allowClear: false
        });

        $('.searchable-select5').select2({
            width: '100%',
            dir: 'rtl',
            placeholder: "اختر مستثمر",
            dropdownParent: $('#sellUnitModal')
        }).on('select2:open', function () {
            document.querySelector('.select2-search__field').placeholder = 'ابحث عن مستثمر...';
        });
    }
});

// ============================
// Dropdown
// ============================
document.addEventListener('DOMContentLoaded', function() {
    const dropdownBtn = document.getElementById('dropdown-btn');
    const dropdownContent = document.getElementById('dropdown-content');

    if (dropdownBtn && dropdownContent) {
        dropdownBtn.addEventListener('mouseenter', function() {
            dropdownContent.style.display = 'block';
        });

        dropdownBtn.addEventListener('mouseleave', function() {
            setTimeout(() => {
                if (!dropdownContent.matches(':hover') && !dropdownBtn.matches(':hover')) {
                    dropdownContent.style.display = 'none';
                }
            }, 100);
        });
    }
});

// ============================
// إدارة القائمة الجانبية على الهواتف الذكية
// ============================
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const toggle = document.querySelector('.menu-toggle');

    if (!sidebar || !toggle) return;

    // تبديل القائمة عند النقر على الزر
    toggle.addEventListener('click', (e) => {
        if (window.innerWidth <= 768) {
            e.stopPropagation();
            sidebar.classList.toggle('mobile-open');
        }
    });

    // إغلاق القائمة عند النقر خارجها
    document.addEventListener('click', () => {
        sidebar.classList.remove('mobile-open');
    });

    // إضافة حدث النقر على الشعار لتبديل القائمة
    const logo = sidebar.querySelector('.logo');
    if (logo && window.innerWidth <= 768) {
        logo.style.cursor = 'pointer';
        logo.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebar.classList.toggle('mobile-open');
        });
    }

    // إغلاق القائمة عند النقر على أي رابط
    const navLinks = sidebar.querySelectorAll('.nav-links a, .nav-links .dropdown-btn');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768 && !this.classList.contains('dropdown-btn')) {
                setTimeout(() => {
                    sidebar.classList.remove('mobile-open');
                }, 200);
            }
        });
    });

    // معالجة تغيير حجم النافذة
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('mobile-open');
            }
        }, 250);
    });
});

// ============================
// تبديل الوضع الليلي
// ============================
function toggleTheme() {
    const html = document.documentElement;
    const icon = document.getElementById('theme-icon');
    if (!icon) return;

    if (html.getAttribute('data-theme') === 'dark') {
        html.removeAttribute('data-theme');
        icon.className = 'fa-solid fa-moon';
        localStorage.setItem('theme', 'light');
    } else {
        html.setAttribute('data-theme', 'dark');
        icon.className = 'fa-solid fa-sun';
        localStorage.setItem('theme', 'dark');
    }
}

// تطبيق الثيم المحفوظ
if (localStorage.getItem('theme') === 'dark') {
    document.documentElement.setAttribute('data-theme', 'dark');
    const icon = document.getElementById('theme-icon');
    if (icon) icon.className = 'fa-solid fa-sun';
}

// ============================
// فتح القائمة المنسدلة للشركات
// ============================
function toggleDropdown(event) {
    event.preventDefault();
    const btn = event.currentTarget;
    const menu = document.getElementById('company-menu');
    if (menu) {
        menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
        btn.classList.toggle('active');
    }
}

// ============================
// تحسين الـ Dropdown على الهواتف
// ============================
function toggleDropdownMobile() {
    const dropdownBtn = event.currentTarget;
    const dropdownContent = dropdownBtn.nextElementSibling;

    if (dropdownContent && dropdownContent.classList.contains('dropdown-content')) {
        if (dropdownContent.style.display === 'none') {
            dropdownContent.style.display = 'block';
            dropdownBtn.classList.add('active');
        } else {
            dropdownContent.style.display = 'none';
            dropdownBtn.classList.remove('active');
        }
    }
}

// ============================
// تحديد نوع العميل في صفحة بيع الوحدة
// ============================
document.addEventListener('DOMContentLoaded', function() {
    const customerTypeSelect = document.querySelector('select[name="customerType"]');
    if (!customerTypeSelect) return;

    customerTypeSelect.addEventListener('change', function() {
        const investorSelect = document.querySelector('#investor');
        const marketerSelect = document.querySelector('#customer');

        if (this.value === 'investor') {
            if (investorSelect) investorSelect.style.display = 'flex';
            if (marketerSelect) marketerSelect.style.display = 'none';
        } else {
            if (investorSelect) investorSelect.style.display = 'none';
            if (marketerSelect) marketerSelect.style.display = 'flex';
        }
    });

    // Initialize the form
    const event = new Event('change');
    customerTypeSelect.dispatchEvent(event);
});

// ============================
// تحديث السعر الإجمالي عند الخصم
// ============================
document.addEventListener('DOMContentLoaded', function() {
    const unitPriceInput = document.getElementById('unit_price');
    const discountInput = document.getElementById('discount');
    const totalPriceInput = document.getElementById('total_price');

    if (!unitPriceInput || !discountInput || !totalPriceInput) return;

    function calculateFinalPrice() {
        const unitPrice = parseFloat(unitPriceInput.value) || 0;
        const discount = parseFloat(discountInput.value) || 0;
        const totalPrice = Math.max(unitPrice - discount, 0);
        totalPriceInput.value = totalPrice.toFixed(2);
    }

    unitPriceInput.addEventListener('input', calculateFinalPrice);
    discountInput.addEventListener('input', calculateFinalPrice);
    window.addEventListener('load', calculateFinalPrice);
});

// ============================
// الرسوم البيانية - ApexCharts
// ============================
document.addEventListener('DOMContentLoaded', function() {
    // الرسم البياني 1: نمو المبيعات هذا الشهر
    if (window.chartData && window.chartData.salesLabels && window.chartData.salesLabels.length > 0) {
        const salesChart = document.querySelector('#monthlySalesChart');
        if (salesChart) {
            const options = {
                chart: {
                    type: 'area',
                    height: 350,
                    toolbar: { show: true }
                },
                series: [{
                    name: 'المبيعات (ريال)',
                    data: window.chartData.salesData
                }],
                stroke: { curve: 'smooth' },
                xaxis: {
                    categories: window.chartData.salesLabels,
                    title: { text: 'اليوم' }
                },
                yaxis: {
                    title: { text: 'المبيعات (ريال)' }
                },
                tooltip: {
                    y: { formatter: function (val) { return new Intl.NumberFormat().format(val) + ' ريال'; } }
                },
                dataLabels: { enabled: false },
                fill: { opacity: 0.3 }
            };

            const chart = new ApexCharts(salesChart, options);
            chart.render();
        }
    }

    // الرسم البياني 2: إحصائيات الوحدات حسب المشاريع
    if (window.chartData && window.chartData.projectLabels && window.chartData.projectLabels.length > 0) {
        const projectsChart = document.querySelector('#projectsUnitsChart');
        if (projectsChart) {
            const options = {
                chart: { type: 'bar', height: 420, toolbar: { show: true } },
                plotOptions: { bar: { horizontal: false, columnWidth: '100%' } },
                dataLabels: { enabled: false },
                stroke: { show: true, width: 1, colors: ['#fff'] },
                series: [
                    { name: 'محجوزة', data: window.chartData.projectReserved },
                    { name: 'مباعة', data: window.chartData.projectSold },
                    { name: 'متاحة', data: window.chartData.projectAvailable }
                ],
                colors: ['#f6c23e', '#4e73df', '#1cc88a'],
                xaxis: { categories: window.chartData.projectLabels, title: { text: 'المشروع' } },
                yaxis: { title: { text: 'عدد الوحدات' } },
                legend: { position: 'top' },
                tooltip: { y: { formatter: function (val) { return parseInt(val); } } }
            };

            const chart = new ApexCharts(projectsChart, options);
            chart.render();
        }
    }
});

// ============================
// إدارة بيع الوحدات - إضافة وإزالة المشترين
// ============================
let buyerIndex = 1;

document.addEventListener('DOMContentLoaded', function() {
    const addBuyerBtn = document.getElementById('add-buyer-btn');
    if (addBuyerBtn) {
        addBuyerBtn.addEventListener('click', addBuyer);
    }

    // تهيئة أحداث الخصم والسعر الأساسي
    const discountInput = document.getElementById('discount');
    const unitPriceInput = document.getElementById('unit_price');

    if (discountInput) {
        discountInput.addEventListener('input', calculateTotalPrice);
    }
    if (unitPriceInput) {
        unitPriceInput.addEventListener('input', calculateTotalPrice);
    }

    // تهيئة Select2 للمشتري الأول
    const firstBuyerRow = document.querySelector('.buyer-row');
    if (firstBuyerRow) {
        initializeSelect2(firstBuyerRow);
    }

    // معالجة تغيير نوع المشتري
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('customer-type-select')) {
            handleCustomerTypeChange(e.target);
        }
    });

    // معالجة تغيير النسب المئوية
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('percentage-input')) {
            calculateTotalPercentage();
            calculateSaleValues();
        }
    });

    // التحقق من النسب عند الإرسال
    const sellForm = document.getElementById('sellUnitForm');
    if (sellForm) {
        sellForm.addEventListener('submit', function(e) {
            const totalPercentage = calculateTotalPercentage();
            if (totalPercentage !== 100) {
                e.preventDefault();
                alert('يجب أن تكون إجمالي النسب 100%');
                return;
            }
        });
    }

    // تهيئة التاريخ والحسابات
    const saleDateInput = document.getElementById('sale_date');
    if (saleDateInput) {
        const today = new Date().toISOString().split('T')[0];
        saleDateInput.value = today;
    }

    calculateTotalPrice();
    updateRemoveButtons();
});

function addBuyer() {
    const container = document.getElementById('buyers-container');
 
    if (!container) return;

    const newRow = document.createElement('div');
    newRow.className = 'buyer-row';
    newRow.setAttribute('data-index', buyerIndex);

    // إنشاء خيارات العملاء
    let buyerOptions = '<option value="">اختر عميل</option>';
    if (typeof buyer !== 'undefined' && Array.isArray(buyer)) {
        buyer.forEach(b => {
            buyerOptions += `<option value="${b.id}">${b.name}</option>`;
        });
    }

    // إنشاء خيارات المستثمرين
    let investorOptions = '<option value="">اختر مستثمر</option>';
    if (typeof investor !== 'undefined' && Array.isArray(investor)) {
        investor.forEach(i => {
            investorOptions += `<option value="${i.id}">${i.name}</option>`;
        });
    }

    newRow.innerHTML = `
        <h4>المشتري ${buyerIndex + 1}</h4>
        <div class="form-group">
            <label><i class="fas fa-user-tag"></i> نوع المشتري</label>
            <select name="customers[${buyerIndex}][type]" class="customer-type-select" required>
                <option value="customer">مشتري مباشر</option>
                <option value="investor">مستثمر</option>
            </select>
        </div>

        <div class="form-group buyer-select" id="buyer-customer-${buyerIndex}">
            <label><i class="fas fa-user"></i> العميل</label>
            <select name="customers[${buyerIndex}][id]" class="searchable-select2">
                ${buyerOptions}
            </select>
        </div>

        <div class="form-group buyer-select" id="buyer-investor-${buyerIndex}" style="display: none;">
            <label><i class="fas fa-hand-holding-usd"></i> المستثمر</label>
            <select class="searchable-select5">
                ${investorOptions}
            </select>
        </div>

        <div class="form-group">
            <label><i class="fas fa-percentage"></i> نسبة البيع (%)</label>
            <input type="number" name="customers[${buyerIndex}][share]" class="percentage-input" min="0" max="100" step="0.01" required>
        </div>

        <div class="form-group">
            <label><i class="fas fa-dollar-sign"></i> قيمة البيع</label>
            <input type="number" class="sale-value-input" readonly>
        </div>

        <div class="form-group">
            <label><i class="fas fa-money-bill-wave"></i> المبلغ المدفوع</label>
            <input type="number" name="customers[${buyerIndex}][amount_paid]" min="0" step="0.01">
        </div>

        <div class="form-group">
            <label><i class="fas fa-file-contract"></i> رقم العقد</label>
            <input type="text" name="customers[${buyerIndex}][contract_number]">
        </div>

        <button type="button" class="btn-remove-buyer" onclick="removeBuyer(this)">إزالة هذا المشتري</button>
    `;

    container.appendChild(newRow);
    buyerIndex++;

    updateRemoveButtons();
    initializeSelect2(newRow);

    // تشغيل حدث change على select نوع المشتري لتحديث العرض
    const typeSelect = newRow.querySelector('.customer-type-select');
    if (typeSelect) {
        const event = new Event('change');
        typeSelect.dispatchEvent(event);
    }
}

function removeBuyer(button) {
    const row = button.closest('.buyer-row');
    if (row) {
        row.remove();
        updateRemoveButtons();
        calculateTotalPercentage();
        calculateSaleValues();
        buyerIndex--;
    }
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.buyer-row');
    rows.forEach((row) => {
        const removeBtn = row.querySelector('.btn-remove-buyer');
        if (removeBtn) {
            removeBtn.style.display = rows.length > 1 ? 'block' : 'none';
        }
    });
}

function calculateTotalPercentage() {
    let total = 0;
    document.querySelectorAll('.percentage-input').forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    const totalPercentageEl = document.getElementById('total-percentage');
    if (totalPercentageEl) {
        totalPercentageEl.textContent = total.toFixed(2);
    }
    return total;
}

function calculateSaleValues() {
    const totalPrice = parseFloat(document.getElementById('total_price')?.value) || 0;
    document.querySelectorAll('.buyer-row').forEach(row => {
        const percentage = parseFloat(row.querySelector('.percentage-input')?.value) || 0;
        const saleValue = (totalPrice * percentage) / 100;
        const saleValueInput = row.querySelector('.sale-value-input');
        if (saleValueInput) {
            saleValueInput.value = saleValue.toFixed(2);
        }
    });
}

function calculateTotalPrice() {
    const unitPrice = parseFloat(document.getElementById('unit_price')?.value) || 0;
    const discount = parseFloat(document.getElementById('discount')?.value) || 0;
    const totalPrice = Math.max(unitPrice - discount, 0);
    const totalPriceInput = document.getElementById('total_price');
    if (totalPriceInput) {
        totalPriceInput.value = totalPrice.toFixed(2);
        calculateSaleValues();
    }
}

function handleCustomerTypeChange(selectElement) {
    const row = selectElement.closest('.buyer-row');
    if (!row) return;

    const index = row.getAttribute('data-index');
    const customerDiv = document.getElementById(`buyer-customer-${index}`);
    const investorDiv = document.getElementById(`buyer-investor-${index}`);
    const customerSelect = customerDiv ? customerDiv.querySelector('select') : null;
    const investorSelect = investorDiv ? investorDiv.querySelector('select') : null;

    if (selectElement.value === 'investor') {
        if (customerDiv) customerDiv.style.display = 'none';
        if (investorDiv) investorDiv.style.display = 'flex';
        // إزالة name attribute من select العملاء وإضافته للمستثمرين
        if (customerSelect) {
            customerSelect.removeAttribute('name');
            customerSelect.value = '';
        }
        if (investorSelect) {
            investorSelect.setAttribute('name', `customers[${index}][id]`);
        }
    } else {
        if (customerDiv) customerDiv.style.display = 'flex';
        if (investorDiv) investorDiv.style.display = 'none';
        // إزالة name attribute من select المستثمرين وإضافته للعملاء
        if (investorSelect) {
            investorSelect.removeAttribute('name');
            investorSelect.value = '';
        }
        if (customerSelect) {
            customerSelect.setAttribute('name', `customers[${index}][id]`);
        }
    }
}

function initializeSelect2(container) {
    // إعادة تهيئة Select2 للعناصر الجديدة إذا كانت مكتبة Select2 محملة
    if (typeof $ !== 'undefined' && $.fn.select2) {
        const selects = container.querySelectorAll('.searchable-select2, .searchable-select5');
        selects.forEach(select => {
            $(select).select2({
                width: '100%',
                dir: 'rtl'
            });
        });
    }
}

