<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateResponseTranscriptionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('response_transcriptions', function(Blueprint $table)
		{
			$table->increments('id');
			$table->text('transcription', 65535);
			$table->integer('question_response_id')->index('response_transcriptions_question_response_id_foreign');
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
		Schema::drop('response_transcriptions');
	}

}
