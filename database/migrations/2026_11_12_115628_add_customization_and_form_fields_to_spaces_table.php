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
        Schema::table('spaces', function (Blueprint $table) {
            $table->string('title')->nullable()->after('name');
            $table->text('subtitle')->nullable()->after('title');
            $table->text('ask')->nullable()->after('subtitle');
            $table->string('theme')->default('light')->after('custom_cta');
            $table->boolean('collect_company')->default(true)->after('theme');
            $table->boolean('require_company')->default(false)->after('collect_company');
            $table->boolean('collect_social_links')->default(true)->after('require_company');
            $table->boolean('require_social_links')->default(false)->after('collect_social_links');
            $table->boolean('collect_avatar')->default(true)->after('require_social_links');
            $table->boolean('require_avatar')->default(false)->after('collect_avatar');
            $table->boolean('enable_ratings')->default(true)->after('require_avatar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'subtitle',
                'ask',
                'theme',
                'collect_company',
                'require_company',
                'collect_social_links',
                'require_social_links',
                'collect_avatar',
                'require_avatar',
                'enable_ratings',
            ]);
        });
    }
};
