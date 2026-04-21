<?php
namespace Package\Raxon\Audio\Trait;

use Raxon\App;
use Raxon\Config;

use Raxon\Doctrine\Module\Database;
use Raxon\Doctrine\Module\Entity;
use Raxon\Exception\DirectoryCreateException;

use Raxon\Exception\ObjectException;
use Raxon\Module\Cli;
use Raxon\Module\Controller;
use Raxon\Module\Data;
use Raxon\Module\Dir;
use Raxon\Module\Core;
use Raxon\Module\File;
use Raxon\Parse\Module\Parse;

use Raxon\Node\Module\Node;

use Exception;

trait Main {
    const NAME = 'Audio';
    const ROUTE_NAME = 'application-ollama-manager';
    /**
     * @throws DirectoryCreateException
     * @throws Exception
     */
    public function install($flags, $options): void
    {
        $object = $this->object();
        if($object->config(Config::POSIX_ID) !== 0){
            return;
        }
        $command = 'app install raxon/account -patch';
        Core::execute($object, $command, $output, $notification);
        if($output){
            echo $output;
        }
        if($notification){
            echo $notification;
        }
//        $list = $this->user_list('ROLE_ADMIN');
//        $this->navigation_create($list);
    }
}