<?php

declare(strict_types=1);

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
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('space_id')->constrained()->cascadeOnDelete();
            $table->string('submitter_name');
            $table->string('submitter_email')->nullable();
            $table->string('submitter_avatar_path')->nullable();
            $table->string('submitter_company')->nullable();
            $table->string('submitter_role')->nullable();
            $table->text('content');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->string('type')->default('text');
            $table->string('video_url')->nullable();
            $table->string('status')->default('pending');
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->index('space_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
