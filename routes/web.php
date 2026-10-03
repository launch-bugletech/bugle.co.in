<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\backend\BackendController;
use App\Http\Controllers\frontend\CustomerController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Backend\AttendanceController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/login', function () {
	return view('frontend.auth.login');
});

Route::get('/', function () {
	return view('frontend.auth.login');
	// /return redirect()->route('backend.home');
});
Route::post('login', 'App\Http\Controllers\Auth\LoginController@login')->name('login');
// Route::get('/otp-verify/{id}', 'App\Http\Controllers\frontend\SignupController@otpVerify')->name('otpVerify');
// Route::post('/verified', 'App\Http\Controllers\frontend\SignupController@verifiedOtp')->name('verifiedOtp');
Route::get('/', 'App\Http\Controllers\frontend\SignupController@loadLogin')->name('loadLogin');
Route::post('/login', 'App\Http\Controllers\frontend\SignupController@userLogin')->name('userLogin');
Route::post('/dashboard', 'App\Http\Controllers\SignupController@loadDashboard')->name('loadDashboard');

//Route::post('/verified', [App\Http\Controllers\LoginController::class, 'verifiedOtp'])->name('verifiedOtp');


// Password Reset Routes
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
Route::get('/logout', [LogoutController::class, 'logout'])->name('logout');

Route::get('customer/signup', [CustomerController::class, 'create'])->name('customer-create');
Route::POST('/customer/store', [App\Http\Controllers\frontend\CustomerController::class, 'store'])->name('customer-store');
Route::get('/access-denied', [App\Http\Controllers\frontend\CustomerController::class, 'accessDenied'])->name('accessDenied');
Route::get('/contact-us', [App\Http\Controllers\frontend\CustomerController::class, 'contact'])->name('contact');
Route::get('/about-us', [App\Http\Controllers\frontend\CustomerController::class, 'about'])->name('about');
Route::get('/terms-condition', [App\Http\Controllers\frontend\CustomerController::class, 'termCondition'])->name('termCondition');
Route::POST('/contact/store', [App\Http\Controllers\frontend\CustomerController::class, 'contactStore'])->name('contactStore');

//Akuntha

Route::get('/signup', [App\Http\Controllers\frontend\SignupController::class, 'create'])->name('create');
Route::POST('/signup/store', [App\Http\Controllers\frontend\SignupController::class, 'store'])->name('store');

Auth::routes();
Route::group(['middleware' => ['auth', 'isCustomer']], function () {

	Route::get('/dashboard', [App\Http\Controllers\frontend\DashboardController::class, 'index'])->name('index');
	Route::get('/customer-draft', [App\Http\Controllers\frontend\DraftController::class, 'customerDraft'])->name('customerDraft');
	Route::POST('/customer-draft/store', [App\Http\Controllers\frontend\DraftController::class, 'store'])->name('store');
	Route::get('/door-style/{id}', [App\Http\Controllers\frontend\DraftController::class, 'doorStyle'])->name('doorStyle');
	Route::post('/add-draft', [App\Http\Controllers\frontend\DraftController::class, 'CustomerDraftStyle'])->name('addDraft');
	Route::get('/door-style/change/{draft_style_id}', [App\Http\Controllers\frontend\DraftController::class, 'change_draft_style_get'])->name('change_draft_style_get');
	Route::post('/add-draft/change/{id}/{draft_style_id}', [App\Http\Controllers\frontend\DraftController::class, 'change_draft_style_post'])->name('change_draft_style_post');

	Route::POST('/modification/store', [App\Http\Controllers\backend\ModificationController::class, 'store'])->name('modificationmaster.store');

	Route::get('/public/img/texExemptForm/{filename}', [App\Http\Controllers\backend\CustomerManagementController::class, 'downloadFile'])->name('download.file');


	Route::get('download', [App\Http\Controllers\backend\CustomerManagementController::class, 'download'])->name('download');


	Route::get('/reset_password/edit/', '\App\Http\Controllers\frontend\ResetPasswordController@edit')->name('edit');
	//Route::get('/reset_password/changePassword/', 'App\Http\Controllers\ResetPasswordController@changePassword')->name('reset_password.update');
	Route::PATCH('change-password/', '\App\Http\Controllers\frontend\ResetPasswordController@changePassword')->name('change-password');
	//Customer Edit Profile Module

	Route::get('/customer_edit_profile/edit/', '\App\Http\Controllers\frontend\CustomerEditProfileController@edit')->name('edit');
	Route::PATCH('/customer_edit_profile/update/{id}', '\App\Http\Controllers\frontend\CustomerEditProfileController@update')->name('customer_edit_profile.update');

	//Route::get('/draft/autocomplete', '\App\Http\Controllers\backend\DraftController@autocomplete')->name('draft.autocomplete');
	Route::POST('/draft-product/store/{customer_draft_id}', [App\Http\Controllers\frontend\DraftController::class, 'draft_product_store'])->name('draft-product-store');
	Route::get('/delete-draft-style/{draft_style_id}', [App\Http\Controllers\frontend\DraftController::class, 'delete_draft_style'])->name('delete-draft-style');
	Route::get('/new-draft/{customer_draft_Id}', [App\Http\Controllers\frontend\DraftController::class, 'showProduct'])->name('new-draft');
	Route::get('/get-draft-products', [App\Http\Controllers\frontend\DraftController::class, 'get_draft_products'])->name('get-draft-products');
	Route::get('/store-draft-product', [App\Http\Controllers\frontend\DraftController::class, 'store_draft_product'])->name('store-draft-product');
	Route::get('/get-products-by-sku-and-door-style', [App\Http\Controllers\frontend\DraftController::class, 'getProductsBySku'])->name('new-draft');
	Route::get('/get-modifications-by-product', [App\Http\Controllers\frontend\DraftController::class, 'getModificationsByProduct'])->name('get-modifications-by-product');
	Route::get('/add-modification-to-draft-product', [App\Http\Controllers\frontend\DraftController::class, 'addModificationToDraftProduct'])->name('add-modification-to-draft-product');
	Route::get('/delete-modification-from-draft-product', [App\Http\Controllers\frontend\DraftController::class, 'deleteModificationFromDraftProduct']);
	Route::get('/get-modification-values', [App\Http\Controllers\frontend\DraftController::class, 'getModificationValues']);
	Route::get('/edit-modification-info', [App\Http\Controllers\frontend\DraftController::class, 'editModificationInfo']);
	Route::get('/get-accessories-by-product', [App\Http\Controllers\frontend\DraftController::class, 'getaccessoriesByProduct'])->name('get-accessories-by-product');
	Route::get('/add-accessorie-to-draft-product', [App\Http\Controllers\frontend\DraftController::class, 'addaccessorieToDraftProduct'])->name('add-accessorie-to-draft-product');
	Route::get('/delete-accessorie-from-draft-product', [App\Http\Controllers\frontend\DraftController::class, 'deleteAccessorieFromDraftProduct']);
	Route::get('/quantity-update', [App\Http\Controllers\frontend\DraftController::class, 'quantity_update'])->name('quantity-update');
	Route::get('/quantity-plus', [App\Http\Controllers\frontend\DraftController::class, 'quantity_plus'])->name('quantity-plus');
	Route::get('/quantity-minus', [App\Http\Controllers\frontend\DraftController::class, 'quantity_minus'])->name('quantity-minus');
	Route::get('/add-hinge', [App\Http\Controllers\frontend\DraftController::class, 'add_hinge'])->name('add-hinge');
	Route::get('/add-finish-side', [App\Http\Controllers\frontend\DraftController::class, 'add_finish_side'])->name('add-finish-side');
	Route::get('/update-servie-configuration', [App\Http\Controllers\frontend\DraftController::class, 'updateServieConfiguration']);
	Route::get('/draft-product/{draft_style_id}', [App\Http\Controllers\frontend\CustomerController::class, 'draft_product'])->name('draft-product');
	// Route::get('/add-cart/{customer_draft_Id}',[App\Http\Controllers\frontend\DraftController::class,'addCart'])->name('add-cart');
	Route::get('/add-cart/{customer_draft_Id}', [App\Http\Controllers\frontend\TrackingStatusController::class, 'addCart'])->name('add-cart');
	Route::get('/draft-product/delete/{id}', '\App\Http\Controllers\frontend\DraftController@draft_product_destroy')->name('draft-product-delete');
	Route::get('/draft/delete/{id}', '\App\Http\Controllers\frontend\DraftController@draft_destroy')->name('draft-delete');
	Route::get('save-draft/{status}/{id}/{price}/{original_price}', '\App\Http\Controllers\frontend\DraftController@save_draft')->name('save_draft');
	Route::get('credit-card/authentication/{customer_draft_Id}', [App\Http\Controllers\frontend\DraftController::class, 'credit_card_authentication'])->name('credit-card-authentication');
	Route::POST('store_credit_card/{customer_draft_Id}', [App\Http\Controllers\frontend\DraftController::class, 'store_credit_card'])->name('credit-card-authentication');
	Route::get('payment/{customer_draft_Id}', [App\Http\Controllers\frontend\DraftController::class, 'order'])->name('payment');

	Route::get('/card-payment/{id}', [App\Http\Controllers\frontend\DraftController::class, 'card_payment'])->name('card-payment');
	Route::get('/bank-payment', [App\Http\Controllers\frontend\DraftController::class, 'bank_payment'])->name('bank-payment');
	Route::get('/success/{id}/{payment_id?}', [App\Http\Controllers\frontend\DraftController::class, 'success'])->name('success');

	Route::get('/payment-quickbooks', [App\Http\Controllers\frontend\QuickBooksController::class, 'showPaymentForm'])->name('payment-form');
	Route::get('/initiate-payment/{id}', [App\Http\Controllers\frontend\QuickBooksController::class, 'initiatePayment'])->name('initiate-payment');
	Route::get('/oauth/callback', [App\Http\Controllers\frontend\QuickBooksController::class, 'paymentCallback'])->name('payment-callback');
	Route::post('/add-bank-account', [App\Http\Controllers\frontend\QuickBooksController::class, 'addBankAccount']);
	Route::post('/create-invoice2', [App\Http\Controllers\frontend\QuickBooksController::class, 'createInvoice2']);

	Route::get('/add_material', [App\Http\Controllers\frontend\DraftController::class, 'add_material'])->name('add_material');

	//CreditCard

	Route::get('/payment', [App\Http\Controllers\frontend\CreditcardController::class, 'index'])->name('index');
	Route::get('/card/create', [App\Http\Controllers\frontend\CreditcardController::class, 'create'])->name('create');
	Route::POST('/payment/store', [App\Http\Controllers\frontend\CreditcardController::class, 'store'])->name('store');
	Route::get('/payment/delete/{id}', [App\Http\Controllers\frontend\CreditcardController::class, 'destroy'])->name('delete');
	Route::get('/payment/edit/{id}', [App\Http\Controllers\frontend\CreditcardController::class, 'edit'])->name('edit');
	Route::PATCH('/payment/update/{id}', [App\Http\Controllers\frontend\CreditcardController::class, 'update'])->name('update');


	//Tracking Status

	Route::get('/tracking-status', [App\Http\Controllers\frontend\TrackingStatusController::class, 'index'])->name('index');
	Route::get('/tracking-status/view/{id}', [App\Http\Controllers\frontend\TrackingStatusController::class, 'view'])->name('view');
	Route::POST('/tracking-status/fetch-style', [App\Http\Controllers\frontend\TrackingStatusController::class, 'fetchStyles'])->name('fetchStyles');

	Route::get('material-request/index', [App\Http\Controllers\frontend\MaterialRequestController::class, 'index'])->name('material-request-index');
	Route::get('/material-request/create', [App\Http\Controllers\frontend\MaterialRequestController::class, 'create'])->name('material-request-create');
	Route::POST('/material-request/store', [App\Http\Controllers\frontend\MaterialRequestController::class, 'store'])->name('material-request.store');
	Route::get('/material-request/delete/{id}', [App\Http\Controllers\frontend\MaterialRequestController::class, 'destroy'])->name('delete');
	Route::get('/material-request/edit/{id}', [App\Http\Controllers\frontend\MaterialRequestController::class, 'edit'])->name('material-request-edit');
	Route::PATCH('/material-request-update/{id}', [App\Http\Controllers\frontend\MaterialRequestController::class, 'update'])->name('material-request-update');

	Route::POST('/material-product/store', [App\Http\Controllers\frontend\MaterialRequestController::class, 'marerialProductstore'])->name('material-product.store');
	Route::get('/material-product/delete/{id}/{guid}', [App\Http\Controllers\frontend\MaterialRequestController::class, 'marerialProductsdestroy'])->name('material-product.destroy');

	Route::get('/material-request/create-request', [App\Http\Controllers\frontend\MaterialRequestController::class, 'create_request'])->name('create-request');
	Route::POST('/material-request/store-request', [App\Http\Controllers\frontend\MaterialRequestController::class, 'store_request'])->name('store-request');
	Route::get('/material-request/create-substation/{id}', [App\Http\Controllers\frontend\MaterialRequestController::class, 'create_substation'])->name('create-substation');
	Route::POST('/material-request/store-substation/{id}', [App\Http\Controllers\frontend\MaterialRequestController::class, 'store_substation'])->name('store-substation');
	Route::get('/material-request/create-product/{id}', [App\Http\Controllers\frontend\MaterialRequestController::class, 'create_product'])->name('create-product');
	// Route::POST('/material-request/store-product/{id}', [App\Http\Controllers\frontend\MaterialRequestController::class,'store_product'])->name('store-product');
	Route::get('/material-request/store-product', [App\Http\Controllers\frontend\MaterialRequestController::class, 'store_product'])->name('store_product');
	Route::get('/material-request/get-products', [App\Http\Controllers\frontend\MaterialRequestController::class, 'get_products'])->name('get_products');
	Route::get('/material-request/delete-product/{id}', [App\Http\Controllers\frontend\MaterialRequestController::class, 'delete_product'])->name('get_products');
	Route::get('/material-request/delete-substation/{id}', [App\Http\Controllers\frontend\MaterialRequestController::class, 'delete_substation'])->name('get_products');
	Route::get('/material-request/quantity-update', [App\Http\Controllers\frontend\MaterialRequestController::class, 'quantity_update'])->name('quantity-update');
	Route::get('/material-request/quantity-plus', [App\Http\Controllers\frontend\MaterialRequestController::class, 'quantity_plus'])->name('quantity-plus');
	Route::get('/material-request/quantity-minus', [App\Http\Controllers\frontend\MaterialRequestController::class, 'quantity_minus'])->name('quantity-minus');
	Route::get('/material-request/change-unit', [App\Http\Controllers\frontend\MaterialRequestController::class, 'change_unit'])->name('change-unit');
	Route::get('material-request/get-products-by-name', [App\Http\Controllers\frontend\MaterialRequestController::class, 'getProductsByName'])->name('get-products-by-name');
	Route::get('/material-request/view/{id}', [App\Http\Controllers\frontend\MaterialRequestController::class, 'view']);

	Route::get('testing-request/index', [App\Http\Controllers\frontend\TestingRequestController::class, 'index'])->name('testing-request-index');
	Route::get('/testing-request/create', [App\Http\Controllers\frontend\TestingRequestController::class, 'create'])->name('testing-request-create');
	Route::POST('/testing-request/store', [App\Http\Controllers\frontend\TestingRequestController::class, 'store'])->name('testing-request.store');
	Route::get('/testing-request/delete/{id}', [App\Http\Controllers\frontend\TestingRequestController::class, 'destroy'])->name('delete');
	Route::get('/testing-request/edit/{id}', [App\Http\Controllers\frontend\TestingRequestController::class, 'edit'])->name('testing-request-edit');
	Route::PATCH('/testing-request-update/{id}', [App\Http\Controllers\frontend\TestingRequestController::class, 'update'])->name('testing-request-update');

	Route::get('/testing-request/create-equipment/{id}', [App\Http\Controllers\frontend\TestingRequestController::class, 'create_equipment'])->name('create-equipment');
	Route::POST('/testing-request/store-equipment/{id}', [App\Http\Controllers\frontend\TestingRequestController::class, 'store_equipment'])->name('store-equipment');
	Route::get('/testing-request/create-testing-type/{id}', [App\Http\Controllers\frontend\TestingRequestController::class, 'create_testing_type'])->name('create-create_testing_type');
	Route::get('testing-request/get-equipment-by-name', [App\Http\Controllers\frontend\TestingRequestController::class, 'getEquipmentByName'])->name('get-equipment-by-name');
	Route::get('/testing-request/store-testing-type', [App\Http\Controllers\frontend\TestingRequestController::class, 'store_testing_type'])->name('store_testing_type');
	Route::get('/testing-request/get-testing-type', [App\Http\Controllers\frontend\TestingRequestController::class, 'get_testing_type'])->name('get_testing_type');
	Route::get('/testing-request/delete-testing-type/{id}', [App\Http\Controllers\frontend\TestingRequestController::class, 'delete_testing_type'])->name('delete_testing_type');
	Route::get('/testing-request/delete-equipment/{id}', [App\Http\Controllers\frontend\TestingRequestController::class, 'delete_equipment'])->name('delete_equipment');
	Route::POST('/testing-request/store-request', [App\Http\Controllers\frontend\TestingRequestController::class, 'storeRequest'])->name('storeRequest');



	//Testing-Completion
	Route::get('testing-completion/index', [App\Http\Controllers\frontend\TestingCompletionController::class, 'index'])->name('testing-completion-index');
	Route::get('/testing-completion/create', [App\Http\Controllers\frontend\TestingCompletionController::class, 'create'])->name('testing-completion-create');
	Route::POST('/testing-completion/store', [App\Http\Controllers\frontend\TestingCompletionController::class, 'store'])->name('testing-completion.store');
	Route::get('/testing-completion/delete/{id}', [App\Http\Controllers\frontend\TestingCompletionController::class, 'destroy'])->name('delete');
	Route::get('/testing-completion/edit/{id}', [App\Http\Controllers\frontend\TestingCompletionController::class, 'edit'])->name('testing-completion-edit');
	Route::PATCH('/testing-completion-update/{id}', [App\Http\Controllers\frontend\TestingCompletionController::class, 'update'])->name('testing-completion-update');

	Route::get('/testing-completion/add-equipment/{id}', [App\Http\Controllers\frontend\TestingCompletionController::class, 'add_equipment'])->name('add-equipment');
	Route::POST('/testing-completion/store-equipment/{id}', [App\Http\Controllers\frontend\TestingCompletionController::class, 'store_equipment'])->name('store-equipment');
	Route::get('/testing-completion/create-testing-type/{id}', [App\Http\Controllers\frontend\TestingCompletionController::class, 'create_testing_type'])->name('create-create_testing_type');
	Route::get('testing-completion/get-equipment-by-name', [App\Http\Controllers\frontend\TestingCompletionController::class, 'getEquipmentByName'])->name('get-equipment-by-name');
	Route::get('/testing-completion/store-testing-type', [App\Http\Controllers\frontend\TestingCompletionController::class, 'store_testing_type'])->name('store_testing_type');
	Route::get('/testing-completion/get-testing-type', [App\Http\Controllers\frontend\TestingCompletionController::class, 'get_testing_type'])->name('get_testing_type');
	Route::get('/testing-completion/delete-testing-type/{id}', [App\Http\Controllers\frontend\TestingCompletionController::class, 'delete_testing_type'])->name('delete_testing_type');
	Route::get('/testing-completion/delete-equipment/{id}', [App\Http\Controllers\frontend\TestingCompletionController::class, 'delete_equipment'])->name('delete_equipment');
	Route::POST('/testing-completion/store-request', [App\Http\Controllers\frontend\TestingCompletionController::class, 'storeRequest'])->name('storeRequest');

	//Defect
	Route::get('defect/index', [App\Http\Controllers\frontend\DefectController::class, 'index'])->name('index');
	Route::get('defect/create', [App\Http\Controllers\frontend\DefectController::class, 'create'])->name('create');
	Route::POST('defect/store', [App\Http\Controllers\frontend\DefectController::class, 'store'])->name('store');
	Route::get('defect/delete/{id}', [App\Http\Controllers\frontend\DefectController::class, 'destroy'])->name('delete');
	Route::get('defect/edit/{id}', [App\Http\Controllers\frontend\DefectController::class, 'edit'])->name('edit');
	Route::PATCH('defect/update/{id}', [App\Http\Controllers\frontend\DefectController::class, 'update'])->name('update');
	Route::POST('defect-image/delete/{id}', [App\Http\Controllers\frontend\DefectController::class, 'imageDelete'])->name('imageDelete');

	//Report

	Route::get('/report', [App\Http\Controllers\frontend\ReportController::class, 'index'])->name('index');
	Route::any('/order-history', [App\Http\Controllers\frontend\ReportController::class, 'orderHistory'])->name('orderHistory');
	Route::get('/sales-orders/{id}', [App\Http\Controllers\frontend\ReportController::class, 'salesOrders'])->name('salesOrders');
	Route::get('/inventory-levels', [App\Http\Controllers\frontend\ReportController::class, 'inventoryLevels'])->name('inventoryLevels');
	Route::get('/sales-test', [App\Http\Controllers\frontend\ReportController::class, 'salesTest'])->name('salesTest');



	//Specbook
	Route::get('/specbook', [App\Http\Controllers\frontend\SpecbookController::class, 'index'])->name('index');
	Route::POST('/specbook', [App\Http\Controllers\frontend\SpecbookController::class, 'compare_draft'])->name('compare_draft');
	Route::get('/get-category-products', [App\Http\Controllers\frontend\SpecbookController::class, 'getProducts'])->name('getProducts');


	//RMA's

	Route::get('/rma-s', [App\Http\Controllers\frontend\RMAController::class, 'index'])->name('index');
	Route::get('/replace/{id}', [App\Http\Controllers\frontend\RMAController::class, 'view'])->name('view');
	Route::POST('/replace-order', [App\Http\Controllers\frontend\RMAController::class, 'replaceOrder'])->name('replace-order');

	//ExpensesReport

	Route::get('expenses_report/create', [App\Http\Controllers\frontend\ExpensesReportController::class, 'create'])->name('create');
	Route::get('expenses_report/index', [App\Http\Controllers\frontend\ExpensesReportController::class, 'index'])->name('index');
	Route::POST('/expenses_report/store', [App\Http\Controllers\frontend\ExpensesReportController::class, 'store'])->name('store');
	Route::get('expenses_report/edit/{id}', [App\Http\Controllers\frontend\ExpensesReportController::class, 'edit'])->name('edit');
	Route::get('/expenses_report/delete/{id}', [App\Http\Controllers\frontend\ExpensesReportController::class, 'destroy'])->name('delete');
	Route::PATCH('/expenses_report/update/{id}', [App\Http\Controllers\frontend\ExpensesReportController::class, 'update'])->name('update');





});


Auth::routes();
Route::group(['middleware' => ['auth', 'isAdmin']], function () {
	Route::prefix('admin')->group(function () {
		Route::get('home', [BackendController::class, 'index'])->name('admin.home');
		Route::post('/attendance/fetch', [BackendController::class, 'fetchAttendance'])->name('attendance.fetch');

		//Products
		Route::get('product-add', [App\Http\Controllers\backend\ProductController::class, 'create'])->name('admin.product-add');
		Route::Post('/product-store', [App\Http\Controllers\backend\ProductController::class, 'store'])->name('admin.product-store');
		Route::get('product/edit/{id}', [App\Http\Controllers\backend\ProductController::class, 'edit'])->name('admin.product.edit');
		Route::get('/product/delete/{id}', [App\Http\Controllers\backend\ProductController::class, 'destroy'])->name('admin.delete');
		Route::PATCH('/product/update/{id}', [App\Http\Controllers\backend\ProductController::class, 'update'])->name('admin.update');
		Route::get('product-list', [App\Http\Controllers\backend\ProductController::class, 'index'])->name('admin.product-list');

		//ExpensesMaster
		Route::get('expenses-add', [App\Http\Controllers\backend\ExpensesMasterController::class, 'create'])->name('admin.expenses-add');
		Route::get('expenses-list', [App\Http\Controllers\backend\ExpensesMasterController::class, 'index'])->name('admin.expenses-list');
		Route::Post('/expenses-store', [App\Http\Controllers\backend\ExpensesMasterController::class, 'store'])->name('admin.expenses-store');
		Route::get('expenses/edit/{id}', [App\Http\Controllers\backend\ExpensesMasterController::class, 'edit'])->name('admin.expenses.edit');
		Route::get('/expenses/delete/{id}', [App\Http\Controllers\backend\ExpensesMasterController::class, 'destroy'])->name('admin.delete');
		Route::PATCH('/expenses/update/{id}', [App\Http\Controllers\backend\ExpensesMasterController::class, 'update'])->name('admin.update');
		Route::get('expenses-record', [App\Http\Controllers\backend\ExpensesMasterController::class, 'approveList'])->name('admin.expenses-record');
		Route::get('viewRecord/{id}', [App\Http\Controllers\backend\ExpensesMasterController::class, 'viewRecord'])->name('admin.viewRecord');
		Route::get('/expenses-view/approve/{id}', [\App\Http\Controllers\backend\ExpensesMasterController::class, 'viewApprove'])->name('viewApprove');
		Route::get('/expenses-view/reject/{id}', [\App\Http\Controllers\backend\ExpensesMasterController::class, 'viewReject'])->name('viewReject');

		// Items

		Route::get('item-list', [App\Http\Controllers\backend\ItemController::class, 'index'])->name('admin.item-list');
		Route::get('item-view/{id}', [App\Http\Controllers\backend\ItemController::class, 'view'])->name('admin.item.view');
		Route::get('item-add', [App\Http\Controllers\backend\ItemController::class, 'create'])->name('admin.item-add');
		Route::Post('/item-store', [App\Http\Controllers\backend\ItemController::class, 'store'])->name('admin.item-store');
		Route::get('item-edit/{id}', [App\Http\Controllers\backend\ItemController::class, 'edit'])->name('admin.item.edit');
		Route::get('/item/delete/{id}', [App\Http\Controllers\backend\ItemController::class, 'destroy'])->name('item.delete');
		Route::PATCH('/item/update/{id}', [App\Http\Controllers\backend\ItemController::class, 'update'])->name('item.update');
		Route::get('item-duplicate/{id}', [App\Http\Controllers\backend\ItemController::class, 'duplicate'])->name('admin.item.duplicate');
		Route::Post('/item/duplicate/{id}', [App\Http\Controllers\backend\ItemController::class, 'store_duplicate'])->name('item.duplicate');


		//Modification

		Route::get('modification/create', [App\Http\Controllers\backend\ModificationController::class, 'create'])->name('modification-create');
		Route::POST('/modification/store', [App\Http\Controllers\backend\ModificationController::class, 'store'])->name('modificationmaster.store');
		Route::get('modification/index', [App\Http\Controllers\backend\ModificationController::class, 'index'])->name('modification-index');
		Route::get('/modification/delete/{id}', [App\Http\Controllers\backend\ModificationController::class, 'destroy'])->name('delete');


		//Customer group

		Route::get('/customer-group/index', '\App\Http\Controllers\backend\CustomerGroupController@index')->name('index');
		Route::get('/customer-group/create', '\App\Http\Controllers\backend\CustomerGroupController@create')->name('create');
		Route::POST('/customer-group/store', '\App\Http\Controllers\backend\CustomerGroupController@store')->name('store');
		Route::get('/customer-group/edit/{id}', '\App\Http\Controllers\backend\CustomerGroupController@edit')->name('edit');
		Route::PATCH('/customer-group/update/{id}', '\App\Http\Controllers\backend\CustomerGroupController@update')->name('update');
		Route::get('/customer-group/delete/{id}', '\App\Http\Controllers\backend\CustomerGroupController@destroy')->name('delete');



		//Customer Grouping

		Route::get('/customer-grouping/index', '\App\Http\Controllers\backend\CustomerGroupingController@index')->name('index');
		Route::get('/customer-grouping/edit/{id}', '\App\Http\Controllers\backend\CustomerGroupingController@edit')->name('edit');
		Route::PATCH('/customer-grouping/update/{id}', '\App\Http\Controllers\backend\CustomerGroupingController@update')->name('update');
		Route::POST('/customer-grouping/fetch-customers', '\App\Http\Controllers\backend\CustomerGroupingController@fetchCustomers')->name('fetchCustomers');

		//Add Modification

		Route::get('addmodification/index', [App\Http\Controllers\backend\AddmodificationController::class, 'index'])->name('addmodification-index');
		Route::get('/addmodification/create', [App\Http\Controllers\backend\AddmodificationController::class, 'create'])->name('addmodification-create');
		Route::POST('/backend/addmodification/store', [App\Http\Controllers\backend\AddmodificationController::class, 'store'])->name('addmodification.store');
		Route::get('/addmodification/delete/{id}', [App\Http\Controllers\backend\AddmodificationController::class, 'destroy'])->name('delete');
		Route::get('/addmodification/edit/{id}', [App\Http\Controllers\backend\AddmodificationController::class, 'edit'])->name('addmodification-edit');
		Route::PATCH('/addmodification-update/{id}', [App\Http\Controllers\backend\AddmodificationController::class, 'update'])->name('addmodification-update');


		//Add Accessories

		Route::get('accessories/index', [App\Http\Controllers\backend\AccessoriesController::class, 'index'])->name('accessories-index');
		Route::get('/accessories/create', [App\Http\Controllers\backend\AccessoriesController::class, 'create'])->name('accessories-create');
		Route::POST('/backend/accessories/store', [App\Http\Controllers\backend\AccessoriesController::class, 'store'])->name('accessories.store');
		Route::get('/accessories/delete/{id}', [App\Http\Controllers\backend\AccessoriesController::class, 'destroy'])->name('delete');
		Route::get('/accessories/edit/{id}', [App\Http\Controllers\backend\AccessoriesController::class, 'edit'])->name('accessories-edit');
		Route::PATCH('/accessories-update/{id}', [App\Http\Controllers\backend\AccessoriesController::class, 'update'])->name('accessories-update');

		//Add Substations

		Route::get('substations/index', [App\Http\Controllers\backend\SubstationsController::class, 'index'])->name('substations-index');
		Route::get('/substation/create', [App\Http\Controllers\backend\SubstationsController::class, 'create'])->name('substation-create');
		Route::POST('/substation/store', [App\Http\Controllers\backend\SubstationsController::class, 'store'])->name('substation.store');
		Route::get('/substation/delete/{id}', [App\Http\Controllers\backend\SubstationsController::class, 'destroy'])->name('delete');
		Route::get('/substation/edit/{id}', [App\Http\Controllers\backend\SubstationsController::class, 'edit'])->name('substation-edit');
		Route::PATCH('/substation-update/{id}', [App\Http\Controllers\backend\SubstationsController::class, 'update'])->name('substation-update');
		Route::get('/substation-engineer/view/{id}', [App\Http\Controllers\backend\SubstationsController::class, 'view'])->name('view');
		Route::get('/engineer/create/{id}', [App\Http\Controllers\backend\SubstationsController::class, 'Engineeradd'])->name('engineeradd');
		Route::POST('/engineer/store', [App\Http\Controllers\backend\SubstationsController::class, 'Engineerstore'])->name('engineerstore');
		Route::get('/engineer/delete/{id}', [App\Http\Controllers\backend\SubstationsController::class, 'Engineerdestroy'])->name('engineerdelete');

		//Material Request 
		Route::any('material-request/index', [App\Http\Controllers\backend\MaterialRequestController::class, 'index'])->name('material-request-index');
		Route::get('/material-request/create', [App\Http\Controllers\backend\MaterialRequestController::class, 'create'])->name('material-request-create');
		Route::POST('/material-request/store', [App\Http\Controllers\backend\MaterialRequestController::class, 'store'])->name('material-request.store');
		Route::get('/material-request/delete/{id}', [App\Http\Controllers\backend\MaterialRequestController::class, 'destroy'])->name('delete');
		Route::get('/material-request/edit/{id}', [App\Http\Controllers\backend\MaterialRequestController::class, 'edit'])->name('material-request-edit');
		Route::PATCH('/material-request-update/{id}', [App\Http\Controllers\backend\MaterialRequestController::class, 'update'])->name('material-request-update');
		Route::get('/material-request/view/{id}', [App\Http\Controllers\backend\MaterialRequestController::class, 'view'])->name('material-request-view');

		Route::get('material-request/approve/{id}', [App\Http\Controllers\backend\MaterialRequestController::class, 'approve'])->name('material-request-approve');
		Route::get('material-request/reject/{id}', [App\Http\Controllers\backend\MaterialRequestController::class, 'reject'])->name('material-request-reject');
		Route::POST('/material-request-excel/download', [App\Http\Controllers\backend\MaterialRequestController::class, 'excelDownload'])->name('excelDownload');

		//Testing Request
		Route::any('testing-request/index', [App\Http\Controllers\backend\TestingRequestController::class, 'index'])->name('testing-request-index');
		Route::get('/testing-request/create', [App\Http\Controllers\backend\TestingRequestController::class, 'create'])->name('testing-request-create');
		Route::POST('/testing-request/store', [App\Http\Controllers\backend\TestingRequestController::class, 'store'])->name('testing-request.store');
		Route::get('/testing-request/delete/{id}', [App\Http\Controllers\backend\TestingRequestController::class, 'destroy'])->name('delete');
		Route::get('/testing-request/edit/{id}', [App\Http\Controllers\backend\TestingRequestController::class, 'edit'])->name('testing-request-edit');
		Route::PATCH('/testing-request-update/{id}', [App\Http\Controllers\backend\TestingRequestController::class, 'update'])->name('testing-request-update');
		Route::get('/testing-request/view/{id}', [App\Http\Controllers\backend\TestingRequestController::class, 'view'])->name('testing-request-view');
		Route::get('testing-request/approve/{id}', [App\Http\Controllers\backend\TestingRequestController::class, 'approve'])->name('testing-request-approve');
		Route::get('testing-request/reject/{id}', [App\Http\Controllers\backend\TestingRequestController::class, 'reject'])->name('testing-request-reject');
		Route::POST('/testing-request-excel/download', [App\Http\Controllers\backend\TestingRequestController::class, 'TestexcelDownload'])->name('TestexcelDownload');

		//Add Reason

		Route::get('reason/index', [App\Http\Controllers\backend\ReasonController::class, 'index'])->name('reason-index');
		Route::get('/reason/create', [App\Http\Controllers\backend\ReasonController::class, 'create'])->name('reason-create');
		Route::POST('/reason/store', [App\Http\Controllers\backend\ReasonController::class, 'store'])->name('reason.store');
		Route::get('/reason/delete/{id}', [App\Http\Controllers\backend\ReasonController::class, 'destroy'])->name('delete');
		Route::get('/reason/edit/{id}', [App\Http\Controllers\backend\ReasonController::class, 'edit'])->name('reason-edit');
		Route::PATCH('/reason-update/{id}', [App\Http\Controllers\backend\ReasonController::class, 'update'])->name('reason-update');

		//Defect

		Route::any('/defect/index', '\App\Http\Controllers\backend\DefectController@index')->name('index');
		Route::get('/defect/approve/{id}', '\App\Http\Controllers\backend\DefectController@approve')->name('approve');
		Route::get('/defect/reject/{id}', '\App\Http\Controllers\backend\DefectController@reject')->name('reject');
		Route::get('/defect/view/{id}', '\App\Http\Controllers\backend\DefectController@view')->name('view');
		Route::get('/defect-view/approve/{id}', '\App\Http\Controllers\backend\DefectController@viewApprove')->name('viewApprove');
		Route::get('/defect-view/reject/{id}', '\App\Http\Controllers\backend\DefectController@viewReject')->name('viewReject');
		Route::POST('/defect-request-excel/download', '\App\Http\Controllers\backend\DefectController@DefectexcelDownload')->name('DefectexcelDownload');


		// Category

		Route::get('categories-list', [App\Http\Controllers\backend\CategoryController::class, 'index'])->name('admin.categories-list');
		Route::get('categories-add', [App\Http\Controllers\backend\CategoryController::class, 'create'])->name('admin.categories-add');
		Route::POST('/categories-store', [App\Http\Controllers\backend\CategoryController::class, 'store'])->name('admin.categories-store');
		Route::get('/categorie/delete/{id}', '\App\Http\Controllers\backend\CategoryController@destroy')->name('delete');
		Route::get('categorie/edit/{id}', [App\Http\Controllers\backend\CategoryController::class, 'edit'])->name('categories-edit');
		Route::PATCH('categories-update/{id}', [App\Http\Controllers\backend\CategoryController::class, 'update'])->name('admin.categories-update');

		// Equipment
		Route::get('equipment-list', [App\Http\Controllers\backend\AddEquipmentController::class, 'index'])->name('admin.equipment-list');
		Route::get('equipment-add', [App\Http\Controllers\backend\AddEquipmentController::class, 'create'])->name('admin.equipment-add');
		Route::POST('/equipment-store', [App\Http\Controllers\backend\AddEquipmentController::class, 'store'])->name('admin.equipment-store');
		Route::get('/equipment/delete/{id}', '\App\Http\Controllers\backend\AddEquipmentController@destroy')->name('delete');
		Route::get('equipment/edit/{id}', [App\Http\Controllers\backend\AddEquipmentController::class, 'edit'])->name('equipment-edit');
		Route::PATCH('equipment-update/{id}', [App\Http\Controllers\backend\AddEquipmentController::class, 'update'])->name('admin.equipment-update');

		// Testing
		Route::get('testing-list', [App\Http\Controllers\backend\AddTestingController::class, 'index'])->name('admin.testing-list');
		Route::get('testing-add', [App\Http\Controllers\backend\AddTestingController::class, 'create'])->name('admin.testing-add');
		Route::POST('/testing-store', [App\Http\Controllers\backend\AddTestingController::class, 'store'])->name('admin.testing-store');
		Route::get('/testing/delete/{id}', '\App\Http\Controllers\backend\AddTestingController@destroy')->name('delete');
		Route::get('testing/edit/{id}', [App\Http\Controllers\backend\AddTestingController::class, 'edit'])->name('testing-edit');
		Route::PATCH('testing-update/{id}', [App\Http\Controllers\backend\AddTestingController::class, 'update'])->name('admin.testing-update');


		// Door style

		Route::get('door-style-list', [App\Http\Controllers\backend\DoorStyleController::class, 'index'])->name('admin.door-style-list');
		Route::get('door-style-add', [App\Http\Controllers\backend\DoorStyleController::class, 'create'])->name('admin.door-style-add');
		Route::POST('door-style-store', [App\Http\Controllers\backend\DoorStyleController::class, 'store'])->name('admin.door-style-store');
		Route::get('door-style/delete/{id}', '\App\Http\Controllers\backend\DoorStyleController@destroy')->name('delete');
		Route::get('door-style-edit/{id}', [App\Http\Controllers\backend\DoorStyleController::class, 'edit'])->name('door-style-edit');
		Route::PATCH('door-style-update/{id}', [App\Http\Controllers\backend\DoorStyleController::class, 'update'])->name('admin.door-style-update');


		// Order

		Route::get('order-list', [BackendController::class, 'orderList'])->name('admin.order-list');
		Route::get('order-details', [BackendController::class, 'orderDetails'])->name('admin.order-details');
		Route::get('order-invoices', [BackendController::class, 'orderInvoices'])->name('admin.order-invoices');


		// Customer

		Route::get('customers', [App\Http\Controllers\backend\CustomerManagementController::class, 'index'])->name('admin.customers');
		Route::get('/customer/delete/{id}', '\App\Http\Controllers\backend\CustomerManagementController@destroy')->name('delete');
		Route::get('customer-create', '\App\Http\Controllers\backend\CustomerManagementController@create')->name('create');
		Route::get('customer-detail', [BackendController::class, 'customerDetails'])->name('admin.customer-detail');
		Route::POST('/customer/store', [App\Http\Controllers\backend\CustomerManagementController::class, 'store'])->name('customer-store');
		Route::get('customer-edit/{id}', [App\Http\Controllers\backend\CustomerManagementController::class, 'edit'])->name('customer.edit');
		Route::PATCH('/customer-update/{id}', [App\Http\Controllers\backend\CustomerManagementController::class, 'update'])->name('customer.update');
		Route::get('customer-view/{id}', [App\Http\Controllers\backend\CustomerManagementController::class, 'view'])->name('customer.view');
		Route::get('customer-approval-view/{id}', [App\Http\Controllers\backend\CustomerManagementController::class, 'approval_view'])->name('customer.approval_view');
		Route::get('approvals', [App\Http\Controllers\backend\CustomerManagementController::class, 'approvals'])->name('approvals');
		Route::get('customer/approve/{id}', [App\Http\Controllers\backend\CustomerManagementController::class, 'approve'])->name('approve');
		Route::get('customer/reject/{id}', [App\Http\Controllers\backend\CustomerManagementController::class, 'reject'])->name('reject');
		Route::get('customer/active/{id}', [App\Http\Controllers\backend\CustomerManagementController::class, 'active'])->name('active');
		Route::get('customer/inactive/{id}', [App\Http\Controllers\backend\CustomerManagementController::class, 'inactive'])->name('inactive');


		//Employee Management ROUTES 08-01-2026

		Route::get('/employees', [App\Http\Controllers\backend\TeamMemberController::class, 'index'])->name('employees.index');
		Route::get(
			'/employees/status/{id}',
			[App\Http\Controllers\backend\TeamMemberController::class, 'changeStatus']
		)->name('team-members.status');
		Route::get('employees/create', '\App\Http\Controllers\backend\TeamMemberController@create')->name('create');
		Route::POST('/employees/store', [App\Http\Controllers\backend\TeamMemberController::class, 'store'])->name('employees.store');
		Route::get('/employees/edit/{id}', [App\Http\Controllers\backend\TeamMemberController::class, 'edit'])->name('employees.edit');
		Route::put('/employees/{id}', [App\Http\Controllers\backend\TeamMemberController::class, 'update_emp'])->name('employees.update');
		Route::get('/employees/delete/{id}', [App\Http\Controllers\backend\TeamMemberController::class, 'destroy'])
			->name('employees.delete');
		Route::get('employees/view/{id}', [App\Http\Controllers\backend\TeamMemberController::class, 'view'])->name('customer.view');
		//ProcessManagement

		Route::get('process-manage', [App\Http\Controllers\backend\ProcessManagementController::class, 'index'])->name('admin.process.manage');
		Route::get('process-manage/delete/{id}', [App\Http\Controllers\backend\ProcessManagementController::class, 'destroy'])->name('draft-product-delete');
		Route::get('process-manage-view/{id}', [App\Http\Controllers\backend\ProcessManagementController::class, 'view'])->name('admin.process-manage.view');
		Route::PATCH('/process-manage/update/{id}', [App\Http\Controllers\backend\ProcessManagementController::class, 'update'])->name('process-manage.update');


		//Report 

		Route::get('/report', [App\Http\Controllers\backend\ReportController::class, 'index'])->name('index');
		Route::any('/order-history', [App\Http\Controllers\backend\ReportController::class, 'orderHistory'])->name('orderHistory');
		Route::get('/sales-performance/{id}', [App\Http\Controllers\backend\ReportController::class, 'salesPerformance'])->name('salesPerformance');
		Route::get('/customer-list', [App\Http\Controllers\backend\ReportController::class, 'customerList'])->name('customerList');


		//Change Password 

		Route::get('/reset_password', [App\Http\Controllers\backend\ResetPasswordController::class, 'edit'])->name('edit');
		Route::PATCH('/update_password', [App\Http\Controllers\backend\ResetPasswordController::class, 'update'])->name('update');

		Route::get('/edit_profile', [App\Http\Controllers\backend\ResetPasswordController::class, 'edit_profile'])->name('edit');
		Route::PATCH('/update_profile', [App\Http\Controllers\backend\ResetPasswordController::class, 'update_profile'])->name('update');


		// Specbook Pdf

		Route::get('/specbook-pdf', '\App\Http\Controllers\backend\SpecbookPdfController@index')->name('index');
		Route::get('/specbook-pdf/create', '\App\Http\Controllers\backend\SpecbookPdfController@create')->name('create');
		Route::POST('/specbook-pdf/store', '\App\Http\Controllers\backend\SpecbookPdfController@store')->name('store');
		Route::get('/specbook-pdf/edit/{id}', '\App\Http\Controllers\backend\SpecbookPdfController@edit')->name('edit');
		Route::PATCH('/specbook-pdf/update/{id}', '\App\Http\Controllers\backend\SpecbookPdfController@update')->name('update');


		// Roles Permission

		Route::resource('roles', '\App\Http\Controllers\backend\RolesController');


		// Grouping Stakeholders

		Route::get('/grouping-stakeholders', '\App\Http\Controllers\backend\StakeholdersController@index')->name('index');
		Route::get('/grouping-stakeholders/edit/{id}', '\App\Http\Controllers\backend\StakeholdersController@edit')->name('edit');
		Route::PATCH('/grouping-stakeholders/update/{id}', '\App\Http\Controllers\backend\StakeholdersController@update')->name('update');


		// AttendanceController ROUTES	
		Route::middleware(['auth'])->group(function () {
			Route::get('attendance', '\App\Http\Controllers\backend\AttendanceController@index')->name('attendance.index');
			Route::post('attendance/clockn', '\App\Http\Controllers\backend\AttendanceController@clockIn')->name('attendance.clockin');
			Route::post('attendance/clockout', '\App\Http\Controllers\backend\AttendanceController@clockOut')->name('attendance.clockout');
			Route::get('attendance/list', '\App\Http\Controllers\backend\AttendanceController@adminIndex')->name('attendance.list');
			Route::get('/attendance/export/pdf', '\App\Http\Controllers\backend\AttendanceController@exportPdf')->name('attendance.export.pdf');
			Route::get('/attendance/export/excel', '\App\Http\Controllers\backend\AttendanceController@exportExcel')->name('attendance.export.excel');
			Route::get('attendance/employee/{user}/monthly', '\App\Http\Controllers\backend\AttendanceController@employeeMonthlyAttendance')->name('attendance.employee.monthly');
			Route::get('/attendance/daily', '\App\Http\Controllers\backend\AttendanceController@getDailyAttendance')
				->name('admin.attendance.daily');
		});
		Route::middleware(['auth'])->group(function () {
			Route::get('/attendance', '\App\Http\Controllers\backend\AttendanceController@getAttendanceCalendar')
				->name('attendance.calendar');
		});

		Route::middleware(['auth'])->group(function () {
			Route::get('/tasks/daily', [App\Http\Controllers\backend\TaskController::class, 'daily'])
				->name('tasks.daily');
		});


		Route::middleware(['auth'])->prefix('admin')->group(function () {

			// Task board view
			Route::get('/tasks/board', '\App\Http\Controllers\backend\TaskController@board')
				->name('tasks.board');

			// ── REMARKS — must be before {id} wildcard routes ──────
			Route::get('tasks/{taskId}/remarks', '\App\Http\Controllers\backend\TaskController@getRemarks')
				->name('tasks.remarks.get');
			Route::post('tasks/{taskId}/remarks', '\App\Http\Controllers\backend\TaskController@addRemark')
				->name('tasks.remarks.add');
			Route::delete('tasks/remarks/{remarkId}', '\App\Http\Controllers\backend\TaskController@deleteRemark')
				->name('tasks.remarks.delete');

			// Create new task
			Route::post('/tasks', '\App\Http\Controllers\backend\TaskController@store')
				->name('tasks.store');

			// Update task status (drag & drop)
			Route::post('/tasks/updateStatus', '\App\Http\Controllers\backend\TaskController@updateStatus')
				->name('tasks.updateStatus');

			// Update task details (edit form)
			Route::post('/tasks/update', '\App\Http\Controllers\backend\TaskController@update')
				->name('tasks.update');

			// Delete task
			Route::post('/tasks/delete', '\App\Http\Controllers\backend\TaskController@delete')
				->name('tasks.delete');

			Route::get('/tasks/recent', '\App\Http\Controllers\backend\TaskController@recentTasks')
				->name('tasks.recent');

			// ADMIN TASK MANAGEMENT (QUICK ADD/EDIT/DELETE FROM DASHBOARD)
			Route::post('tasks/quick-add', [App\Http\Controllers\backend\TaskController::class, 'quickAdd'])
				->name('tasks.quick-add');

			Route::post('tasks/{id}/status', [App\Http\Controllers\backend\TaskController::class, 'quickUpdateStatus'])
				->name('tasks.quick-update-status');

			Route::put('tasks/{id}', [App\Http\Controllers\backend\TaskController::class, 'quickUpdate'])
				->name('tasks.quick-update');

			Route::POST('tasks/{id}', [App\Http\Controllers\backend\TaskController::class, 'quickDelete'])
				->name('tasks.quick-delete');


			Route::post('/tasks/{task}/timer/start', '\App\Http\Controllers\backend\TaskController@startTimer')->name('tasks.timer.start');
			Route::post('/tasks/{task}/timer/stop', '\App\Http\Controllers\backend\TaskController@stopTimer')->name('tasks.timer.stop');
			Route::get('/tasks/{task}/timer/status', '\App\Http\Controllers\backend\TaskController@getTimerStatus')->name('tasks.timer.status');
			Route::get('/tasks/export/excel', [\App\Http\Controllers\backend\TaskController::class, 'exportExcel'])
				->name('tasks.export.excel');

			Route::get('/tasks/export/pdf', [\App\Http\Controllers\backend\TaskController::class, 'exportPdf'])
				->name('tasks.export.pdf');


				// NOTIFICATIONS Routes
				Route::get('/admin/notifications', '\App\Http\Controllers\backend\AdminNotificationController@index')
					->name('admin.notifications');
				Route::post('/admin/notifications/mark-read', '\App\Http\Controllers\backend\AdminNotificationController@markRead')
					->name('admin.notifications.read');
		});


		// Push Notification Routes
		Route::middleware(['auth'])->group(function () {
			Route::post('push/subscribe', [App\Http\Controllers\backend\PushNotificationController::class, 'subscribe'])->name('push.subscribe');
			Route::post('push/unsubscribe', [App\Http\Controllers\backend\PushNotificationController::class, 'unsubscribe'])->name('push.unsubscribe');
			Route::get('push/subscriptions', [App\Http\Controllers\backend\PushNotificationController::class, 'getSubscriptions'])->name('push.subscriptions');
			Route::post('push/test', [App\Http\Controllers\backend\PushNotificationController::class, 'test'])->name('push.test');
			Route::get('push/settings', [App\Http\Controllers\backend\PushNotificationController::class, 'settings'])->name('push.settings');
		});

		// Leave Management Routes
		Route::get('/leaves', [App\Http\Controllers\backend\LeaveController::class, 'index'])
			->name('admin.leaves.index');

		// Create leave
		Route::get('/leaves/create', [App\Http\Controllers\backend\LeaveController::class, 'create'])
			->name('admin.leaves.create');

		// Store leave
		Route::post('/leaves', [App\Http\Controllers\backend\LeaveController::class, 'store'])
			->name('admin.leaves.store');

		// View leave
		Route::get('/leaves/{id}', [App\Http\Controllers\backend\LeaveController::class, 'show'])
			->name('admin.leaves.show');

		// Edit leave
		Route::get('/leaves/{id}/edit', [App\Http\Controllers\backend\LeaveController::class, 'edit'])
			->name('admin.leaves.edit');

		// Update leave
		Route::put('/leaves/{id}', [App\Http\Controllers\backend\LeaveController::class, 'update'])
			->name('admin.leaves.update');
		// Delete leave
		Route::post('/leaves/{id}', [App\Http\Controllers\backend\LeaveController::class, 'destroy'])
			->name('admin.leaves.destroy');

		// Approve leave
		Route::post('/leaves/{id}/approve', [App\Http\Controllers\backend\LeaveController::class, 'approve'])
			->name('admin.leaves.approve');

		// Reject leave
		Route::post('/leaves/{id}/reject', [App\Http\Controllers\backend\LeaveController::class, 'reject'])
			->name('admin.leaves.reject');
		// cancelled_leave
		Route::post(
			'/leaves/{id}/cancelled_leave',
			[App\Http\Controllers\backend\LeaveController::class, 'cancelled_leave']
		)->name('admin.leaves.cancelled_leave');
	});

	// Project Management Routes
	Route::get('/projects', [App\Http\Controllers\backend\ProjectController::class, 'index'])
		->name('admin.projects.index');
	Route::get('/projects/create', [App\Http\Controllers\backend\ProjectController::class, 'create'])
		->name('admin.projects.create');
	Route::post('/projects', [App\Http\Controllers\backend\ProjectController::class, 'store'])
		->name('admin.projects.store');
	Route::get(
		'/projects/{project}/edit',
		[App\Http\Controllers\backend\ProjectController::class, 'edit']
	)->name('admin.projects.edit');
	Route::put(
		'/projects/{project}',
		[App\Http\Controllers\backend\ProjectController::class, 'update']
	)->name('admin.projects.update');
	Route::delete(
		'/projects/{project}',
		[App\Http\Controllers\backend\ProjectController::class, 'destroy']
	)->name('admin.projects.destroy');
	Route::get(
		'/projects/{project}',
		[App\Http\Controllers\backend\ProjectController::class, 'show']
	)->name('admin.projects.show');


});

Route::get('/logout', function () {
	Auth::logout();

	return redirect('/');
})->name('logout');
