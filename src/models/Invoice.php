<?php

namespace Abs\InvoicePkg;

use Abs\HelperPkg\Traits\SeederTrait;
use App\Company;
use App\Config;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model {
	use SeederTrait;
	// use SoftDeletes;
	protected $table = 'invoices';
	public $timestamps = true;
	protected $fillable = [
		'name',
		'description',
		'company_id',
		'invoice_number',
	];

	public function outlet() {
		return $this->belongsTo('App\Outlet', 'outlet_id');
	}

	//ISSUE : naming
	public function business() {
		return $this->belongsTo('Abs\BusinessPkg\Sbu', 'sbu_id');
	}

	public function sbu() {
		return $this->belongsTo('Abs\BusinessPkg\Sbu', 'sbu_id');
	}

	public static function createFromObject($record_data) {

		$errors = [];
		$company = Company::where('code', $record_data->company)->first();
		if (!$company) {
			dump('Invalid Company : ' . $record_data->company);
			return;
		}

		$admin = $company->admin();
		if (!$admin) {
			dump('Default Admin user not found');
			return;
		}

		$type = Config::where('name', $record_data->type)->where('config_type_id', 89)->first();
		if (!$type) {
			$errors[] = 'Invalid Tax Type : ' . $record_data->type;
		}

		if (count($errors) > 0) {
			dump($errors);
			return;
		}

		$record = self::firstOrNew([
			'company_id' => $company->id,
			'name' => $record_data->tax_name,
		]);
		$record->type_id = $type->id;
		$record->created_by_id = $admin->id;
		$record->save();
		return $record;
	}

}
