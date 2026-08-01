<?php

use App\Http\Middleware\{
    RedirectIfUserAuthenticated,
    CheckWebAuth,
    EnsureProfileIsCompleted,
    SetDefaultType,
    SaveLocation
};
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Website\{
    HomeController,
    AuthController,
    CategoryController,
    ProductController,
    CartController,
    CheckoutController,
    OrderController,
    SearchController,
    PageController,
    BranchController,
    LocationController,
    ProfileController,
    MenuController
};

Route::get('/compress-images', function () {
    ini_set('max_execution_time', 0); // Disable execution time limit
    try {
        \Illuminate\Support\Facades\Artisan::call('images:compress', ['--quality' => 75]);
        return '<pre>' . \Illuminate\Support\Facades\Artisan::output() . '</pre>';
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

Route::get('admin-panel', function () {
    return redirect(url('admin-panel/login'));
});

Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => [\App\Http\Middleware\SetWebsiteLocale::class, SetDefaultType::class, SaveLocation::class, 'localeSessionRedirect', 'localizationRedirect']], function () {
    Route::get('/', [HomeController::class, 'home'])->name('website.home');
    Route::get('/menu_type', [HomeController::class, 'menu_type'])->name('website.menu_type');
    Route::get('/save-location', function (Request $request) {
        return response()->json(['status' => 'ok']);
    });
    // Categories route
    Route::get('/categories/{slug?}', [CategoryController::class, 'categories'])->name('website.categories');
    Route::get('/branches/{slug?}', [BranchController::class, 'branches'])->name('website.branches');
    Route::get('/branches-by-city', [BranchController::class, 'getBranchesByCity'])->name('website.branches.by-city');
    Route::get('/scan-qr/{identifier}', [BranchController::class, 'scanQr'])->name('website.scan.qr');
    Route::post('/save-pickup-branch', [BranchController::class, 'savePickupBranch'])->name('website.branches.save-pickup');
    Route::get('/products/{productId}/prices', [ProductController::class, 'getPrices'])->name('website.products.prices');
    Route::post('/products/{productId}/update-price', [ProductController::class, 'updatePrice'])->name('website.products.update-price');
    Route::get('/products/{slug}', [ProductController::class, 'product'])->name('website.products');

    // Product Notes Routes
    Route::group(['middleware' => [CheckWebAuth::class]], function () {
        Route::post('/products/{productId}/notes', [ProductController::class, 'storeNote'])->name('website.products.notes.store');
        Route::get('/products/{productId}/notes', [ProductController::class, 'getNotes'])->name('website.products.notes.index');
    });
    Route::get('/menus', [MenuController::class, 'menus'])->name('website.menus');
    Route::get('/menus/{slug}', [MenuController::class, 'menu'])->name('website.menu');
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::post('/add', [CartController::class, 'add'])->name('add');
        Route::get('/', [CartController::class, 'newCart'])->name('index');
        Route::get('/new', [CartController::class, 'newCart'])->name('new');
        Route::post('/remove', [CartController::class, 'remove'])->name('remove');
        Route::post('/clear', [CartController::class, 'clear'])->name('clear');
        Route::post('/update-quantity', [CartController::class, 'updateQuantity'])->name('update.quantity');
        Route::post('/update-notes', [CartController::class, 'updateNotes'])->name('update.notes');
        Route::post('/apply-voucher', [CartController::class, 'applyVoucher'])->name('apply.voucher');
        Route::post('/remove-voucher', [CartController::class, 'removeVoucher'])->name('remove.voucher');
        Route::post('/set-order-type', [CartController::class, 'setOrderType'])->name('set.order.type');
    });

//    location save
    Route::post('/save-address', [LocationController::class, 'saveLocation'])->name('website.save.location');
    // Specific routes must come before parameterized routes
    Route::get('/locations/states', [LocationController::class, 'getStates'])->name('website.locations.states');
    Route::get('/locations/cities', [LocationController::class, 'getCities'])->name('website.locations.cities');
    Route::get('/locations/delivery-time', [LocationController::class, 'getDeliveryTime'])->name('website.locations.delivery-time');
    Route::get('/locations/{parentId}', [LocationController::class, 'getChildren']);
//    end location save

    Route::get('/search', [SearchController::class, 'search'])
        ->name('website.search');

    Route::get('change-currency/{key}', function ($currency) {
        session(['currency' => $currency]);
        return redirect()->back();
    })->name('change-currency');

    Route::get('/page', [PageController::class, 'page'])->name('page');

    Route::group(['middleware' => [CheckWebAuth::class, EnsureProfileIsCompleted::class]], function () {
//        Route::get('contact-us', [PageController::class, 'contact'])->name('website.contact.show');
//        Route::post('contact-us', [PageController::class, 'contact_post'])->name('website.contact.send');
        Route::prefix('profile')->name('profile.')->group(function () {
            // Keep old AJAX route for backward compatibility (optional)
            Route::get('/tab/{tab}', [ProfileController::class, 'getTabContent'])
                ->name('tab.content');

            // Main profile page (redirects to account info)
            Route::get('/', [ProfileController::class, 'profileData'])
                ->name('index');

            // Separate routes for each tab
            Route::get('/account-info', [ProfileController::class, 'profileData'])
                ->name('account-info');
            Route::get('/orders', [ProfileController::class, 'orders'])
                ->name('orders');
            Route::get('/wallet', [ProfileController::class, 'wallet'])
                ->name('wallet');
            Route::get('/wallet/add-money', [ProfileController::class, 'addMoneyPage'])
                ->name('wallet.add-money');
            Route::get('/track-orders', [ProfileController::class, 'trackOrders'])
                ->name('track-orders');
            Route::get('/wishlist', [ProfileController::class, 'wishlist'])
                ->name('wishlist');
            Route::get('/addresses', [ProfileController::class, 'addresses'])
                ->name('addresses');

            // API routes
            Route::post('/update', [ProfileController::class, 'updateProfile'])->name('update');
            Route::post('/send-phone-otp', [ProfileController::class, 'sendPhoneChangeOtp'])->name('send.phone.otp');
            Route::post('/add-favorite', [ProfileController::class, 'addFavorite'])->name('add.favorite');
            Route::post('/remove-favorite', [ProfileController::class, 'removeFavorite'])->name('remove.favorite');
            Route::post('/add-money', [ProfileController::class, 'addMoney'])->name('add.money');
            Route::post('/convert-points', [ProfileController::class, 'convertPointsToWallet'])->name('convert.points');
        });

        // Address routes
        Route::prefix('addresses')->name('addresses.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Website\AddressController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Website\AddressController::class, 'store'])->name('store');
            Route::put('/{id}', [\App\Http\Controllers\Website\AddressController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\Website\AddressController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/set-main', [\App\Http\Controllers\Website\AddressController::class, 'setMain'])->name('set.main');
        });

        // Wallet top-up routes
        Route::prefix('wallet')->name('website.wallet.')->group(function () {
            Route::get('/callback', [ProfileController::class, 'walletCallback'])->name('callback');
            Route::get('/success', [ProfileController::class, 'walletSuccess'])->name('success');
            Route::get('/failed', [ProfileController::class, 'walletFailed'])->name('failed');
        });

        Route::post('subscribe', [PageController::class, 'subscribe'])->name('website.subscribe.send');
    });

    // Checkout routes - available to both authenticated and guest users
    Route::prefix('checkout')->name('checkout')->group(function () {
        Route::get('/', [CheckoutController::class, 'index']);
        Route::post('/store', [OrderController::class, 'store'])->name('.store');
        Route::post('/check-phone', [CheckoutController::class, 'checkPhone'])->name('.check-phone');
        Route::post('/send-otp', [CheckoutController::class, 'sendOtp'])->name('.send-otp');
        Route::post('/verify-otp', [CheckoutController::class, 'verifyOtp'])->name('.verify-otp');
        Route::post('/register-guest', [CheckoutController::class, 'registerGuest'])->name('.register-guest');
    });

    // Order routes
    Route::prefix('order')->name('website.orders.')->group(function () {
        Route::get('/payment/{order}', [OrderController::class, 'payment'])->name('payment');
        Route::post('/payment/{order}/process', [OrderController::class, 'payment_post'])->name('payment.process');
        Route::get('/payment/{order}/callback', [OrderController::class, 'paymentCallback'])->name('payment.callback');
        Route::get('/payment/{order}/failed', [OrderController::class, 'paymentFailed'])->name('payment.failed');
        Route::get('/success/{order}', [OrderController::class, 'success'])->name('success');
    });
    //    auth routes
    Route::group(['middleware' => [RedirectIfUserAuthenticated::class]], function () {
        Route::get('/login', [AuthController::class, 'login'])->name('website.login');
        Route::post('/login', [AuthController::class, 'login_post'])->name('website.login.post');

        Route::get('/register', [AuthController::class, 'register'])->name('website.register');
        Route::post('/register', [AuthController::class, 'register_post'])->name('website.register.post');

        // Google OAuth routes
        Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('website.google.login');
        Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('website.google.callback');

        //verify
        Route::get('/verify-otp/{user_email}', [AuthController::class, 'verify_form'])
            ->name('website.otp.verify');
        Route::get('/verify-otp-phone', [AuthController::class, 'verify_phone_form'])
            ->name('website.otp.verify.phone');
        Route::post('/verify-otp-phone', [AuthController::class, 'verifyOtpPhone'])
            ->name('website.otp.verify.phone.post');
        Route::post('/otp/resend/{user_email}', [AuthController::class, 'resend'])
            ->name('website.otp.resend');
        Route::post('/otp/resend-phone', [AuthController::class, 'resendPhone'])
            ->name('website.otp.resend.phone');
//    forget pass
        Route::get('/forget_password', [AuthController::class, 'forget_password'])
            ->name('website.forget_pass');
        Route::post('/forget_password', [AuthController::class, 'forget_password_post'])
            ->name('website.forget_pass.post');
        Route::get('/forget_password/otp/verify', [AuthController::class, 'forgetPasswordOtpVerify'])
            ->name('website.forget_pass.otp.verify');
        Route::post('/forget_password/otp/verify', [AuthController::class, 'forgetPasswordOtpVerifyPost'])
            ->name('website.forget_pass.otp.verify.post');
        Route::get('reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
        Route::post('reset-password', [AuthController::class, 'reset'])->name('password.update');

        Route::post('/verifyOtp/{user_email}', [AuthController::class, 'verifyOtp'])->name('website.otp.verify.post');
    });

    Route::get('/logout', [AuthController::class, 'logout'])->name('website.logout');

    // Email Verification routes for website users
    Route::middleware(['auth:web'])->group(function () {
        Route::get('/verify-email', [\App\Http\Controllers\Website\EmailVerificationPromptController::class, '__invoke'])
            ->name('website.verification.notice');
        
        Route::get('/verify-email/{id}/{hash}', [\App\Http\Controllers\Website\EmailVerificationController::class, '__invoke'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('website.verification.verify');
        
        Route::post('/email/verification-notification', [\App\Http\Controllers\Website\EmailVerificationNotificationController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('website.verification.send');
    });
    //  ! auth routes
//
    Route::get('/{slug}', [PageController::class, 'show'])
        ->name('website.pages.show');

});


Route::post('/location-modal-closed', function () {
    session(['location_modal_closed' => true]);
    return response()->json(['status' => 'ok']);
})->name('location.modal.closed');
