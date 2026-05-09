<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_permissions', function (Blueprint $table) {
            $table->string('effect')
                ->default('allow')
                ->after('permission_id')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('member_permissions', function (Blueprint $table) {
            $table->dropColumn('effect');
        });
    }
};
