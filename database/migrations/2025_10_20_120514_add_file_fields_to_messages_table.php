<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('type')->default('text')->after('message');
            $table->string('file_path')->nullable()->after('type');
            $table->string('file_name')->nullable()->after('file_path');
            $table->string('file_size')->nullable()->after('file_name');
            $table->string('mime_type')->nullable()->after('file_size');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['type', 'file_path', 'file_name', 'file_size', 'mime_type']);
        });
    }
};