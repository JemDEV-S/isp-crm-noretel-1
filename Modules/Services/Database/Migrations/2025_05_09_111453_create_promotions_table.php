<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('discount', 10, 2);
            $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->json('conditions')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Tabla pivot para relación muchos a muchos entre planes y promociones
        Schema::create('plan_promotion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plan_promotion');
        Schema::dropIfExists('promotions');
    }
};
