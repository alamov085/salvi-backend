<?php
/**
 * Created by PhpStorm.
 * User: tik
 * Date: 08.05.2020
 * Time: 12:51
 */

namespace common\dto;

use yii\base\Model;

/**
 * Class CartResponseDto
 * @package common\dto
 */
class CartResponseDto extends Model
{
    public $result = false;
    public $total = 0;
}
