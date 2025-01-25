<?php

/**
 * @package modules\comments
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Comments\AdminApi;


use Xaraya\Modules\Comments\AdminApi;
use Xaraya\Modules\MethodClass;
use xarMod;
use xarSecurity;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments adminapi remove_module function
 * @extends MethodClass<AdminApi>
 */
class RemoveModuleMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Called from the core when a module is removed.
     * Delete the appertain comments when the module is hooked.
     * @see AdminApi::removeModule()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var AdminApi $adminapi */
        $adminapi = $this->adminapi();

        // When called via hooks, we should get the real module name from objectid
        // here, because the current module is probably going to be 'modules' !!!
        if (!isset($objectid) || !is_string($objectid)) {
            $msg = $this->ml('Invalid Parameter');
            throw new BadParameterException($msg);
        }

        $modid = xarMod::getRegID($objectid);
        if (empty($modid)) {
            $msg = $this->ml('Invalid Parameter');
            throw new BadParameterException($msg);
        }

        // TODO: re-evaluate this for hook calls !!
        // Security check - important to do this as early on as possible to
        // avoid potential security holes or just too much wasted processing
        // if(!xarSecurity::check('DeleteHitcountItem',1,'Item',"All:All:$objectid")) return;

        // FIXME: we need to remove the comments for items of all types here, so a direct DB call
        //        would be better than this "delete recursively" trick
        $adminapi->delete_module_nodes(['modid' => $modid]);
        return $extrainfo;
    }
}
