// ============================
// مودالات المشاريع، الشركات، الوحدات، العملاء، المستخدمين
// ============================

// --- مودال إضافة مشروع ---
function openAddProjectModal() {
    document.getElementById('addProjectModal').style.display = 'flex';
}

function closeAddProjectModal() {
    document.getElementById('addProjectModal').style.display = 'none';
}

// --- مودال إضافة شركة ---
function openAddCompany() {
    document.getElementById("addCompanyModal").style.display = 'flex';
}

function closeAddCompanyModal() {
    document.getElementById("addCompanyModal").style.display = 'none';
}

// --- مودال إضافة وحدة ---
function openAddUnitModal() {
    document.getElementById('addUnitModal').style.display = 'block';
}

function closeAddUnitModal() {
    document.getElementById('addUnitModal').style.display = 'none';
}

// --- مودال بيع الوحدة ---
function openSellUnitModal(button) {
    const unitId = button.dataset.unitId;
    const unitName = button.dataset.unitName;
    const projectName = button.dataset.projectName;
    const price = button.dataset.price;

    document.getElementById('sale_unit_id').value = unitId;
    document.getElementById('sale_unit_name').innerText = unitName;
    document.getElementById('sale_project_name').innerText = projectName;
    document.getElementById('sale_total_price').value = price;

    const today = new Date().toISOString().split('T')[0]; // yyyy-mm-dd
    document.querySelector('input[name="sale_date"]').value = today;

    document.getElementById('sellUnitModal').style.display = 'flex';
}

function closeSellUnitModal() {
    document.getElementById('sellUnitModal').style.display = 'none';
}

// --- مودال إضافة عميل ---
function openAddClientModal() {
    document.getElementById('addClientModal').style.display = 'flex';
}

function closeAddClientModal() {
    document.getElementById('addClientModal').style.display = 'none';
}

// --- مودال إضافة مستخدم جديد ---
function openAddUserModal() {
    document.getElementById('addUserModal').style.display = 'flex';
}

function closeAddUserModal() {
    document.getElementById('addUserModal').style.display = 'none';
}

// --- مودال الدفعات المالية ---
function togglePaymentModal() {
    const modal = document.getElementById('paymentModal');
    modal.style.display = (modal.style.display === 'flex') ? 'none' : 'flex';
}


document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('.sidebar');
    const toggle = document.querySelector('.menu-toggle');

    if (!sidebar || !toggle) return;

    toggle.addEventListener('click', (e) => {
        if (window.innerWidth <= 768) {
            e.stopPropagation();
            sidebar.classList.toggle('mobile-open');
        }
    });

    document.addEventListener('click', () => {
        sidebar.classList.remove('mobile-open');
    });
});



// ============================
// استيراد Excel
// ============================
function submitImport() {
    const fileInput = document.getElementById('importInput');
    const btn = document.querySelector('.btn-accent-custom');
    
    if (fileInput.files.length > 0) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الرفع...';
        document.getElementById('importForm').submit();
    }
}
function submitImport2() {
    const fileInput = document.getElementById('importInput2');
    const btn = document.querySelector('.btn-accent-custom');
    
    if (fileInput.files.length > 0) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الرفع...';
        document.getElementById('importForm2').submit();
    }
}

function submitImport3() {
    const fileInput = document.getElementById('importInput3');
    const btn = document.querySelector('.btn-accent-custom');
    
    if (fileInput.files.length > 0) {
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
        'addProjectModal', 'sellUnitModal', 'addCompanyModal',
        'addUnitModal', 'addClientModal', 'addUserModal', 'paymentModal'
    ];

    modals.forEach(id => {
        const modal = document.getElementById(id);
        if (event.target === modal) {
            if (id === 'paymentModal') {
                togglePaymentModal();
            } else if (id === 'sellUnitModal') {
                closeSellUnitModal();
            } else if (id === 'addUnitModal') {
                closeAddUnitModal();
            } else if (id === 'addProjectModal') {
                closeAddProjectModal();
            } else if (id === 'addCompanyModal') {
                closeAddCompanyModal();
            } else if (id === 'addClientModal') {
                closeAddClientModal();
            } else if (id === 'addUserModal') {
                closeAddUserModal();
            }
        }
    });
});

// ============================
// Event Listeners للأزرار
// ============================
document.getElementById('openUserModal')?.addEventListener('click', openAddUserModal);

// بيع الوحدة (كل الأزرار)
document.querySelectorAll('.btn-sell').forEach(btn => {
    btn.addEventListener('click', function() { openSellUnitModal(this); });
});

// نموذج بيع الوحدة
document.getElementById('sellUnitForm')?.addEventListener('submit', function() {
    closeSellUnitModal();
});

// نموذج إضافة مشروع
document.getElementById('projectForm')?.addEventListener('submit', function() {
    closeAddProjectModal();
});

// ============================
// Select2 (بحث داخل الـ select) لو موجود
// ============================



document.addEventListener('DOMContentLoaded', function() {
    if (typeof $ !== 'undefined' && $.fn.select2) {
        // كل العناصر التي تحمل class "searchable-select" سيتم تحويلها لـ Select2
        $('.searchable-select').select2({
            width: '100%',
            dir: 'rtl' // لتغيير اتجاه القائمة للعربي
        });
    }
});
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
            placeholder: "اختر مسوق",
            dropdownParent: $('#sellUnitModal')
        }).on('select2:open', function () {
            document.querySelector('.select2-search__field').placeholder = 'ابحث عن مسوق...';
        });

        $('.searchable-select3').select2({
            width: '100%',
            dir: 'rtl',
            placeholder: "اختر مشتري",
            dropdownParent: $('#sellUnitModal')
        }).on('select2:open', function () {
            document.querySelector('.select2-search__field').placeholder = 'ابحث عن مشتري...';
        });
    }
});

// ============================
// Dropdown
// ============================
document.addEventListener('DOMContentLoaded', function() {
    const dropdownBtn = document.getElementById('dropdown-btn');
    const dropdownContent = document.getElementById('dropdown-content');

    dropdownBtn?.addEventListener('mouseenter', function() {
        dropdownContent.style.display = 'block';
    });

    dropdownBtn?.addEventListener('mouseleave', function() {
        setTimeout(() => {
            if (!dropdownContent.matches(':hover') && !dropdownBtn.matches(':hover')) {
                dropdownContent.style.display = 'none';
            }
        }, 100);
    });
});


// مودل اضافة المستخدمين والتعديل
document.addEventListener('DOMContentLoaded', () => {
    const addUserModal = document.getElementById('addUserModal');
    const openBtn = document.getElementById('openUserModal');

    if (!addUserModal || !openBtn) return;

    openBtn.addEventListener('click', () => {
        addUserModal.style.display = 'flex';
    });
});


function openEditModal(el) {
    document.getElementById('editUserModal').style.display = 'flex';

    document.getElementById('editName').value  = el.dataset.name;
    document.getElementById('editEmail').value = el.dataset.email;
    document.getElementById('editRole').value  = el.dataset.role;

    document.getElementById('editUserForm').action =
        `/users/${el.dataset.id}`;
}

function closeEditModal() {
    document.getElementById('editUserModal').style.display = 'none';
}


// مودل الدفعات

function toggleModal() {
    const modal = document.getElementById('paymentModal');
    modal.style.display = (modal.style.display === 'none' || modal.style.display === '') ? 'flex' : 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('paymentModal');
    // إذا كان الهدف المضغطوط عليه هو الـ Overlay نفسه وليس المحتوى الداخلي
    if (event.target == modal) {
        toggleModal();
    }
}

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
                    title: { text: 'المبيعات (ريال)', colors: '#fff' }
                },
                tooltip: {
                    y: { formatter: function (val) { return new Intl.NumberFormat().format(val) + ' ريال'; } },
                    style: {
                        color: '#003d82'
                    }
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
// إدارة القائمة الجانبية على الهواتف الذكية
// ============================

// تفعيل/إخفاء القائمة الجانبية على الشاشات الصغيرة


document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebar) {
        // إضافة حدث النقر على الشعار لتبديل القائمة
        const logo = sidebar.querySelector('.logo');
        if (logo && window.innerWidth <= 768) {
            logo.style.cursor = 'pointer';
            logo.addEventListener('click', function(e) {

                logo.addEventListener('click', (e) => {
                    console.log('LOGO CLICKED');
                    if (window.innerWidth <= 768) {
                        e.stopPropagation();
                        sidebar.classList.toggle('mobile-open');
                    }
                });
                // تجنب الانتشار إذا كنا على سطح المكتب
                if (window.innerWidth <= 768) {
                    e.stopPropagation();
                    sidebar.classList.toggle('mobile-open');
                }
            });
        }
        
        // إغلاق القائمة عند النقر على أي رابط
        const navLinks = sidebar.querySelectorAll('.nav-links a, .nav-links .dropdown-btn');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    // لا تغلق القائمة الفرعية (dropdowns)
                    if (!this.classList.contains('dropdown-btn')) {
                        setTimeout(() => {
                            sidebar.classList.remove('mobile-open');
                        }, 200);
                    }
                }
            });
        });
    }
    
    // معالجة تغيير حجم النافذة
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar && window.innerWidth > 768) {
                // إزالة الحالة على الشاشات الكبيرة
                sidebar.classList.remove('mobile-open');
            }
        }, 250);
    });
});



document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('.sidebar');
    const toggle = document.querySelector('.menu-toggle');

    if (!sidebar || !toggle) return;

    toggle.addEventListener('click', (e) => {
        if (window.innerWidth <= 768) {
            e.stopPropagation();
            sidebar.classList.toggle('mobile-open');
        }
    });
});
// تحسين الـ Dropdown على الهواتف
function toggleDropdown() {
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

// تبديل الوضع الليلي
        function toggleTheme() {
            const html = document.documentElement;
            const icon = document.getElementById('theme-icon');
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

        // حفظ الاختيار
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            document.getElementById('theme-icon').className = 'fa-solid fa-sun';
        }

        // فتح القائمة المنسدلة
        function toggleDropdown(event) {
            event.preventDefault();
            const btn = event.currentTarget;
            const menu = document.getElementById('company-menu');
            if (menu) {
                menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
                btn.classList.toggle('active');
            }
        }


        document.addEventListener('DOMContentLoaded', function () {
            const companySelect = document.getElementById('companySelect');
            const projectSelect = document.getElementById('projectSelect');
        
            if (!companySelect || !projectSelect) return;
        
            companySelect.addEventListener('change', function () {
                const companyId = this.value;
                projectSelect.innerHTML = '<option value="">جميع المشاريع</option>';
        
                if (!companyId) return;
        
                fetch(`/companies/${companyId}/projects`)
                    .then(res => res.json())
                    .then(projects => {
                        projects.forEach(p => {
                            const option = document.createElement('option');
                            option.value = p.id;
                            option.textContent = p.name;
                            projectSelect.appendChild(option);
                        });
                    })
                    .catch(err => console.error(err));
            });
        });
        
        document.addEventListener('DOMContentLoaded', () => {
            const menu = document.getElementById('menu');
            if (!menu) return;
        
            menu.addEventListener('click', function() {
                this.classList.toggle('open');
            });
        });


        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.querySelector('.sidebar');
            const toggle = document.querySelector('.menu-toggle');
        
            if (!sidebar || !toggle) return;
        
            toggle.addEventListener('click', (e) => {
                if (window.innerWidth <= 768) {
                    e.stopPropagation();
                    sidebar.classList.toggle('mobile-open');
                }
            });
        
            document.addEventListener('click', () => {
                sidebar.classList.remove('mobile-open');
            });
        });
        