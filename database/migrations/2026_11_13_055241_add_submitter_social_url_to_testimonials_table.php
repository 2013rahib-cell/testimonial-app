<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Store the submitter's social profile / website link collected on the public form.
     */
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table): void {
            $table->string('submitter_social_url', 255)->nullable()->after('submitter_role');
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table): void {
            $table->dropColumn('submitter_social_url');
        });
    }
};
