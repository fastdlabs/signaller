<?php
/**
 * @author    jan huang <bboyjanhuang@gmail.com>
 * @copyright 2018
 *
 * @see      https://www.github.com/fastdlabs
 * @see      http://www.fastdlabs.com/
 */

/**
 * @return \FastD\Signaller\Signaller
 */
function signaller()
{
    if (function_exists('app') && app()->has('signaller')) {
        return app()->get('signaller');
    }

    return new \FastD\Signaller\Signaller();
}
