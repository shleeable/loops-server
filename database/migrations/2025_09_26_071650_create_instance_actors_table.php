<?php

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
        Schema::create('instance_actors', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique()->index();
            $table->string('uri')->unique()->index();
            $table->string('key_id')->nullable()->unique();
            $table->text('public_key')->nullable();
            $table->string('shared_inbox')->nullable();
            $table->unsignedBigInteger('instance_id')->nullable()->index();
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamp('last_fetched_at')->nullable()->index();
            $table->foreign('instance_id')->references('id')->on('instances')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instance_actors');
    }
};
