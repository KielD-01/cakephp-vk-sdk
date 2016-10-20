<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Filesystem\File;

/**
 * Class InitShell
 * @package App\Shell
 */
class InitShell extends Shell
{

    private $_confFile = WWW_ROOT . 'settings.json';

    public function initialize()
    {

    }

    /**
     * Checking auth File
     *
     * @return bool|int|void
     */
    public function main()
    {
        return $this->_checkAuthFile() ?
            $this->out('Auth file already exists') :
            $this->_createProcedure();
    }

    private function _createProcedure()
    {
        $this->out('Preparing data for Auth File..');
        $this->_createAuthFile();
    }

    private function _checkAuthFile()
    {
        return (new File($this->_confFile))->exists();
    }

    private function _createAuthFile()
    {
        $settings = [
            'application' => [
                'app_id' => $this->in('Enter Application ID'),
                'secret_key' => $this->in('Enter Application Secret')
            ]
        ];

        $confFile = new File($this->_confFile, 1, 0644);
        $confFile->append(json_encode((object)$settings));
        $confFile->close();

        return $this->out((new File($this->_confFile))->exists() ?
            'Configuration file has been created' :
            'Failed to create configuration file'
        );
    }
}
