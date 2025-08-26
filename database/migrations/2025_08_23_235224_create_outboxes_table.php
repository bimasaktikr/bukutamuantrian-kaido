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
        Schema::create('outboxes', function (Blueprint $table) {
            $table->id();
            $table->string('to');                 // phone number
            $table->text('message');              // payload text
            // polymorphic link to what this message refers to (Queue, Transaction, etc.)
            $table->nullableMorphs('related');    // related_type, related_id
            $table->string('status')->default('pending'); // pending|sent|failed
            $table->integer('response_code')->nullable();
            $table->longText('response_body')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outboxes');
    }
};
