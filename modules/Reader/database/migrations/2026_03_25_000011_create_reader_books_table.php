<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reader_books', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('organization_id')->index();
            $table->unsignedBigInteger('author_id')->index();
            $table->string('title');
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->string('status')->default('draft'); // draft|submitted|approved|rejected
            $table->boolean('is_copyrighted')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reader_books');
    }
};

