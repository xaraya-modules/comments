<?php

/**
 * @package modules\comments
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Comments\UserApi;

use Xaraya\Modules\Comments\UserApi;
use Xaraya\Modules\MethodClass;

/**
 * comments userapi get_hostname function
 * @extends MethodClass<UserApi>
 */
class GetHostnameMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Retrieves the host name of the commentor
     * @see UserApi::getHostname()
     */
    public function __invoke(array $args = [])
    {
        $forwarded = $this->ctl()->getServerVar('HTTP_X_FORWARDED_FOR');
        if (!empty($forwarded)) {
            $hostname = preg_replace('/,.*/', '', $forwarded);
        } else {
            $hostname = $this->ctl()->getServerVar('REMOTE_ADDR');
        }
        return $hostname;
    }
}
