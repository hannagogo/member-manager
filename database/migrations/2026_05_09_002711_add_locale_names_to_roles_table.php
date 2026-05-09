<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('name_ja')
                ->nullable()
                ->after('short_name');

            $table->string('name_en')
                ->nullable()
                ->after('name_ja');

            $table->dropColumn('name');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('name')
                ->nullable();

            $table->dropColumn([
                'name_ja',
                'name_en',
            ]);
        });
    }
};
