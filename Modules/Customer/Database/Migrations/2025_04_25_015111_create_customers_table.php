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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_type');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('identity_document')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->integer('credit_score')->nullable();
            $table->string('contact_preferences')->nullable();
            $table->string('segment')->nullable();
            $table->timestamp('registration_date')->nullable();
            $table->boolean('active')->default(true);
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
        Schema::dropIfExists('customers');
    }
};
