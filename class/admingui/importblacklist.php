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
use Xaraya\Modules\Comments\AdminApi;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarMod;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments admin importblacklist function
 * @extends MethodClass<AdminGui>
 */
class ImportblacklistMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * This is a standard function to update the configuration parameters of the
     * module given the information passed back by the modification form
     * @see AdminGui::importblacklist()
     */
    public function __invoke(array $args = [])
    {
        /** @var AdminApi $adminapi */
        $adminapi = $this->adminapi();
        if (!$this->sec()->checkAccess('AdminComments')) {
            return;
        }
        if (!$adminapi->import_blacklist()) {
            return;
        }
        return [];
    }
}
