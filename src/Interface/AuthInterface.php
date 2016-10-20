<?php
namespace App\Interfaces;

/**
 * Interface AuthInterface
 * @package App\Interfaces
 */
interface AuthInterface
{
    /**
     * Returns user token after authorized
     * Using offline access by default
     *
     * @param $numberOrEmail
     * @param $password
     * @param array $scope
     * @return string
     */
    public function authorize($numberOrEmail, $password, array $scope = ['offline']);
}