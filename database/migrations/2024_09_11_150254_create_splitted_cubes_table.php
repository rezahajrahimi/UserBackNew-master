<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('splitted_cubes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('factory_id')->unsigned();
            $table->bigInteger('cube_id')->unsigned();
            // add saw_id wich is the id of the saw that splitted the cube
            $table->bigInteger('saw_id')->unsigned()->nullable();
            $table->dateTime('splitted_at')->nullable();
            $table->dateTime('cutted_at')->nullable();

            $table->boolean('is_active')->default(true);
            $table->double('weight', 15, 2)->nullable()->default(0.00);
            $table->double('height', 15, 8)->nullable()->default(0.00);
            $table->double('length', 15, 8)->nullable()->default(0.00);
            $table->double('width', 15, 8)->nullable()->default(0.00);
            $table->timestamps();


            $table->foreign('factory_id')->references('id')->on('factories')->onDelete('cascade');
            $table->foreign('cube_id')->references('id')->on('cubes')->onDelete('cascade');
            $table->foreign('saw_id')->references('id')->on('cu_saws')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('splitted_cubes');
    }
};
