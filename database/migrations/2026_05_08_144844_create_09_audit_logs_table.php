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

            $table->foreignId('actor_member_id')
                ->nullable()
                ->constrained('members')
                ->nullOnDelete();

            $table->string('action')
                ->index();

            $table->string('target_type')
                ->nullable()
                ->index();

            $table->unsignedBigInteger('target_id')
                ->nullable()
                ->index();

            $table->json('before_json')
                ->nullable();

            $table->json('after_json')
                ->nullable();

            $table->ipAddress('ip_address')
                ->nullable();

            $table->text('user_agent')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
