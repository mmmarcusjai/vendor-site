<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectBriefsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_briefs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('project_name');
            $table->string('description');
            $table->string('category_id');
            $table->string('img_info');
            $table->string('start_date');
            $table->string('end_date');
            $table->integer('status')->default(0);
            $table->bigInteger('company_id')->nullable();
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
        Schema::dropIfExists('project_briefs');
    }
}
