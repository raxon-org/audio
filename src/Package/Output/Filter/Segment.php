<?php

namespace Package\Raxon\Audio\Output\Filter;

use Raxon\App;
use Raxon\Module\Controller;
use Raxon\Module\Value;

class Segment extends Controller
{

    public static function filter(App $object, $destination, $filter, $options = []): mixed
    {
        if(array_key_exists('response', $options)){
            ddd($options);

//            $options['response'] = Value::remove_comment($options['response']);
        }
        return $options['response'];
    }

}