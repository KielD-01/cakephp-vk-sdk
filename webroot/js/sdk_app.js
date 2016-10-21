/*
 VK SDK Application
 */

var sdk = angular.module('sdkMain', ['ngRoute', 'ngSanitize']),
    settings = {
        tplPath: '../views/',
        tplExt: '.html',
        f: function (a) {
            return settings.tplPath + a + settings.tplExt;
        }
    };

sdk.config(function ($routeProvider) {
    $routeProvider.when('/', {
        templateUrl: settings.f('auth'),
        controller: 'Auth'
    });
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

        if (typeof a == 'string' && typeof b == 'string') {
            return true;
        }
    };
});

sdk.controller('Auth', function ($scope, $http, CheckerService) {
    $scope.auth = function () {

        CheckerService.checker([$scope.email, $scope.pass], ['undefined', 'string']);

        if (typeof $scope.email == 'undefined' || typeof $scope.pass == 'undefined') {
            Materialize.toast('Trying to submit empty fields, bitch?', 1250);
            return console.log('Authorization flow has failed')
        }

        if ($scope.email.length > 4 && $scope.pass.length > 5) {
            return console.log('Authorization flow has been executed');
        }
    };
});