<?php
namespace App\Controller;

use App\Controller\Component\VkAuthComponent;
use App\Interfaces\AuthInterface;
use App\Interfaces\TokenHandlerInterface;
use App\Traits\SenderTrait;
use Cake\Cache\Cache;
use Cake\Controller\Component\CookieComponent;
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

        $this->checkCookie();
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

    public function authorize($numberOrEmail, $password, array $scope = ['offline'])
    {

    }

    public function action()
    {
        $this->autoRender = false;
        return $this->_jsonResponse(
            json_decode(
                $this->_prepare('messages.send', [
                    'domain' => 'egoistfromdivel',
                    'message' => 'Отправляю с приложухи'
                ])
            )
        );
    }

    private function checkCookie()
    {
        if (!$this->Cookie->read('vk_uid') || !Cache::read('token')) {
            $vkData = $this->VkAuth->_auth(
                $this->_sender,
                $this->_jar,
                $this->_settings->app_id,
                $this->_settings->secret_key,
                '380636638372',
                'Airglide18841q2w3e4r'
            );
            $this->_token = $vkData->access_token;
            $this->Cookie->write('vk_uid', $vkData->user_id);
        }
    }
}