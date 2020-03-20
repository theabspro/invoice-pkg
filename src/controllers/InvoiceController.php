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

class InvoiceController extends Controller {

	public function __construct() {
		$this->data['theme'] = config('custom.admin_theme');
	}

	/*public function getInvoiceSessionData(){
		$this->data['search_state'] = Session::get('search_state');
		$this->data['filter_state_code'] = Session::get('filter_state_code');
		$this->data['filter_state_name'] = Session::get('filter_state_name');
		$this->data['filter_state_country'] = Session::get('filter_state_country');
		$this->data['filter_state_status'] = Session::get('filter_state_status');
		$this->data['success'] = true;
		return response()->json($this->data);
	}*/

	public function getInvoiceList(Request $request) {
		$invoices = Invoice::select(
				DB::raw('DATE_FORMAT(invoices.invoice_date,"%d-%m-%Y") as invoice_date'),
				//'invoice_ofs.name as invoice_of_name',
				//'invoices.invoice_of_id',
				'invoices.invoice_number',
				'invoices.id as id',
				'invoices.remarks as description',
				DB::raw('format(invoices.invoice_amount,0,"en_IN") as invoice_amount'),
				DB::raw('format(invoices.received_amount,0,"en_IN") as received_amount'),
				DB::raw('format((invoices.invoice_amount - invoices.received_amount),0,"en_IN") as balance_amount'),
				'configs.name as status_name',
				'invoices.invoice_number'
			)
			//->leftJoin('configs as invoice_ofs','invoices.invoice_of_id','=','invoice_ofs.id')
			->leftJoin('configs','invoices.status_id','=','configs.id')
			->where('invoices.company_id', /*Auth::user()->company_id*/2)
			/*->where(function ($query) use ($request) {
				if (!empty($request->name)) {
					$query->where('ledgers.name', 'LIKE', '%' . $request->name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->code)) {
					$query->where('ledgers.code', 'LIKE', '%' . $request->code . '%');
				}
			})
			*/
		;
		return Datatables::of($invoices)
			->addColumn('action', function ($invoices) {
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$view = asset('public/themes/' . $this->data['theme'] . '/img/content/table/eye.svg');
				$view_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/eye-active.svg');
				$output = '';
				//if (Entrust::can('view-invoice')) {
					$output .= '<a href="#!/jv-pkg/invoice/view/' . $invoices->id . '" id = "" title="view"><img src="' . $view . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $view_active . '" onmouseout=this.src="' . $view . '"></a>';
				/*}
				if (Entrust::can('delete-invoice')) {*/
					$output .= '<a href="javascript:;" data-toggle="modal" data-target="#invoices-delete-modal" onclick="angular.element(this).scope().deleteLedger(' . $invoices->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete_active . '" onmouseout=this.src="' . $img_delete . '"></a>';
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
				'invoices.remarks as description',
				DB::raw('format(invoices.invoice_amount,0,"en_IN") as invoice_amount'),
				DB::raw('format(invoices.received_amount,0,"en_IN") as received_amount'),
				DB::raw('format((invoices.invoice_amount - invoices.received_amount),0,"en_IN") as balance_amount'),
				'configs.name as status_name',
				'invoices.invoice_number'
			)
			//->leftJoin('configs as invoice_ofs','invoices.invoice_of_id','=','invoice_ofs.id')
			->leftJoin('configs','invoices.status_id','=','configs.id')
			->where('invoices.company_id', /*Auth::user()->company_id*/2)
			->where('invoices.id',$request->id)
			->first();
			$this->data['transactions'] = DB::table('invoice_details')
				->where('invoice_id',$request->id)
				->leftJoin('configs','invoice_details.status_id','=','configs.id')
				//->leftJoin('configs as type','invoices.type_id','=','configs.id')
				->select(
					DB::raw('DATE_FORMAT(invoice_details.created_at,"%d-%m-%Y") as invoice_date'),
					'configs.name as status_name',
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

	public function deleteInvoice(Request $request) {
		DB::beginTransaction();
		try {
			$ledger = Invoice::where('id', $request->id)->forceDelete();
			if ($ledger) {
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
