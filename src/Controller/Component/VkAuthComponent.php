<?php
namespace App\Controller\Component;

use Cake\Cache\Cache;
use Cake\Controller\Component;
use Cake\Core\Configure;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\TransferStats;
use phpQuery;

/**
 * Class VkAuthComponent
 * @package App\Controller\Component
 * @property Component\CookieComponent $Cookie
 */
class VkAuthComponent extends Component
{

    /**
     * @var array
     */
    protected $_defaultConfig = [];

    private $loginAction = 'https://login.vk.com/?act=login';

    /**
     * @var array
     */
    private $_fields = [];

    /**
     * @var Client
     */
    private $_client;

    /**
     * @var integer
     */
    private $_appId;

    /**
     * @var string
     */
    private $_appSecret;

    /**
     * @var \Callback
     */
    private $_statsFunction;

    /**
     * @var object
     */
    private $_urlStatus;

    /**
     * @var CookieJar
     */
    private $_cookie;

    public function initialize(array $config)
    {
        parent::initialize($config);
    }

    public function _auth(Client $client, CookieJar $cookieJar, $appId, $appSecret, $email = null, $password = null)
    {
        $this->_client = $client;
        $this->_appId = $appId;
        $this->_cookie = $cookieJar;
        $this->_appSecret = $appSecret;

        $this->_statsFunction = function (TransferStats $transferStats) {
            return $this->_urlStatus = (object)[
                'url' => $transferStats->getEffectiveUri(),
                'status' => $transferStats->getResponse()
                    ->getStatusCode()
            ];
        };

        $loginPage = $this->_client->request('GET', 'https://m.vk.com/');
        $this->_parseLoginFields($loginPage);

        $this->_fields['email'] = $email;
        $this->_fields['pass'] = $password;

        return $this->_authorize();
    }

    /**
     * Login fields parsing
     *
     * @param Response $response
     */
    private function _parseLoginFields(Response $response)
    {
        $dom = phpQuery::newDocumentHTML($response->getBody());

        $form = pq($dom)
            ->find('form');

        $this->loginAction = $form->attr('action');
        $inputsGroup = pq($form)->find('input');

        foreach ($inputsGroup as $input) {
            $input = pq($input);

            if ($input->attr('name')) {
                $this->_fields[$input->attr('name')] = $input->attr('value');
            }
        }
    }

    private function _authorize()
    {
        if (($token = Cache::read('token')) === false) {
            $this->_client->request('POST', $this->loginAction, [
                'form_params' => $this->_fields,
                'on_stats' => $this->_statsFunction
            ]);
            $this->_client->get('https://vk.com/?_fm=index');

            $codeUrl = 'https://oauth.vk.com/authorize?' .
                'client_id=' . $this->_appId . '&' .
                'display=page&' .
                'redirect_uri=&scope=' . Configure::read('scope') . '&' .
                'response_type=code&' .
                'v=5.59';

            $data = $this->_client->get($codeUrl, [
                'on_stats' => $this->_statsFunction
            ]);

            preg_match('/<form method="post" action="(.+?)">/', $data->getBody()->getContents(), $formAccept);

            if (count($formAccept) == 2) {
                $this->_client->get($formAccept[1], [
                    'on_stats' => $this->_statsFunction
                ]);
            }

            $code = explode('=', $this->_urlStatus->url->getFragment());
            $code = end($code);

            $tokenUrl = 'https://oauth.vk.com/access_token?' .
                'client_id=' . $this->_appId . '&' .
                'client_secret=' . $this->_appSecret . '&' .
                'redirect_uri=&' .
                'code=' . $code;

            $token = $this->_client->get($tokenUrl, [
                'on_stats' => $this->_statsFunction
            ]);
            $token = json_decode($token->getBody()->getContents());
            Cache::write('token_' . $token->user_id, $token);
        }
        return $token;
    }

}
