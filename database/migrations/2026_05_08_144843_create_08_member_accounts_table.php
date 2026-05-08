<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_accounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('member_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('provider')
                ->index();

            $table->string('account_identifier')
                ->index();

            $table->boolean('is_primary')
                ->default(false);

            $table->string('status')
                ->default('active')
                ->index();

            $table->timestamp('verified_at')
                ->nullable();

            $table->timestamps();

            $table->unique([
                'provider',
                'account_identifier'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_accounts');
    }
};
