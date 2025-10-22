<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // For video/audio
            $table->integer('duration')->nullable()->after('mime_type'); // in seconds
            $table->string('thumbnail_path')->nullable()->after('duration'); // video thumbnails
            
            // For contacts
            $table->json('contact_data')->nullable()->after('thumbnail_path');
            
            // For polls
            $table->string('poll_question')->nullable()->after('contact_data');
            $table->json('poll_options')->nullable()->after('poll_question');
            $table->json('poll_votes')->nullable()->after('poll_options');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn([
                'duration', 
                'thumbnail_path', 
                'contact_data',
                'poll_question',
                'poll_options',
                'poll_votes'
            ]);
        });
    }
};