<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_instances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('steam_app_id')->unique();
            $table->string('name');
            $table->unsignedInteger('memory_limit_mb')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('steam_app_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_instances');
    }
};
