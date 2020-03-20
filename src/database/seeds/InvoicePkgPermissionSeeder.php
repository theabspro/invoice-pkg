<?php
namespace Abs\InvoicePkg\Database\Seeds;

use App\Permission;
use Illuminate\Database\Seeder;

class InvoicePkgPermissionSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [
			//Invoices
			[
				'display_order' => 99,
				'parent' => null,
				'name' => 'invoices',
				'display_name' => 'Invoices',
			],
			[
				'display_order' => 1,
				'parent' => 'invoices',
				'name' => 'add-invoice',
				'display_name' => 'Add',
			],
			[
				'display_order' => 2,
				'parent' => 'invoices',
				'name' => 'edit-invoice',
				'display_name' => 'Edit',
			],
			[
				'display_order' => 3,
				'parent' => 'invoices',
				'name' => 'delete-invoice',
				'display_name' => 'Delete',
			],

		];
		Permission::createFromArrays($permissions);
	}
}