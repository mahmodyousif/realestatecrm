<?php

use App\Models\Unit;
use App\Models\Report;
use App\Models\Company;
use App\Models\Project;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UnitsController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\ReportsController ;
use App\Http\Controllers\UnitSaleController;
use App\Http\Controllers\CompaniesController;

use App\Http\Controllers\CustomersController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UsersController;
  

Route::middleware(['auth' , 'role:admin'])->group(function(){
  Route::get('/projects/export' , [ProjectsController::class , 'export'])->name('projects_export') ;
    Route::get('users' , [UsersController::class , 'index'] )->name('users') ;    
    Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');

    Route::post('add_user' , [UsersController::class, 'store'])->name('add_user') ;
    Route::get('customers', [CustomersController::class, 'index'])->name('customers');
    Route::get('customers/{id}', [CustomersController::class , 'show'])->name('customer.show'); 
    Route::post('add_customer',[CustomersController::class,'store'])->name('add_customer') ;
    Route::get('edit_customer/{id}', [CustomersController::class, 'edit'])->name('edit_customer') ;
    Route::put('update_customer/{id}', [CustomersController::class, 'update'])->name('update_customer') ;
    Route::get('marketers/{id}', [CustomersController::class , 'marketerShow'])->name('marketer.show'); 
    Route::get('company/{id}', [CompaniesController::class,'index'])->name('company');
    Route::post('add_company', [CompaniesController::class, 'store'])->name('add_company');


    // units crud 
    Route::get('edit_unit/{id}', [UnitsController::class, 'edit'])->name('edit_unit') ;
    Route::put('update_unit/{id}', [UnitsController::class, 'update'])->name('update_unit') ;
    Route::get('delete_unit/{id}', [UnitsController::class, 'delete'])->name('delete_unit') ;
    Route::post('add_unit', [UnitsController::class,'store'])->name('add_unit');


    // Projects Crud 

    Route::get('projects' , [ProjectsController::class,'index'])->name("projects") ;
    Route::post('add_project', [ProjectsController::class, 'add_project']) ->name('add_project');
    Route::get('/projects/{project}', [ProjectsController::class, 'show'])->name('project.show');
    Route::get('edit_project/{id}', [ProjectsController::class, 'edit'])->name('edit_project') ;
    Route::put('update_project/{id}', [ProjectsController::class, 'update'])->name('update_project') ;
    Route::get('delete_project/{id}', [ProjectsController::class, 'delete'])->name('delete_project') ;


    // EXPORTS 

    Route::get('/units/export', [UnitsController::class, 'export'])->name('unit_export');
    Route::get('/unitsales/export', [UnitSaleController::class, 'export'])->name('sales_export');
    Route::get('reports/export' , [ReportsController::class , 'export'])->name('reports.export');
    Route::get('/payments/export/{id}', [PaymentsController::class, 'exportCustomerPayments'])
        ->name('paymentCustomer.export');

    Route::get('/customers/{id}/export-full', [CustomersController::class, 'exportCustomerFull'])->name('customers.exportFull');
    Route::get('/projects/{project}/export', [ProjectsController::class, 'exportInfo'])
    ->name('projects.info.export');
    Route::get('/company/{id}/export', [CompaniesController::class, 'export'])
     ->name('company.export');
    Route::POST('/projects/import' , [ProjectsController::class , 'import'])->name('projects.import'); 
    Route::POST('/units/import', [UnitsController::class , 'import'])->name('unit.import') ;
    Route::POST('/customers/import', [CustomersController::class , 'import'])->name('customers.import') ;
}) ;







// الشركات 


// الوحدات

Route::middleware(['auth' , 'role:seller'])->group(function(){
    Route::get('units', [UnitsController::class, 'index'])->name('units');
    Route::get('/units/{unit}', [UnitsController::class, 'show'])->name('units.show');
    Route::post('unit_sell',[UnitSaleController::class,'store'])->name('unit_sell');
}); 




// التقارير 



Route::middleware(['auth' , 'role:accountant'])->group(function(){
    Route::get('reports' , [ReportsController::class , 'index'])->name('reports') ;
    // ادارة الدفعات
    Route::get('payments' , [PaymentsController::class, 'index'])->name('payments');
    Route::post('add_payment', [PaymentsController::class,'store'])->name('add_payment');
    Route::get('payments/{id}' , [PaymentsController::class, 'show'])->name('payments.show') ;
}) ;


    Route::middleware(['auth'])->group(function() {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    });



    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

