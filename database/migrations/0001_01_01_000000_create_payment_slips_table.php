<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_slips', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('name');
            $table->bigInteger('governmentId');
            $table->decimal("debtAmount");
            $table->date("debtDueDate");
            $table->string('debtId');
            $table->enum('status', ["pending", "sent", "error"])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_slip');
    }
};
