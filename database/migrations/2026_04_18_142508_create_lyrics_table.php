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
		Schema::create('lyrics', function (Blueprint $table) {
			$table->id();
			$table->string('title');
			$table->string('artist');
			$table->string('album')->nullable();
			$table->integer('duration')->nullable()->default(0);
			$table->string('by')->comment("Lyric author")->nullable()->default('');
			$table->integer('offset')->nullable()->default(0);
			$table->longText('content');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('lyrics');
	}
};
