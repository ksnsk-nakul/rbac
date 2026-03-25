<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reader_book_files', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('book_id')->index();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('format')->nullable(); // pdf|doc|docx|epub|txt
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reader_book_files');
    }
};

