@if(config('invoice-pkg.DEV'))
    <?php $invoice_pkg_prefix = '/packages/abs/invoice-pkg/src';?>
@else
    <?php $invoice_pkg_prefix = '';?>
@endif

<script type="text/javascript">
	app.config(['$routeProvider', function($routeProvider) {

	    $routeProvider.
	    //INVOICE
	    when('/invoice-pkg/invoice/list', {
	        template: '<invoice-list></invoice-list>',
	        title: 'Invoices',
	    }).
	    when('/invoice-pkg/invoice/add', {
	        template: '<invoice-form></invoice-form>',
	        title: 'Add Invoice',
	    }).
	    when('/invoice-pkg/invoice/edit/:id', {
	        template: '<invoice-form></invoice-form>',
	        title: 'Edit Invoice',
	    });
	}]);

	//INVOICES
    var invoice_list_template_url = "{{asset($invoice_pkg_prefix.'/public/themes/'.$theme.'/invoice-pkg/invoice/list.html')}}";
    var invoice_form_template_url = "{{asset($invoice_pkg_prefix.'/public/themes/'.$theme.'/invoice-pkg/invoice/form.html')}}";
</script>
<script type="text/javascript" src="{{asset($invoice_pkg_prefix.'/public/themes/'.$theme.'/invoice-pkg/invoice/controller.js')}}"></script>
