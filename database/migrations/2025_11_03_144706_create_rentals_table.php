<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("rentals", function (Blueprint $table) {
            $table->id();
            $table->string("rental_code")->unique();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->unsignedBigInteger("transaction_id")->nullable();
            $table->date("start_date");
            $table->date("end_date");
            $table->integer("duration_days");
            $table->decimal("subtotal", 15, 2);
            $table->decimal("tax", 15, 2)->default(0);
            $table->decimal("total_price", 15, 2);
            $table->string("status")->default("pending"); // pending, confirmed, on_rent, completed, cancelled
            $table->string("payment_status")->default("unpaid"); // unpaid, paid, refunded
            $table->text("notes")->nullable();
            $table->timestamp("confirmed_at")->nullable();
            $table->timestamp("picked_up_at")->nullable();
            $table->timestamp("returned_at")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("rentals");
    }
};
