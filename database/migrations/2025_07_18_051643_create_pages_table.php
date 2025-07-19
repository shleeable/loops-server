<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Page;
use App\Services\PageService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->enum('location', [
                'none',
                'side_menu_guest',
                'side_menu_user',
                'side_menu_all',
                'footer_guest',
                'footer_user',
                'footer_all'
            ])->default('none');
            $table->boolean('system_page')->default(false);
            $table->json('meta')->nullable();
            $table->index(['status', 'created_at']);
            $table->index('slug');
            $table->timestamps();
        });

        $pages = [
            [ 'title' => 'About', 'slug' => 'about', 'source' => 'about.md'],
            [ 'title' => 'Terms of Service', 'slug' => 'terms', 'source' => 'terms.md'],
            [ 'title' => 'Privacy', 'slug' => 'privacy', 'source' => 'privacy.md'],
            [ 'title' => 'Community Guidelines', 'slug' => 'community-guidelines', 'source' => 'community-guidelines.md'],
        ];

        foreach ($pages as $pageData) {
            $page = new Page;
            $page->title = $pageData['title'];
            $page->slug = $pageData['slug'];
            $page->content = PageService::getTemplateAndReplaceAppUrl($pageData['source']);
            $page->status = 'published';
            $page->system_page = true;
            $page->created_at = now();
            $page->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
