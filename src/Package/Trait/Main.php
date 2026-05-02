<?php
namespace Package\Raxon\Audio\Trait;

use Raxon\App;
use Raxon\Config;

use Raxon\Doctrine\Module\Database;
use Raxon\Doctrine\Module\Entity;
use Raxon\Exception\DirectoryCreateException;

use Raxon\Exception\FileWriteException;
use Raxon\Exception\ObjectException;
use Raxon\Exception\TemplateException;
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
        $has_frontend = false;
        $frontend_options = [];
        $backend_options = [];
        if(property_exists($options, 'frontend')){
            if(property_exists($options->frontend, 'host')){
                $has_frontend = true;
                $frontend_options = [
                    'where' => [
                        [
                            'value' => $options->frontend->host,
                            'attribute' => 'name',
                            'operator' => 'partial',
                        ]
                    ]
                ];
            }
        }
        $has_backend = false;
        if(property_exists($options, 'backend')){
            if(property_exists($options->backend, 'host')){
                $has_backend = true;
                $backend_options = [
                    'where' => [
                        [
                            'value' => $options->backend->host,
                            'attribute' => 'name',
                            'operator' => 'partial',
                        ]
                    ]
                ];
            }
        }
        if($has_frontend === false){
            throw new Exception('Frontend.host option is required and will be defined in Node/System.Host.json aborting...');
        }
        if($has_backend === false){
            throw new Exception('Backend.host option is required and will be defined in Node/System.Host.json aborting...');
        }
        $node = new Node($object);
        $class = 'System.Host';
        $response_frontend = $node->record($class, $node->role_system(), $frontend_options);
        if($response_frontend === null){
            $explode = explode('.', $options->frontend->host);
            $count_explode = count($explode);
            if($count_explode === 2){
                $domain = $explode[0];
                $extension = $explode[1];
                $class = 'System.Host';
                $node->create(
                    $class,
                    $node->role_system(),
                    [
                        'name' => ucfirst($domain) . '.' . ucfirst($extension),
                        'subdomain' => false,
                        'domain' => $domain,
                        'extension' => $extension,
                        'url' => [
                            'development' => 'https://' . $domain . '.' . 'local' . '/',
                            'staging' => 'https://' . $domain . '.' . 'staging' . '/',
                            'production' => 'https://' . $domain . '.' . $extension . '/'
                        ]
                    ]
                );
                $class = 'System.Host.Mapper';
                $mapper_options = [
                    'where' => [
                        [
                            'value' => $options->backend->host,
                            'attribute' => 'destination',
                            'operator' => 'partial',
                        ]
                    ]
                ];
                $mapper = $node->record($class, $node->role_system(), $mapper_options);
                if($mapper === null){
                    $class = 'System.Host.Mapper';
                    $node->create(
                        $class,
                        $node->role_system(),
                        [
                            'source' => $domain . '.' . 'local',
                            'destination' => $domain . '.' . $extension,
                            'extension' => $extension,
                        ]
                    );
                    $class = 'System.Host.Mapper';
                    $node->create(
                        $class,
                        $node->role_system(),
                        [
                            'source' => $domain . '.' . 'local:5003',
                            'destination' => $domain . '.' . $extension,
                            'extension' => $extension,
                        ]
                    );
                    $class = 'System.Host.Mapper';
                    $node->create(
                        $class,
                        $node->role_system(),
                        [
                            'source' => $domain . '.' . 'staging',
                            'destination' => $domain . '.' . $extension,
                            'extension' => $extension,
                        ]
                    );
                }
                $response_frontend = $node->record($class, $node->role_system(), $frontend_options);
            }
            elseif($count_explode === 3){
                $subdomain = $explode[0];
                $domain = $explode[1];
                $extension = $explode[2];
                $class = 'System.Host';
                $node->create(
                    $class,
                    $node->role_system(),
                    [
                        'name' => ucfirst($subdomain) . '.' . ucfirst($domain) . '.' . ucfirst($extension),
                        'subdomain' => $subdomain,
                        'domain' => $domain,
                        'extension' => $extension,
                        'url' => [
                            'development' => 'https://' . $subdomain . '.' . $domain . '.' . 'local' . '/',
                            'staging' => 'https://' . $subdomain . '.' . $domain . '.' . 'staging' . '/',
                            'production' => 'https://' . $subdomain . '.' . $domain . '.' . $extension . '/'
                        ]
                    ]
                );
                $class = 'System.Host.Mapper';
                $mapper_options = [
                    'where' => [
                        [
                            'value' => $options->backend->host,
                            'attribute' => 'destination',
                            'operator' => 'partial',
                        ]
                    ]
                ];
                $mapper = $node->record($class, $node->role_system(), $mapper_options);
                if($mapper === null){
                    $class = 'System.Host.Mapper';
                    $node->create(
                        $class,
                        $node->role_system(),
                        [
                            'source' => $subdomain . '.' . $domain . '.' . 'local',
                            'destination' => $subdomain . '.' . $domain . '.' . $extension,
                            'extension' => $extension,
                        ]
                    );
                    $class = 'System.Host.Mapper';
                    $node->create(
                        $class,
                        $node->role_system(),
                        [
                            'source' => $subdomain . '.' . $domain . '.' . 'local:5003',
                            'destination' => $subdomain . '.' . $domain . '.' . $extension,
                            'extension' => $extension,
                        ]
                    );
                    $class = 'System.Host.Mapper';
                    $node->create(
                        $class,
                        $node->role_system(),
                        [
                            'source' => $subdomain . '.' . $domain . '.' . 'staging',
                            'destination' => $subdomain . '.' . $domain . '.' . $extension,
                            'extension' => $extension,
                        ]
                    );
                }
                $class = 'System.Host';
                $response_frontend = $node->record($class, $node->role_system(), $frontend_options);
            } else {
                throw new Exception('Frontend host not found aborting...');
            }
        }
        $response_backend = $node->record($class, $node->role_system(), $backend_options);
        if($response_backend === null){
            $explode = explode('.', $options->backend->host);
            $count_explode = count($explode);
            if($count_explode === 2){
                $domain = $explode[0];
                $extension = $explode[1];
                $class = 'System.Host';
                $node->create(
                    $class,
                    $node->role_system(),
                    [
                        'name' => ucfirst($domain) . '.' . ucfirst($extension),
                        'subdomain' => false,
                        'domain' => $domain,
                        'extension' => $extension,
                        'url' => [
                            'development' => 'https://' . $domain . '.' . 'local' . '/',
                            'staging' => 'https://' . $domain . '.' . 'staging' . '/',
                            'production' => 'https://' . $domain . '.' . $extension . '/'
                        ]
                    ]
                );
                $class = 'System.Host.Mapper';
                $mapper_options = [
                    'where' => [
                        [
                            'value' => $options->backend->host,
                            'attribute' => 'destination',
                            'operator' => 'partial',
                        ]
                    ]
                ];
                $mapper = $node->record($class, $node->role_system(), $mapper_options);
                if($mapper === null){
                    $class = 'System.Host.Mapper';
                    $node->create(
                        $class,
                        $node->role_system(),
                        [
                            'source' => $domain . '.' . 'local',
                            'destination' => $domain . '.' . $extension,
                            'extension' => $extension,
                        ]
                    );
                    $class = 'System.Host.Mapper';
                    $node->create(
                        $class,
                        $node->role_system(),
                        [
                            'source' => $domain . '.' . 'local:5003',
                            'destination' => $domain . '.' . $extension,
                            'extension' => $extension,
                        ]
                    );
                    $class = 'System.Host.Mapper';
                    $node->create(
                        $class,
                        $node->role_system(),
                        [
                            'source' => $domain . '.' . 'staging',
                            'destination' => $domain . '.' . $extension,
                            'extension' => $extension,
                        ]
                    );
                }
                $response_backend = $node->record($class, $node->role_system(), $backend_options);
            }
            elseif($count_explode === 3){
                $subdomain = $explode[0];
                $domain = $explode[1];
                $extension = $explode[2];
                $class = 'System.Host';
                $node->create(
                    $class,
                    $node->role_system(),
                    [
                        'name' => ucfirst($subdomain) . '.' . ucfirst($domain) . '.' . ucfirst($extension),
                        'subdomain' => $subdomain,
                        'domain' => $domain,
                        'extension' => $extension,
                        'url' => [
                            'development' => 'https://' . $subdomain . '.' . $domain . '.' . 'local' . '/',
                            'staging' => 'https://' . $subdomain . '.' . $domain . '.' . 'staging' . '/',
                            'production' => 'https://' . $subdomain . '.' . $domain . '.' . $extension . '/'
                        ]
                    ]
                );
                $class = 'System.Host.Mapper';
                $mapper_options = [
                    'where' => [
                        [
                            'value' => $options->backend->host,
                            'attribute' => 'destination',
                            'operator' => 'partial',
                        ]
                    ]
                ];
                $mapper = $node->record($class, $node->role_system(), $mapper_options);
                if($mapper === null){
                    $class = 'System.Host.Mapper';
                    $node->create(
                        $class,
                        $node->role_system(),
                        [
                            'source' => $subdomain . '.' . $domain . '.' . 'local',
                            'destination' => $subdomain . '.' . $domain . '.' . $extension,
                            'extension' => $extension,
                        ]
                    );
                    $class = 'System.Host.Mapper';
                    $node->create(
                        $class,
                        $node->role_system(),
                        [
                            'source' => $subdomain . '.' . $domain . '.' . 'local:5003',
                            'destination' => $subdomain . '.' . $domain . '.' . $extension,
                            'extension' => $extension,
                        ]
                    );
                    $class = 'System.Host.Mapper';
                    $node->create(
                        $class,
                        $node->role_system(),
                        [
                            'source' => $subdomain . '.' . $domain . '.' . 'staging',
                            'destination' => $subdomain . '.' . $domain . '.' . $extension,
                            'extension' => $extension,
                        ]
                    );
                }
                $response_backend = $node->record($class, $node->role_system(), $backend_options);
            } else {
                throw new Exception('Backend host not found aborting...');
            }
        }
        if(
            property_exists($options, 'server') &&
            property_exists($options->server, 'admin')
        ){
            //nothing
        } else {
            if(!property_exists($options, 'server')){
                $options->server = (object) [
                    'admin' => 'development@' . $response_frontend['node']->domain . '.' . $response_frontend['node']->extension,
                ];
            } else {
                $options->server->admin = 'development@' . $response_frontend['node']->domain . '.' . $response_frontend['node']->extension;
            }
        }
        if(
            property_exists($options, 'server') &&
            property_exists($options->server, 'name')
        ){
            //nothing
        } else {
            $options->server->name = $response_frontend['node']->domain . '.' . $response_frontend['node']->extension;
        }
        if(
            property_exists($options, 'server') &&
            property_exists($options->server, 'alias')
        ){
            if(!is_array($options->server->alias)){
                throw new Exception('Server.alias must be an array...');
            }
            if(
                !in_array(
                    $options->backend->host,
                    $options->server->alias,
                    true
                )
            ){
                $options->server->alias[] = $options->backend->host;
            }
            if(
                !in_array(
                    $options->frontend->host,
                    $options->server->alias,
                    true
                )
            ){
                $options->server->alias[] = $options->frontend->host;
            }
        } else {
            $options->server->alias = [
                $options->backend->host,
                $options->frontend->host,
            ];
        }
        Dir::create($object->config('project.dir.domain'), Dir::CHMOD);
        File::permission($object, [
            'domain' => $object->config('project.dir.domain'),
        ]);
        $this->install_backend($response_backend, $response_frontend, $options);
        $this->install_frontend($response_backend, $response_frontend, $options);
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

    private function install_frontend($response_backend, $response_frontend, $options): void
    {
        $object = $this->object();
        $dir_read_frontend = $object->config('project.dir.vendor') .
            $object->request('package') .
            $object->config('ds') .
            'src' .
            $object->config('ds') .
            'Admin' .
            $object->config('ds')
        ;
        $dir_admin = $object->config('project.dir.domain') .
            $response_frontend['node']->name .
            $object->config('ds')
        ;
        if(!File::exist($dir_admin)){
            Dir::create($dir_admin, Dir::CHMOD);
            File::permission($object, [
                'admin' => $dir_admin,
            ]);
        }
        $dir = new Dir();
        $read = $dir->read($dir_read_frontend, true);
        if(is_array($read)){
            foreach($read as $nr => $file){
                $explode = explode($dir_read_frontend, $file->url, 2);
                if(array_key_exists(1, $explode)){
                    $file->target = $dir_admin . $explode[1];
                }
            }
            $this->install_service($read, $response_backend, $response_frontend, $options);
        }
    }

    /**
     * @throws DirectoryCreateException
     * @throws ObjectException
     * @throws TemplateException
     * @throws FileWriteException
     */
    private function install_backend($response_backend, $response_frontend, $options): void
    {
        $object = $this->object();
        $dir_read_backend = $object->config('project.dir.vendor') .
            $object->request('package') .
            $object->config('ds') .
            'src' .
            $object->config('ds') .
            'Api' .
            $object->config('ds')
        ;
        $dir_api = $object->config('project.dir.domain') .
            $response_backend['node']->name .
            $object->config('ds')
        ;
        if(!File::exist($dir_api)){
            Dir::create($dir_api, Dir::CHMOD);
            File::permission($object, [
                'api' => $dir_api,
            ]);
        }
        $dir = new Dir();
        $read = $dir->read($dir_read_backend, true);
        if(is_array($read)){
            foreach($read as $nr => $file){
                $explode = explode($dir_read_backend, $file->url, 2);
                if(array_key_exists(1, $explode)){
                    $file->target = $dir_api . $explode[1];
                }
            }
            $this->install_service($read, $response_backend, $response_frontend, $options);
        }
    }

    /**
     * @throws DirectoryCreateException
     * @throws ObjectException
     * @throws TemplateException
     * @throws FileWriteException
     * @throws Exception
     */
    private function install_service($read, $response_backend, $response_frontend, $options): void
    {
        $object = $this->object();
        if(is_array($read)){
            foreach($read as $nr => $file){
                if($file->type === Dir::TYPE){
                    if(!File::exist($file->target)){
                        Dir::create($file->target, Dir::CHMOD);
                        File::permission($object, [
                            'target' => $file->target,
                        ]);
                    }
                }
            }
            $patch = $options->patch ?? null;
            foreach($read as $nr => $file){
                if($file->type === File::TYPE){
                    $file->extension = File::extension($file->target);
                    if($file->extension === 'rax'){
                        $explode = explode('.rax', $file->target, 2);
                        if(array_key_exists(1, $explode)){
                            $file->target = $explode[0];
                            $file->original_extension = File::extension($file->target);
                            if(!File::exist($file->target) || $patch !== null){
                                $clone_options = new Data();
                                if(!property_exists($response_frontend['node'],'subdomain')){
                                    $clone_options->set('frontend.host', $response_frontend['node']->domain . '.' . $response_frontend['node']->extension);
                                } else {
                                    $clone_options->set('frontend.host', $response_frontend['node']->subdomain . '.' . $response_frontend['node']->domain . '.' . $response_frontend['node']->extension);
                                }
                                if(!property_exists($response_backend['node'],'subdomain')){
                                    $clone_options->set('backend.host', $response_backend['node']->domain . '.' . $response_backend['node']->extension);
                                } else {
                                    $clone_options->set('backend.host', $response_backend['node']->subdomain . '.' . $response_backend['node']->domain . '.' . $response_backend['node']->extension);
                                }
                                $data = new Data($object->data());
                                $clone = clone $object;
                                $clone->data(App::OPTIONS, $clone_options->data());
                                switch($file->original_extension){
                                    case 'json':
                                        echo Cli::info('Processing file:') . $file->target . PHP_EOL;
                                        $content = $clone->parse_read($file->url);
                                        if($patch !== null) {
                                            File::delete($file->target);
                                        }
                                        File::write($file->target, Core::object($content->data(), Core::JSON));
                                        File::permission($object, [
                                            'target' => $file->target,
                                        ]);
                                        //imports should be in a json file (class => url/contains)
                                        if(str_contains($file->target, 'System.Route')){
                                            $command = 'app raxon/node object import -class=System.Route -url="' . $file->target . '" -patch';
                                            Core::execute($object, $command, $output, $notification);
                                            if($output){
                                                echo $output;
                                            }
                                            if($notification){
                                                echo $notification;
                                            }
                                        }
                                        break;
                                    default:
                                        echo Cli::info('Processing file:') . $file->target . PHP_EOL;
                                        $clone_options->set('source', $file->url);
                                        $flags = App::flags($clone);
                                        $parse = new Parse($clone, $data, $flags, $clone_options->data());
                                        $read = File::read($file->url);
                                        $content = $parse->compile($read, $data);
                                        if($patch !== null) {
                                            File::delete($file->target);
                                        }
                                        File::write($file->target, $content);
                                        File::permission($object, [
                                            'target' => $file->target,
                                        ]);
                                        break;
                                }
                            }
                        }
                    } else {
                        if($patch !== null) {
                            File::delete($file->target);
                        }
                        echo Cli::info('Processing file:') . $file->target . PHP_EOL;
                        File::copy($file->url, $file->target);
                        File::permission($object, [
                            'target' => $file->target,
                        ]);
                    }
                }
            }
        }
    }
}