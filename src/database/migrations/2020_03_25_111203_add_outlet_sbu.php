<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOutletSbu extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {
		Schema::table('invoices', function (Blueprint $table) {
			$table->unsignedInteger('outlet_id')->after('invoice_date')->nullable();
			$table->integer('sbu_id')->after('outlet_id')->nullable();

			$table->foreign('outlet_id')->references('id')->on('outlets')->onDelete('SET NULL')->onUpdate('cascade');
			$table->foreign('sbu_id')->references('id')->on('sbus')->onDelete('SET NULL')->onUpdate('cascade');

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('invoices', function (Blueprint $table) {

			$table->dropForeign('invoices_outlet_id_foreign');
			$table->dropForeign('invoices_sbu_id_foreign');

			$table->dropColumn('outlet_id');
			$table->dropColumn('sbu_id');
		});
	}
}
