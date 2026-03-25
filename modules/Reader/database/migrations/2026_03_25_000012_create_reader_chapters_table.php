<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reader_chapters', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('book_id')->index();
            $table->unsignedInteger('number')->default(1);
            $table->string('title');
            $table->longText('content')->nullable();
            $table->string('status')->default('draft'); // draft|published
            $table->timestamps();

            $table->unique(['book_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reader_chapters');
    }
};

