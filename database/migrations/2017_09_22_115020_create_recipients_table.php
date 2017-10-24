<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRecipientsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('recipients', function(Blueprint $table)
		{
			$table->integer('recipient_id', true);
			$table->string('name', 50);
			$table->string('phone_number', 30);
			$table->string('preferred_lang', 30);
			$table->string('preferred_time', 30);
			$table->enum('status', array('pregnant','delivered','undefined','completed'));
			$table->string('delivery_month', 20);
			$table->integer('delivery_day');
			$table->integer('babys_age');
			$table->integer('delivery_stage');
			$table->integer('pregnancy_stage');
			$table->boolean('isready');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('recipients');
	}

}
