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

	public function getInvoiceList(Request $request) {
		$invoices = Invoice::withTrashed()
			->select([
				'invoices.id',
				'invoices.name',
				DB::raw('COALESCE(invoices.description,"--") as description'),
				DB::raw('IF(invoices.deleted_at IS NULL, "Active","Inactive") as status'),
			])
			->where('invoices.company_id', Auth::user()->company_id)
			->where(function ($query) use ($request) {
				if (!empty($request->name)) {
					$query->where('invoices.name', 'LIKE', '%' . $request->name . '%');
				}
			})
			->where(function ($query) use ($request) {
				if ($request->status == '1') {
					$query->whereNull('invoices.deleted_at');
				} else if ($request->status == '0') {
					$query->whereNotNull('invoices.deleted_at');
				}
			})
			->orderby('invoices.id', 'Desc');

		return Datatables::of($invoices)
			->addColumn('name', function ($invoice) {
				$status = $invoice->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $invoice->name;
			})
			->addColumn('action', function ($invoice) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				if (Entrust::can('edit-invoice')) {
					$output .= '<a href="#!/invoice-pkg/invoice/edit/' . $invoice->id . '" id = "" title="Edit"><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1_active . '" onmouseout=this.src="' . $img1 . '"></a>';
				}
				if (Entrust::can('delete-invoice')) {
					$output .= '<a href="javascript:;" data-toggle="modal" data-target="#invoice-delete-modal" onclick="angular.element(this).scope().deleteInvoice(' . $invoice->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete_active . '" onmouseout=this.src="' . $img_delete . '"></a>';
				}
				return $output;
			})
			->make(true);
	}

	public function getInvoiceFormData(Request $request) {
		$id = $request->id;
		if (!$id) {
			$invoice = new Invoice;
			$action = 'Add';
		} else {
			$invoice = Invoice::withTrashed()->find($id);
			$action = 'Edit';
		}
		$this->data['invoice'] = $invoice;
		$this->data['action'] = $action;
		$this->data['theme'];

		return response()->json($this->data);
	}

	public function saveInvoice(Request $request) {
		// dd($request->all());
		try {
			$error_messages = [
				'name.required' => 'Name is Required',
				'name.unique' => 'Name is already taken',
				'name.min' => 'Name is Minimum 3 Charachers',
				'name.max' => 'Name is Maximum 64 Charachers',
				'description.max' => 'Description is Maximum 255 Charachers',
			];
			$validator = Validator::make($request->all(), [
				'name' => [
					'required:true',
					'min:3',
					'max:64',
					'unique:invoices,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'description' => 'nullable|max:255',
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$invoice = new Invoice;
				$invoice->created_by_id = Auth::user()->id;
				$invoice->created_at = Carbon::now();
				$invoice->updated_at = NULL;
			} else {
				$invoice = Invoice::withTrashed()->find($request->id);
				$invoice->updated_by_id = Auth::user()->id;
				$invoice->updated_at = Carbon::now();
			}
			$invoice->fill($request->all());
			$invoice->company_id = Auth::user()->company_id;
			if ($request->status == 'Inactive') {
				$invoice->deleted_at = Carbon::now();
				$invoice->deleted_by_id = Auth::user()->id;
			} else {
				$invoice->deleted_by_id = NULL;
				$invoice->deleted_at = NULL;
			}
			$invoice->save();

			DB::commit();
			if (!($request->id)) {
				return response()->json([
					'success' => true,
					'message' => 'Invoice Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'Invoice Updated Successfully',
				]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json([
				'success' => false,
				'error' => $e->getMessage(),
			]);
		}
	}

	public function deleteInvoice(Request $request) {
		DB::beginTransaction();
		try {
			$invoice = Invoice::withTrashed()->where('id', $request->id)->forceDelete();
			if ($invoice) {
				DB::commit();
				return response()->json(['success' => true, 'message' => 'Invoice Deleted Successfully']);
			}
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
}
