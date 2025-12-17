<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public landing page
Route::get("/", [LandingController::class, "index"])->name("landing");

// Guest routes
Route::middleware("guest")->group(function () {
    Route::get("/login", [AuthController::class, "showLoginForm"])->name(
        "login",
    );
    Route::post("/login", [AuthController::class, "login"]);
    Route::get("/register", [AuthController::class, "showRegisterForm"])->name(
        "register",
    );
    Route::post("/register", [AuthController::class, "register"]);
});

// Authenticated routes
Route::middleware("auth")->group(function () {
    Route::post("/logout", [AuthController::class, "logout"])->name("logout");

    // Home - accessible by all authenticated users
    Route::get("/home", function () {
        $user = auth()->user();

        // Redirect to appropriate dashboard based on permission
        if ($user->hasPermissionTo("view-dashboard-privilege")) {
            return redirect()->route("dashboard");
        }

        return app(\App\Http\Controllers\DashboardController::class)->index();
    })->name("home");

    // Management Dashboard - only for users with privilege
    Route::get("/dashboard", [
        \App\Http\Controllers\PrivilegedDashboardController::class,
        "index",
    ])
        ->middleware("dashboard.permission")
        ->name("dashboard");

    // User Management Routes
    Route::resource("users", UserController::class)->except(["show", "create"]);

    // Role Management Routes
    Route::resource("roles", RoleController::class)->except(["show", "create"]);

    // Permission Routes (View Only)
    Route::get("/permissions", [PermissionController::class, "index"])->name(
        "permissions.index",
    );

    // Category Management Routes
    Route::resource("categories", CategoryController::class)->except([
        "show",
        "create",
    ]);

    // Item Management Routes
    Route::resource("items", ItemController::class)->except(["show", "create"]);

    // Profile Management Routes
    Route::prefix("profile")
        ->name("profile.")
        ->group(function () {
            Route::get("/", [ProfileController::class, "index"])->name("index");
            Route::get("/show", [ProfileController::class, "show"])->name(
                "show",
            );
            Route::put("/update", [ProfileController::class, "update"])->name(
                "update",
            );
        });

    // Catalog Routes (for standard users)
    Route::prefix("catalog")
        ->name("catalog.")
        ->group(function () {
            Route::get("/", [CatalogController::class, "index"])->name("index");
            Route::get("/{item}", [CatalogController::class, "show"])->name(
                "show",
            );
        });

    // Payment Routes
    Route::prefix("payment")
        ->name("payment.")
        ->group(function () {
            Route::get("/", [PaymentController::class, "paymentPage"])->name(
                "page",
            );
            Route::post("/checkout", [
                PaymentController::class,
                "checkout",
            ])->name("checkout");
            Route::get("/history", [PaymentController::class, "history"])->name(
                "history",
            );
            Route::get("/transaction/{orderId}", [
                PaymentController::class,
                "show",
            ])->name("show");
            Route::get("/transaction/{orderId}/status", [
                PaymentController::class,
                "checkStatus",
            ])->name("status");
            Route::post("/transaction/{orderId}/cancel", [
                PaymentController::class,
                "cancel",
            ])->name("cancel");
        });

    // Cart Routes
    Route::prefix("cart")
        ->name("cart.")
        ->group(function () {
            Route::get("/", [CartController::class, "indexView"])->name(
                "index",
            );
            Route::post("/", [CartController::class, "store"])->name("store");
            Route::put("/{cart}", [CartController::class, "update"])->name(
                "update",
            );
            Route::delete("/{cart}", [CartController::class, "destroy"])->name(
                "destroy",
            );
            Route::delete("/", [CartController::class, "clear"])->name("clear");
        });

    // Rental Checkout Routes (for regular users)
    Route::prefix("checkout")
        ->name("checkout.")
        ->group(function () {
            Route::post("/", [CheckoutController::class, "checkout"])->name(
                "process",
            );
            Route::get("/history", [
                CheckoutController::class,
                "historyView",
            ])->name("history");
            Route::get("/rental/{rental}", [
                CheckoutController::class,
                "show",
            ])->name("show");
            Route::post("/rental/{rental}/cancel", [
                CheckoutController::class,
                "cancel",
            ])->name("cancel");
            Route::post("/rental/{rental}/regenerate-payment", [
                CheckoutController::class,
                "regeneratePayment",
            ])->name("regenerate");
            Route::post("/rental/{rental}/check-status", [
                CheckoutController::class,
                "checkPaymentStatus",
            ])->name("check.status");
            Route::get("/rental/{rental}/payment-status", [
                CheckoutController::class,
                "paymentStatus",
            ])->name("payment.status");
        });

    // Transaction Management Routes (for admins/owners) - Gabungan Rental & Payment
    Route::prefix("transactions")
        ->name("transactions.")
        ->middleware("permission:manage-all-rentals")
        ->group(function () {
            Route::get("/", [
                \App\Http\Controllers\TransactionManagementController::class,
                "index",
            ])->name("index");
            Route::post("/{rental}/update-status", [
                \App\Http\Controllers\TransactionManagementController::class,
                "updateStatus",
            ])->name("update.status");
            Route::get("/statistics", [
                \App\Http\Controllers\TransactionManagementController::class,
                "statistics",
            ])->name("statistics");
        });
});

// Webhook route (no authentication, but with signature verification)
Route::post("/payment/webhook", [PaymentController::class, "webhook"])
    ->middleware("verify.midtrans")
    ->name("payment.webhook");
