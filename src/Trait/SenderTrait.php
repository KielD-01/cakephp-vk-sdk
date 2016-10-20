<?php
namespace App\Traits;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Filesystem\File;
use Exception;
use GuzzleHttp\Client;

/**
 * Class SenderTrait
 * @package App\Traits
 */
trait SenderTrait
{

    /**
     * Actions array with scope permissions, allowed fields and urls
     * @var array $_actions
     */
    private $_actions = [];

    /**
     * Action information
     *
     * @var array $_action
     */
    private $_action = [];

    /**
     * @var array
     */
    private $_data;

    /**
     * @var string
     */
    private $_requestData;

    public function _prepare($action, array $data = [], array $scope = [])
    {
        $this->_data = $data;
        $this->_loadActions();
        $this->_loadActionByKey($action);
        $this->_checkActionScope($scope);
        $this->_proceed();
        return $this->_send();
    }

    /**
     * Returns settings for application
     *
     * @return mixed
     * @throws Exception
     */
    protected function _loadSettings()
    {
        if (!$settings = (new File(Configure::read('settings_file')))) {
            throw new Exception('No \'settings.json\' file exists');
        }

        return json_decode($settings->read());
    }

    /**
     * Loading action list
     * If no file exists - throwing exception
     *
     * @return mixed
     * @throws Exception
     */
    private function _loadActions()
    {
        if (($configuration = Configure::read('actions_file'))) {
            if (($actions = new File($configuration))->exists()) {
                return $this->_actions = json_decode($actions->read());
            }
            throw new Exception('No actions file found');
        }
        throw new Exception('Can\'t read \'actions file\' configuration');
    }

    /**
     * Getting action by specific key
     * Throws an exception on non-existing key
     *
     * @param $key
     * @return mixed
     * @throws Exception
     */
    private function _loadActionByKey($key)
    {
        if (array_key_exists($key, (array)$this->_actions)) {
            return $this->_action = $this->_actions->{$key};
        }

        throw new Exception('No action key found like ' . $key);
    }

    private function _checkActionScope(array $scope = [])
    {
    }

    /**
     * @return string
     */
    private function _proceed()
    {
        $requestString = [];

        foreach ($this->_data as $field => $value) {
            $requestString[] = "{$field}={$value}";
        }

        $requestString[] = "access_token=" . Cache::read('token')->access_token;
        $requestString[] = 'v=5.5';
        $requestString[] = 'sig=' . md5('/method/messages.send?' . implode('&', $requestString) . Cache::read('token')->secret);

        return $this->_requestData = implode('&', $requestString);
    }

    private function _send()
    {
        $client = new Client();

        $response = $client->request('GET', 'https://api.vk.com/method/messages.send?' . $this->_requestData);
        return $response
            ->getBody()
            ->getContents();
    }
}