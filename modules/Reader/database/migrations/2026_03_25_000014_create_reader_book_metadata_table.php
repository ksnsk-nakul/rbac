<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reader_book_metadata', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('book_id')->index();
            $table->string('key')->index();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['book_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reader_book_metadata');
    }
};

