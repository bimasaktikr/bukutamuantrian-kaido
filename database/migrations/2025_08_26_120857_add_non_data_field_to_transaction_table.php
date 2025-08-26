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
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('category', ['data','nondata'])->default('data');
            // $table->enum('nondata_type', ['magang','meet'])->nullable();
            // $table->foreignId('employee_id')->nullable()->constrained('employees'); // or users table you use

            $table->foreignId('submethod_id')->nullable()->change();
            $table->foreignId('purpose_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['category']);
        });
    }
};
