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

use Xaraya\Modules\MethodClass;
use xarModHooks;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments userapi formhooks function
 */
class FormhooksMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Sets up any formaction / formdisplay hooks
     */
    public function __invoke(array $args = [])
    {
        $hooks = [];
        $hooks['formaction']              = xarModHooks::call('item', 'formaction', '', [], 'comments');
        $hooks['formdisplay']             = xarModHooks::call('item', 'formdisplay', '', [], 'comments');

        if (empty($hooks['formaction'])) {
            $hooks['formaction'] = '';
        } elseif (is_array($hooks['formaction'])) {
            $hooks['formaction'] = join('', $hooks['formaction']);
        }

        if (empty($hooks['formdisplay'])) {
            $hooks['formdisplay'] = '';
        } elseif (is_array($hooks['formdisplay'])) {
            $hooks['formdisplay'] = join('', $hooks['formdisplay']);
        }

        return $hooks;
    }
}
