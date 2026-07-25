<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\IsAdminMiddleware;
use App\Http\Controllers\Admin\{LoginController as AdminLoginController,
    SettingController,
    CategoryController,
    LocationController,
    CountryController,
    CurrencyController,
    BranchController,
    ProductDefinitionController,
    AddonGroupController,
    AddonController,
    ProductController,
    BoxController,
    UserController,
    ProfileController,
    RoleController,
    AdminController,
    AuditController,
    PageController,
    WalletController,
    CouponController,
    SliderController,
    BannerController,
    OrderController,
    MenuController,
    OfferController,
    DashboardController,
    ReportController,
    NotificationController
};

Route::group(['prefix' => LaravelLocalization::setLocale() . '/admin', 'middleware' => [\App\Http\Middleware\SetAdminLocale::class, IsAdminMiddleware::class, 'localeSessionRedirect', 'localizationRedirect']], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin-panel');
    Route::post('/sms/balance/refresh', [DashboardController::class, 'refreshSmsBalance'])->name('admin.sms.balance.refresh');
    Route::post('/firebase/test', [DashboardController::class, 'testFirebaseNotification'])->name('admin.firebase.test');
    Route::post('/change-theme', [SettingController::class, 'changeTheme'])->name('theme.change');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::post('categories/{id}/toggle-active', [CategoryController::class, 'toggleActive'])->name('categories.toggle-active');
    Route::get('categories/{id}/products', [CategoryController::class, 'getProducts'])->name('categories.products');
    
    Route::get('/branch-menu-copier', [\App\Http\Controllers\Admin\BranchMenuCopierController::class, 'index'])->name('admin.branch-menu-copier.index');
    Route::post('/branch-menu-copier/copy', [\App\Http\Controllers\Admin\BranchMenuCopierController::class, 'copy'])->name('admin.branch-menu-copier.copy');

    Route::resource('menus', MenuController::class)->except('show');
    Route::get('menus/categories', [MenuController::class, 'getCategories'])->name('menus.get-categories');
    Route::get('menus/products', [MenuController::class, 'getProducts'])->name('menus.products');
    Route::get('menus/{id}/categories', [MenuController::class, 'categories'])->name('menus.categories');
    Route::post('menus/{id}/products/update-order', [MenuController::class, 'updateProductOrder'])->name('menus.products.update-order');
    Route::post('menus/{id}/toggle-active', [MenuController::class, 'toggleActive'])->name('menus.toggle-active');
    Route::resource('sliders', SliderController::class)->except('show');
    Route::post('sliders/{id}/toggle-active', [SliderController::class, 'toggleActive'])->name('sliders.toggle-active');
    Route::resource('banners', BannerController::class)->except('show');
    Route::post('banners/{id}/toggle-active', [BannerController::class, 'toggleActive'])->name('banners.toggle-active');
    Route::resource('offers', OfferController::class)->except('show');
    Route::post('offers/{id}/toggle-active', [OfferController::class, 'toggleActive'])->name('offers.toggle-active');

    Route::resource('countries', LocationController::class)->except('show');
    Route::resource('states', LocationController::class)->except('show');
    Route::resource('cities', LocationController::class)->except('show');
    Route::get('/locations/parents', [LocationController::class, 'getParents'])->name('locations.parents');
    Route::post('/states/{id}/unify-cities', [LocationController::class, 'unifyCities'])->name('states.unify-cities');

    Route::resource('currencies', CurrencyController::class)->except('show');

    Route::resource('branches', BranchController::class)->except('show');
    Route::get('branches/{id}/qr', [BranchController::class, 'showQr'])->name('branches.qr');
    Route::post('branches/check-city-availability', [BranchController::class, 'checkCityAvailability'])->name('branches.check-city-availability');
    Route::get('branches/{id}/products', [BranchController::class, 'products'])->name('branches.products');
    Route::post('branches/{id}/products/{productId}/toggle-status', [BranchController::class, 'toggleProductStatus'])->name('branches.products.toggle-status');
//    products
    Route::resource('products', ProductController::class)->except('show');
    Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
    Route::post('products/{product}/toggle-active', [ProductController::class, 'toggleActive'])->name('products.toggle-active');

    Route::resource('meals', ProductController::class)->except('show')->parameters([
        'meals' => 'product'
    ]);
    Route::get('meals/categories/order', [ProductController::class, 'mealsCategoriesOrder'])->name('meals.categories.order');
    Route::post('meals/categories/update-order', [ProductController::class, 'updateCategoriesOrder'])->name('meals.categories.update-order');
    Route::post('meals/products/update-order', [ProductController::class, 'updateMealsOrder'])->name('meals.products.update-order');
    Route::get('products/{id}/addons', [ProductController::class, 'getAddons']);
    Route::get('products/{id}/addons_edit', [ProductController::class, 'getAddonsEdit']);
    Route::get('products/{id}/prices', [ProductController::class, 'prices'])->name('products.prices');
    Route::get('products/{id}/notes', [ProductController::class, 'getProductNotes'])->name('products.notes');
    Route::get('product-notes', [\App\Http\Controllers\Admin\ProductNoteController::class, 'index'])->name('product-notes.index');
    Route::get('product-notes/{id}', [\App\Http\Controllers\Admin\ProductNoteController::class, 'show'])->name('product-notes.show');
    Route::delete('product-notes/{id}', [\App\Http\Controllers\Admin\ProductNoteController::class, 'destroy'])->name('product-notes.destroy');
    Route::resource('product_definitions', ProductDefinitionController::class)->except('show');
    Route::post('product_definitions/{id}/toggle-active', [ProductDefinitionController::class, 'toggleActive'])->name('product_definitions.toggle-active');
    Route::resource('addon_groups', AddonGroupController::class)->except('show');
    Route::resource('addons', AddonController::class)->except('show');
    Route::post('addons/{id}/toggle-active', [AddonController::class, 'toggleActive'])->name('addons.toggle-active');
    Route::post('addons/import', [AddonController::class, 'import'])->name('addons.import');
    Route::resource('boxes', BoxController::class)->except('show');
    Route::post('boxes/{id}/toggle-active', [BoxController::class, 'toggleActive'])->name('boxes.toggle-active');

    Route::resource('users', UserController::class);
    Route::post('users/{id}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::post('users/{id}/convert-points', [UserController::class, 'convertPointsToWallet'])->name('users.convert-points');
    Route::get('users/{id}/addresses', [UserController::class, 'addresses'])->name('users.addresses');
    Route::get('users/{id}/orders', [UserController::class, 'orders'])->name('users.orders');
    Route::post('users/{id}/addresses', [UserController::class, 'storeAddress'])->name('users.addresses.store');
    Route::get('users/{user}/addresses/{address}', [UserController::class, 'showAddress'])->name('users.addresses.show');
    Route::put('users/{user}/addresses/{address}', [UserController::class, 'updateAddress'])->name('users.addresses.update');
    Route::delete('users/{user}/addresses/{address}', [UserController::class, 'destroyAddress'])->name('users.addresses.destroy');
    Route::post('users/{user}/addresses/{address}/set-main', [UserController::class, 'setMainAddress'])->name('users.addresses.set-main');

    Route::get('profile', [ProfileController::class , 'profile_page'])->name('profile.get');
    Route::post('profile', [ProfileController::class , 'profile_page_post'])->name('profile.post');

    Route::get('settings', [SettingController::class , 'get_settings'])->name('settings.get');
    Route::post('settings', [SettingController::class , 'update'])->name('settings.post');
    Route::get('home-page-settings', [SettingController::class , 'getHomePageSettings'])->name('home-page-settings.get');
    Route::post('home-page-settings', [SettingController::class , 'updateHomePageSettings'])->name('home-page-settings.post');

    Route::resource('pages', PageController::class);

    Route::resource('roles', RoleController::class);

    Route::resource('admins', AdminController::class);

    Route::resource('coupons', CouponController::class);

    Route::resource('orders', OrderController::class)->only('index', 'show', 'edit', 'update');
    Route::post('orders/{id}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('orders/{id}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');

    Route::resource('wallets', WalletController::class)->only('index', 'create', 'store');

    Route::group(['prefix' => 'audits', 'as' => 'audits.'], function () {
        Route::get('/', [AuditController::class, 'index'])->name('index');
        Route::get('/{audit}', [AuditController::class, 'show'])->name('show');
    });

    // Reports Routes
    Route::group(['prefix' => 'reports', 'as' => 'reports.'], function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/best-selling', [ReportController::class, 'bestSellingProducts'])->name('best-selling');
        Route::get('/best-branches', [ReportController::class, 'bestBranches'])->name('best-branches');
        Route::get('/payment-methods', [ReportController::class, 'paymentMethods'])->name('payment-methods');
        
        // Export Routes
        Route::get('/export/excel/{type}', [ReportController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/csv/{type}', [ReportController::class, 'exportCsv'])->name('export.csv');
        Route::get('/export/pdf/{type}', [ReportController::class, 'exportPdf'])->name('export.pdf');
    });

    // Notifications Routes
    Route::group(['prefix' => 'notifications', 'as' => 'notifications.'], function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::get('/latest', [NotificationController::class, 'getLatest'])->name('latest');
        Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
    });

    Route::group(['prefix' => 'artisan', 'as' => 'admin.artisan.'], function () {
        Route::get('request', [App\Http\Controllers\Admin\ArtisanWebController::class, 'showRequest'])->name('request');
        Route::post('send-code', [App\Http\Controllers\Admin\ArtisanWebController::class, 'sendCode'])->name('send-code');
        Route::get('console', [App\Http\Controllers\Admin\ArtisanWebController::class, 'showConsole'])->name('console');
        Route::post('execute', [App\Http\Controllers\Admin\ArtisanWebController::class, 'execute'])->name('execute');
        Route::get('logs', [App\Http\Controllers\Admin\ArtisanWebController::class, 'listLogs'])->name('logs.list');
        Route::get('logs/view/{filename}', [App\Http\Controllers\Admin\ArtisanWebController::class, 'viewLog'])->name('logs.view');
        Route::delete('logs/delete/{filename}', [App\Http\Controllers\Admin\ArtisanWebController::class, 'deleteLog'])->name('logs.delete');
    });
});

Route::group(['prefix' => 'admin'], function () {
    Route::get('login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('login', [AdminLoginController::class, 'login']);
    Route::post('logout', [AdminLoginController::class, 'logout'])->name('admin.logout');
});
