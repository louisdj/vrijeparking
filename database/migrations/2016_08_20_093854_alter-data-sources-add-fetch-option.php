<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterDataSourcesAddFetchOption extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data_sources', function (Blueprint $table) {
            $table->boolean('fetch_data');
            $table->string('fetch_class');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data_sources', function (Blueprint $table) {
            $table->dropColumn(['fetch_data', 'fetch_class']);
        });
    }
}
