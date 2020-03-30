app.component('invoiceList', {
    templateUrl: invoice_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $element, $mdSelect) {
        $scope.loading = true;
        $('#search_invoice').focus();
        var self = this;
        self.theme = admin_theme;
        self.hasPermission = HelperService.hasPermission;
         if (!self.hasPermission('invoices')) {
            window.location = "#!/page-permission-denied";
            return false;
        }
        
        $http.get(
                laravel_routes['getInvoiceSessionData']
            ).then(function(response) {
                if(response.data.success){
                    self.status = response.data.status;
                    self.account_code = response.data.account_code;
                    self.account_name = response.data.account_name;
                    self.config_status = response.data.config_status;
                    self.invoice_date = response.data.invoice_date;
                    $('#daterange1').val(response.data.invoice_date);
                    $('#search_invoice').val(response.data.search_invoice);
                }
            });
        /*if (!self.hasPermission('invoices')) {
            window.location = "#!/page-permission-denied";
            return false;
        }*/

        $('.docDatePicker').bootstrapDP({
            endDate: 'today',
            todayHighlight: true
        });

        $('#reference_date').datepicker({
            dateFormat: 'dd-mm-yy',
            maxDate: '0',
            todayHighlight: true,
            autoclose: true
        });
        var table_scroll;
        table_scroll = $('.page-main-content.list-page-content').height() - 37;
        setTimeout(function(){
        var dataTable = $('#invoice_list').DataTable({
            "dom": cndn_dom_structure,
            "language": {
                "search": "",
                "searchPlaceholder": "Search",
                "lengthMenu": "Rows _MENU_",
                "paginate": {
                    "next": '<i class="icon ion-ios-arrow-forward"></i>',
                    "previous": '<i class="icon ion-ios-arrow-back"></i>'
                },
            },
            pageLength: 10,
            processing: true,
            stateSaveCallback: function(settings, data) {
                localStorage.setItem('CDataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function(settings) {
                var state_save_val = JSON.parse(localStorage.getItem('CDataTables_' + settings.sInstance));
                if (state_save_val) {
                    $('#search_invoice').val(state_save_val.search.search);
                }
                return JSON.parse(localStorage.getItem('CDataTables_' + settings.sInstance));
            },
            serverSide: true,
            paging: true,
            stateSave: true,
            scrollY: table_scroll + "px",
            scrollX: true,
            scrollCollapse: true,
            ajax: {
                url: laravel_routes['getInvoiceList'],
                type: "GET",
                dataType: "json",
                data: function(d) {
                    d.account_name = self.account_name;
                    d.account_code = self.account_code;
                    d.invoice_number = self.invoice_number;
                    d.invoice_date = $('#daterange1').val();
                    d.config_status = self.config_status;
                },
            },
            columns: [
                { data: 'action', class: 'action', name: 'action', searchable: false },
                { data: 'invoice_date', searchable: false},
                { data: 'invoice_number', name: 'invoices.invoice_number' },
               // { data: 'invoices_of_name', name: 'invoices.invoices_of_name' },
                { data: 'account_code', name: 'customers.code', searchable: true },
                { data: 'account_name', name: 'customers.name', searchable: true },
                { data: 'invoice_amount',name: 'invoices.invoice_amount',  searchable: true },
                { data: 'received_amount',name: 'invoices.received_amount', searchable: true },
                { data: 'balance_amount',  searchable: false },
                { data: 'description', name: 'invoices.remarks' },
                { data: 'status_name', name: 'configs.name' },
            ],
            "infoCallback": function(settings, start, end, max, total, pre) {
                $('#table_info').html(total)
                $('.foot_info').html('Showing ' + start + ' to ' + end + ' of ' + max + ' entries')
            },
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            }
        });
        $('.dataTables_length select').select2();
        
        $('.refresh_table').on("click", function() {
            $('#invoice_list').DataTable().ajax.reload();
        });

        $scope.clear_search = function() {
            $('#search_invoice').val('');
            $('#invoice_list').DataTable().search('').draw();
            // $('#search_invoice').focus();

        }

        var dataTables = $('#invoice_list').dataTable();
        $("#search_invoice").keyup(function() {
            dataTables.fnFilter(this.value);
        // $('#search_invoice').focus();

        });

        //FOCUS ON SEARCH FIELD
        setTimeout(function() {
            $('div.dataTables_filter input').focus();
        }, 2500);

        //DELETE
        $scope.deleteInvoice = function($id) {
            $('#invoice_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#invoice_id').val();
            $http.get(
                laravel_routes['deleteInvoiceData'], {
                    params: {
                        id: $id,
                    }
                }
            ).then(function(response) {
                if (response.data.success) {
                    custom_noty('success', 'Invoice Deleted Successfully');
                    $('#invoice_list').DataTable().ajax.reload();
                    $location.path('/invoice-pkg/invoice/list');
                    $('#search_invoice').focus();

                }
            });
        }
        $element.find('input').on('keydown', function(ev) {
            ev.stopPropagation();
        });
        $scope.clearSearchTerm = function() {
            $scope.searchTerm = '';
            $scope.searchTerm1 = '';
        };
        /* Modal Md Select Hide */
        $('.modal').bind('click', function(event) {
            if ($('.md-select-menu-container').hasClass('md-active')) {
                $mdSelect.hide();
            }
        });

        var datatables = $('#invoice_list').dataTable();
        $scope.reset_filter = function() {
            self.account_code = '';
            self.account_name  = '';
            self.invoice_number = '';
            self.config_status = '';
            $('#daterange1').val(null);
            datatables.fnFilter();
        }
        // $scope.loadDT = function (){
        //     datatables.fnFilter();
        //     $('#search_invoice').focus();

        // }
        $rootScope.loading = false;
        }, 2500);
    }
});

//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('invoiceView', {
    templateUrl: invoice_view_template_url,
    controller: function($http, HelperService, $scope, $routeParams, $rootScope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        if (!self.hasPermission('view-invoice')) {
            window.location = "#!/page-permission-denied";
            return false;
        }
        /*self.region_permission = self.hasPermission('regions');
        self.city_permission = self.hasPermission('cities');*/
        self.angular_routes = angular_routes;
        $http.get(
            laravel_routes['getInvoiceViewData'], {
                params: {
                    id: $routeParams.id,
                }
            }
        ).then(function(response) {
            self.invoice = response.data.invoice;
            self.transactions = response.data.transactions;
        });
        /* Tab Funtion */
        $('.btn-nxt').on("click", function() {
            $('.editDetails-tabs li.active').next().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-prev').on("click", function() {
            $('.editDetails-tabs li.active').prev().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-pills').on("click", function() {
            tabPaneFooter();
        });
        $scope.btnNxt = function() {}
        $scope.prev = function() {}
    }
});