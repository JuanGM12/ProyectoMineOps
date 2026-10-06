<?php

declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table): void {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('area');
            $table->string('location');
            $table->uuid('responsible_id');
            $table->string('priority', 20);
            $table->string('status', 20);
            $table->date('scheduled_date');
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_date']);
            $table->index(['responsible_id', 'created_at']);
            $table->index(['priority', 'created_at']);
            $table->index(['scheduled_date', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
