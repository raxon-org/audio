<?php
namespace Package\Raxon\Audio\Output\Filter\Stt;

use Raxon\App;
use Raxon\Module\Controller;

class Segment extends Controller {
    const DIR = __DIR__ . '/';

    public static function segment_filter(App $object, $destination, $filter, $response=null): array | object
    {
//        d($destination);
//        d($filter);
//        d($response);
        return $response;
    }
}