<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_roles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('member_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('role_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('organization_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('status')
                ->default('active')
                ->index();

            $table->timestamp('granted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();

            $table->foreignId('granted_by')
                ->nullable()
                ->constrained('members')
                ->nullOnDelete();

            $table->foreignId('revoked_by')
                ->nullable()
                ->constrained('members')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'member_id',
                'role_id',
                'organization_id',
                'status'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_roles');
    }
};
