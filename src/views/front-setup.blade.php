@if(config('invoice-pkg.DEV'))
    <?php $invoice_pkg_prefix = '/packages/abs/invoice-pkg/src';?>
@else
    <?php $invoice_pkg_prefix = '';?>
@endif

<script type="text/javascript">
    var invoice_voucher_list_template_url = "{{asset($invoice_pkg_prefix.'/public/themes/'.$theme.'/invoice-pkg/invoice/invoices.html')}}";
</script>
<script type="text/javascript" src="{{asset($invoice_pkg_prefix.'/public/themes/'.$theme.'/invoice-pkg/invoice/controller.js')}}"></script>
