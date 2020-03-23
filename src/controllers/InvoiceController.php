<?php

namespace Abs\InvoicePkg;
use Abs\InvoicePkg\Invoice;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Entrust;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;
use Session;
use App\Config;
use App\ActivityLog;

class InvoiceController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.admin_theme');
	}

	public function getInvoiceSessionData(){
		$this->data['status'] = Config::select('id','name')->get();
		$this->data['search_invoice'] = Session::get('search_invoice');
		$this->data['account_name'] = Session::get('account_name');
		$this->data['account_code'] = Session::get('account_code');
		$this->data['invoice_date'] = Session::get('invoice_date');
		$this->data['invoice_number'] = Session::get('invoice_number');
		$this->data['config_status'] = Session::get('config_status');
		$this->data['success'] = true;
		return response()->json($this->data);
	}

	public function getInvoiceList(Request $request) {
		Session::put('search_invoice',$request->search['value']);
		Session::put('account_name',$request->account_name);
		Session::put('account_code',$request->account_code);
		Session::put('invoice_date',$request->invoice_date);
		Session::put('invoice_number',$request->invoice_number);
		Session::put('config_status',$request->config_status);
		$start_date = '';
		$end_date = '';
		if(!empty($request->invoice_date)){
			$date_range = explode(' - ',$request->invoice_date);
			$start_date = $date_range[0];
			$end_date = $date_range[1];
		}
		
		$invoices = Invoice::select(
				DB::raw('DATE_FORMAT(invoices.invoice_date,"%d-%m-%Y") as invoice_date'),
				//'invoice_ofs.name as invoice_of_name',
				//'invoices.invoice_of_id',
				'invoices.invoice_number',
				'customers.code as account_code',
				'customers.name as account_name',
				'invoices.id as id',
				DB::raw('IF(invoices.remarks IS NULL,"NA",invoices.remarks) as description'),
				DB::raw('format(invoices.invoice_amount,0,"en_IN") as invoice_amount'),
				DB::raw('format(invoices.received_amount,0,"en_IN") as received_amount'),
				DB::raw('format((invoices.invoice_amount - invoices.received_amount),0,"en_IN") as balance_amount'),
				DB::raw('IF(configs.name IS NULL,"NA",configs.name) as status_name'),
				'invoices.invoice_number'
			)
			//->leftJoin('configs as invoice_ofs','invoices.invoice_of_id','=','invoice_ofs.id')
			->leftJoin('configs','invoices.status_id','=','configs.id')
			->leftJoin('customers','invoices.customer_id','=','customers.id')
			->where('invoices.company_id', Auth::user()->company_id)
			->where(function ($query) use ($request) {
				if (!empty($request->account_code)) {
					$query->where('customers.code', 'LIKE',$request->account_code);
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->account_name)) {
					$query->where('customers.name', 'LIKE',$request->account_name);
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->invoice_number)) {
					$query->where('invoices.invoice_number', 'LIKE',$request->invoice_number);
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->config_status)) {
					$query->where('configs.id',$request->config_status);
				}
			})
			->where(function ($query) use ($request,$start_date,$end_date){
				if (!empty($request->invoice_date) && ($start_date && $end_date)) {
					$query->where('invoices.invoice_date','>=',$start_date)->where('invoices.invoice_date','<=',$end_date);
				}
			})
			
		;
		return Datatables::of($invoices)
			->addColumn('action', function ($invoices) {
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$view = asset('public/themes/' . $this->data['theme'] . '/img/content/table/eye.svg');
				$view_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/eye-active.svg');
				$output = '';
				//if (Entrust::can('view-invoice')) {
					$output .= '<a href="#!/invoice-pkg/invoice/view/' . $invoices->id . '" id = "" title="view"><img src="' . $view . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $view_active . '" onmouseout=this.src="' . $view . '"></a>';
				/*}
				if (Entrust::can('delete-invoice')) {*/
					$output .= '<a href="javascript:;" data-toggle="modal" data-target="#invoices-delete-modal" onclick="angular.element(this).scope().deleteInvoice(' . $invoices->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete_active . '" onmouseout=this.src="' . $img_delete . '"></a>';
				/*}*/
				return $output;
			})
			->make(true);
	}

	public function getInvoiceViewData(Request $request) {
		$id = $request->id;
		if (!$id) {
			$this->data['message'] = 'Invalid Invoice';
			$this->data['success'] = false;
			return response()->json($this->data);
		} else {
			$this->data['invoice'] = $invoice = Invoice::select(
				DB::raw('DATE_FORMAT(invoices.invoice_date,"%d-%m-%Y") as invoice_date'),
				//'invoice_ofs.name as invoice_of_name',
				//'invoices.invoice_of_id',
				'invoices.invoice_number',
				'invoices.id as id',
				DB::raw('IF(invoices.remarks IS NULL,"NA",invoices.remarks) as description'),
				DB::raw('format(invoices.invoice_amount,0,"en_IN") as invoice_amount'),
				DB::raw('format(invoices.received_amount,0,"en_IN") as received_amount'),
				DB::raw('format((invoices.invoice_amount - invoices.received_amount),0,"en_IN") as balance_amount'),
				DB::raw('IF(configs.name IS NULL,"NA",configs.name) as status_name'),
				'customers.code as account_code',
				'customers.name as account_name',
				'invoices.invoice_number'
			)
			//->leftJoin('configs as invoice_ofs','invoices.invoice_of_id','=','invoice_ofs.id')
			->leftJoin('configs','invoices.status_id','=','configs.id')
			->leftJoin('customers','invoices.customer_id','=','customers.id')
			->where('invoices.company_id', /*Auth::user()->company_id*/2)
			->where('invoices.id',$request->id)
			->first();
			$this->data['transactions'] = DB::table('invoice_details')
				->where('invoice_id',$request->id)
				->leftJoin('configs','invoice_details.status_id','=','configs.id')
				//->leftJoin('configs as type','invoices.type_id','=','configs.id')
				->select(
					DB::raw('DATE_FORMAT(invoice_details.created_at,"%d-%m-%Y") as invoice_date'),
					DB::raw('IF(configs.name IS NULL,"NA",configs.name) as status_name'),
				DB::raw('format(invoice_details.received_amount,0,"en_IN") as received_amount'),
				DB::raw('format((invoice_details.invoice_amount - invoice_details.received_amount),0,"en_IN") as balance_amount')
					//,'type.name as type_name'
				)
			->get();
		}
			if(!$invoice){
				$this->data['message'] = 'Invoice Not Found!!';
				$this->data['success'] = false;
				return response()->json($this->data);
			}
			$this->data['success'] = true;
			return response()->json($this->data);
	}

	public function deleteInvoiceData(Request $request) {
		DB::beginTransaction();
		try {
			$invoice = Invoice::where('id', $request->id)->delete();
			$invoice_details = DB::table('invoice_details')->where('invoice_id', $request->id)->delete();
			if ($invoice) {
				$activity = new ActivityLog;
				$activity->date_time = Carbon::now();
				$activity->user_id = Auth::user()->id;
				$activity->module = 'Invoice';
				$activity->entity_id = $request->id;
				$activity->entity_type_id = 1420;
				$activity->activity_id = 282;
				$activity->activity = 282;
				$activity->details = json_encode($activity);
				$activity->save();

				DB::commit();
				return response()->json(['success' => true, 'message' => 'Invoice Deleted Successfully']);
			}
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
}
