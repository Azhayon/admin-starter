<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // nullOnDelete: deleting a user must NOT delete the history of what they did
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 50);                   // created, updated, deleted, roles_changed, ...
            $table->morphs('auditable');                   // auditable_type + auditable_id (polymorphic), indexed together
            $table->string('auditable_label')->nullable(); // "new@example.com": readable even after the row is deleted
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index(); // no updated_at: entries never change
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
