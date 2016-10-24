/*
 VK SDK Application
 */

var sdk = angular.module('sdkMain', ['ngRoute', 'ngSanitize', 'blockUI', 'ui.materialize']),
    settings = {
        tplPath: '../views/',
        tplExt: '.html',
        f: function (a) {
            return settings.tplPath + a + settings.tplExt;
        }
    };

sdk.config(function ($routeProvider) {
    $routeProvider.when('/:template', {
        templateUrl: function (url) {
            return settings.f(url.template);
        },
        controller: 'Actions'
    })
        .when('/', {
            templateUrl: settings.f('auth'),
            controller: 'Auth'
        })
        .otherwise('/');
});

sdk.run(function ($rootScope, $http, blockUI) {
    $rootScope.check = 0;

    if ($rootScope.check == 0) {

        blockUI.start({
            message: 'Checking authorization',
            'z-index': 1000
        });

        $http.get('/check-auth').then(function (res) {
            blockUI.stop();
            if (res.data.status == 1) {
                $rootScope.user = res.data.user;
                $rootScope.check = 1;
                return location.hash = '#/menu';
            } else {
                return location.hash = '#/';
            }
        });
    }

});

sdk.service('CheckerService', function () {
    this.checker = function (a, b) {

        if (typeof a == 'object') {
            if (typeof b == 'object') {
                angular.forEach(a, function (c, d) {
                    if (typeof c == b[d]) {
                        return false;
                    }
                });
                return true;
            }

            if (typeof b == 'string') {
                angular.forEach(a, function (c) {
                    if (typeof c == b) {
                        return false;
                    }
                });
                return true;
            }
        }

        if (typeof a == 'string' && typeof b == 'object') {
            throw('ZaebalException : Da iti ti naxuj');
        }

        return typeof a == b;

    };
});

sdk.controller('Auth', function ($rootScope, $scope, $http, $httpParamSerializer, CheckerService, blockUI) {
    $scope.auth = function () {

        if (CheckerService.checker([$scope.email, $scope.pass], ['string', 'string'])) {

            if (typeof $scope.email == 'undefined' || typeof $scope.pass == 'undefined') {
                Materialize.toast('Trying to submit empty fields, bitch?', 1250);
                return console.log('Authorization flow has failed')
            }

            if ($scope.email.length > 4 && $scope.pass.length > 5) {

                blockUI.start({
                    'message': 'Trying to authorize...',
                    'z-index': 1000
                });

                $http.post('/login', $httpParamSerializer({
                    email: $scope.email,
                    pass: $scope.pass
                })).then(function (res) {
                    $rootScope.user = res.data.auth;

                    if (res.data.status == 1) {
                        location.hash = '#/menu'
                    }
                });

                blockUI.stop();
                return console.log('Authorization flow has been executed');
            }
        }
    };
});

sdk.controller('Actions', function ($scope, $http, $rootScope) {
    $scope.actions = function () {
        $http.get('/application/allowed.json').then(function (response) {
            return $scope.actions = response.data.actions;
        });
    };

    $scope.user = function () {
        $http.get('http://api.vk.com/method/users.get?user_id=' + $rootScope.user.user_id + '&v=5.5').then(function (res) {
            return $scope.user = res.data;
        });
    };

    $scope.actionForm = function (index) {
        angular.forEach($scope.actions, function (action, i) {
            if (i == index) {
                $scope.formFields = action.fields;
                $scope.selectedAction = '';
                $('ul.tabs').tabs('select_tab', 'field-dynamic-0');
                return true;
            }
        });
    };

    $scope.actions();
    $scope.user();
});

$('a').on('click', function () {
    if ($(this).data('tab')) {
        $('div[data-tabs="fields"]').hide();
        $('div[id="' + $(this).data('tab') + '"]').show();
        $('a[data-tab]').removeClass('active');
        $('a[data-tab="' + $(this).data('tab') + '"]').addClass('active');
    }
});