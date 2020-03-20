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
	    when('/invoice-pkg/invoice/view/:id', {
	        template: '<invoice-view></invoice-view>',
	        title: 'View Invoice',
	    });
	}]);

	//INVOICES
    var invoice_list_template_url = "{{asset($invoice_pkg_prefix.'/public/themes/'.$theme.'/invoice-pkg/invoice/list.html')}}";
    var invoice_view_template_url = "{{asset($invoice_pkg_prefix.'/public/themes/'.$theme.'/invoice-pkg/invoice/view.html')}}";
</script>
<script type="text/javascript" src="{{asset($invoice_pkg_prefix.'/public/themes/'.$theme.'/invoice-pkg/invoice/controller.js')}}"></script>
