<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateQuestionResponsesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('question_responses', function(Blueprint $table)
		{
			$table->increments('id');
			$table->text('response', 65535);
			$table->enum('type', array('voice','sms'));
			$table->string('recipient', 30);
			$table->string('session_sid');
			$table->integer('question_id')->index('question_responses_question_id_foreign');
			$table->string('question_num', 11);
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
		Schema::drop('question_responses');
	}

}
