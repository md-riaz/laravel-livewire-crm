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
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('related_type')->nullable(); // 'lead', etc.
            $table->unsignedBigInteger('related_id')->nullable();
            $table->enum('direction', ['inbound', 'outbound']);
            $table->string('from_number');
            $table->string('to_number');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->string('pbx_call_id')->nullable();
            $table->string('recording_url')->nullable();
            $table->foreignId('disposition_id')->nullable()->constrained('call_dispositions')->onDelete('set null');
            $table->text('wrapup_notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id', 'started_at']);
            $table->index(['tenant_id', 'related_type', 'related_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
