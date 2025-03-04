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
use Xaraya\Modules\Comments\Defines;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarDB;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments userapi modcounts function
 * @extends MethodClass<UserApi>
 */
class ModcountsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get the list of modules and itemtypes for the items that we're commenting on
     * @param string status optional status to count: ALL (minus root nodes), ACTIVE, INACTIVE
     * @param int modid optional module id you want to count for
     * @param int itemtype optional item type you want to count for
     * @return array|void $array[$modid][$itemtype] = array('items' => $numitems,'comments' => $numcomments);
     * @see UserApi::modcounts()
     */
    public function __invoke(array $args = [])
    {
        // Get arguments from argument array
        extract($args);

        // Security check
        if (!$this->sec()->checkAccess('ReadComments')) {
            return;
        }

        if (empty($status)) {
            $status = 'all';
        }
        $status = strtolower($status);

        // Database information
        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();
        $commentstable = $xartable['comments'];

        switch ($status) {
            case 'active':
                $where_status = "status = " . Defines::STATUS_ON;
                break;
            case 'inactive':
                $where_status = "status = " . Defines::STATUS_OFF;
                break;
            default:
            case 'all':
                $where_status = "status != " . Defines::STATUS_ROOT_NODE;
        }
        if (!empty($modid)) {
            $where_mod = " AND module_id = $moduleid";
            if (isset($itemtype)) {
                $where_mod .= " AND itemtype = $itemtype";
            }
        } else {
            $where_mod = '';
        }

        // Get items
        $sql = "SELECT module_id, itemtype, COUNT(*), COUNT(DISTINCT itemid)
                FROM $commentstable
                WHERE $where_status $where_mod
                GROUP BY module_id, itemtype";

        $result = $dbconn->Execute($sql);
        if (!$result) {
            return;
        }

        $modlist = [];
        while ($result->next()) {
            [$modid, $itemtype, $numcomments, $numitems] = $result->fields;
            if (!isset($modlist[$modid])) {
                $modlist[$modid] = [];
            }
            $modlist[$modid][$itemtype] = ['items' => $numitems, 'comments' => $numcomments];
        }
        $result->close();

        return $modlist;
    }
}
