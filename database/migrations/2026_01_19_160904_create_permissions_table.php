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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['tenant_admin', 'sales_agent', 'support_agent', 'supervisor', 'read_only']);
            $table->string('permission'); // e.g., 'leads.view', 'leads.manage', 'calls.make'
            $table->timestamps();

            $table->unique(['tenant_id', 'role', 'permission']);
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
