<?php
namespace App\Controller;

use App\Controller\Component\VkAuthComponent;
use App\Interfaces\AuthInterface;
use App\Interfaces\TokenHandlerInterface;
use App\Traits\SenderTrait;
use Cake\Cache\Cache;
use Cake\Controller\Component\CookieComponent;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Client as G;
use GuzzleHttp\Cookie\CookieJar;

/**
 * Class ClientController
 * @package App\Controller
 * @property Client $_sender
 * @property CookieJar $_jar
 * @property CookieComponent $Cookie
 * @property VkAuthComponent $VkAuth
 */
class ClientController extends AppController implements AuthInterface, TokenHandlerInterface
{

    use SenderTrait;

    /**
     * @var string
     */
    private $_cachePrefix = 'data_';

    /**
     * @var G
     */
    private $_sender;

    /**
     * @var CookieJar
     */
    private $_jar;

    /**
     * @var object|array
     */
    protected $_settings;

    /**
     * @var array
     */
    public $components = ['Cookie', 'VkAuth'];

    protected $_token;

    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow();
        $this->_jar = new CookieJar();

        $this->_sender = new Client([
            'cookies' => $this->_jar
        ]);
        $this->_settings = $this->_loadSettings()
            ->application;
    }

    /**
     * Getting value from specific cache
     *
     * @param null $key
     * @return mixed
     */
    public function get($key = null)
    {
        return Cache::read($this->_cachePrefix . $key);
    }

    /**
     * Sets value for specific cache
     *
     * @param null $key
     * @param null $value
     * @return bool
     */
    public function set($key = null, $value = null)
    {
        return Cache::write($this->_cachePrefix . $key, $value);
    }

    /**
     * @param $numberOrEmail
     * @param $password
     * @return \Cake\Network\Response|null
     */
    public function authorize($numberOrEmail, $password)
    {
        if (!$this->Cookie->read('vk_uid')) {
            $vkData = $this->VkAuth->_auth(
                $this->_sender,
                $this->_jar,
                $this->_settings->app_id,
                $this->_settings->secret_key,
                $numberOrEmail,
                $password
            );

            if ($vkData) {
                $this->_token = $vkData->access_token;
                $this->Cookie->write('vk_uid', $vkData->user_id);

                return $this->_jsonResponse([
                    'auth' => $vkData,
                    'status' => 1,
                    'error' => []
                ]);
            }

            return $this->_jsonResponse([
                'auth' => [],
                'status' => 0,
                'error' => __('auth_failed')
            ]);

        } else {
            return $this->_jsonResponse([
                'auth' => Cache::read('token_' . $this->Cookie->read('vk_uid')),
                'status' => 1,
                'error' => []
            ]);
        }
    }

    /**
     * @return \Cake\Network\Response|null
     * @throws Exception
     */
    public function executeAction()
    {
        $this->autoRender = false;
        if ($this->request->is(['ajax', 'post'])) {
            $data = $this->request->data;

            return $this->_jsonResponse(
                json_decode(
                    $this->_prepare($data['action'], $data['fields'])
                )
            );
        }

        throw new Exception('You are not allowed to be here', 403);
    }
}