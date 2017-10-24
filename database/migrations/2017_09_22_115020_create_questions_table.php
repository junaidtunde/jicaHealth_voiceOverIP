<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateQuestionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('questions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->float('question_id', 10, 0);
			$table->string('body');
			$table->enum('kind', array('plain','yes-no','numeric'));
			$table->string('lang', 30);
			$table->integer('no_of_parts');
			$table->integer('pregnancy_stage');
			$table->integer('delivery_stage');
			$table->integer('survey_id')->index('questions_survey_id_foreign');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('questions');
	}

}
