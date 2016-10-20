<?php
namespace App\Interfaces;

/**
 * Interface TokenHandlerInterface
 * @package App\Interfaces
 */
interface TokenHandlerInterface
{

    /**
     * Setting key to be cached with specific data
     *
     * @param null|mixed $key
     * @param null|mixed $value
     * @return mixed
     */
    public function set($key = null, $value = null);

    /**
     * Returns key value from cached data
     *
     * @param string|null|bool $key
     * @return mixed
     */
    public function get($key = null);
}