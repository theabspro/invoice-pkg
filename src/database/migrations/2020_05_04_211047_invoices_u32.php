<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InvoicesU32 extends Migration {
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {

		Schema::table('invoices', function (Blueprint $table) {

			$table->unsignedInteger('invoice_of_id')->nullable()->after('id');
			$table->unsignedInteger('entity_id')->nullable()->after('invoice_of_id');
			$table->unsignedInteger("created_by_id")->nullable()->after('remarks');
			$table->unsignedInteger("updated_by_id")->nullable()->after('updated_by_id');
			$table->unsignedInteger("deleted_by_id")->nullable()->after('deleted_by_id');
			$table->softDeletes();

			$table->foreign("invoice_of_id")->references("id")->on("configs")->onDelete("CASCADE")->onUpdate("CASCADE");
			$table->foreign("created_by_id")->references("id")->on("users")->onDelete("SET NULL")->onUpdate("cascade");
			$table->foreign("updated_by_id")->references("id")->on("users")->onDelete("SET NULL")->onUpdate("cascade");
			$table->foreign("deleted_by_id")->references("id")->on("users")->onDelete("SET NULL")->onUpdate("cascade");

		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {
		Schema::table('invoices', function (Blueprint $table) {
		});
	}
}
