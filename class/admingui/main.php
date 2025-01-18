<?php

/**
 * @package modules\comments
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Comments\AdminGui;

use Xaraya\Modules\Comments\AdminGui;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarModVars;
use xarController;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments admin main function
 * @extends MethodClass<AdminGui>
 */
class MainMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Overview Menu
     */
    public function __invoke(array $args = [])
    {
        if (!$this->sec()->checkAccess('AdminComments')) {
            return;
        }

        if (xarModVars::get('modules', 'disableoverview') == 0) {
            return [];
        } else {
            $this->ctl()->redirect($this->mod()->getURL('admin', 'view'));
        }
        return true;
    }
}
