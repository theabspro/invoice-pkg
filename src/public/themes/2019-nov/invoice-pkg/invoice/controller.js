app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
    when('/invoices', {
        template: '<invoices></invoices>',
        title: 'Invoices',
    });
}]);

app.component('invoices', {
    templateUrl: invoice_voucher_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location) {
        $scope.loading = true;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        $http({
            url: laravel_routes['getInvoices'],
            method: 'GET',
        }).then(function(response) {
            self.invoice_vouchers = response.data.invoice_vouchers;
            $rootScope.loading = false;
        });
        $rootScope.loading = false;
    }
});