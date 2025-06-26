<?php

namespace support;

use app\model\User;

/**
 * Class Request
 * @package support
 *
 * @property User|null $user The authenticated user instance.
 */
class Request extends \Webman\Http\Request
{

}