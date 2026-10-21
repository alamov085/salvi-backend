<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 29.06.2019
 * Time: 22:50
 */

namespace api\schema;

use GraphQL\Type\Definition\ObjectType;

/**
 * Class BasicType
 * @package api\schema
 */
class BasicType extends ObjectType
{
    const FIELD_TYPE = 'type';
    const FIELD_RESOLVE = 'resolve';

    public function __construct(array $config, $pagination = false)
    {
        if ($pagination) {
            $config['name'] = $this->tryInferName() . '_items';
        }
        parent::__construct($config);
    }
}
