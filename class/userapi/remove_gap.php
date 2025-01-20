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
use xarDB;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments userapi remove_gap function
 * @extends MethodClass<UserApi>
 */
class RemoveGapMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Remove a gap in the celko tree
     * @author Carl P. Corliss (aka rabbitt)
     * @access private
     * @param int $startpoint starting point for removing gap
     * @param int $endpoint end point for removing gap
     * @param int $gapsize the size of the gap to remove
     * @param int $modid the module id
     * @param int $itemtype the item type
     * @param string $objectid the item id
     * @return int number of affected rows or false [0] on error
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (!isset($startpoint)) {
            $msg = $this->ml('Missing or Invalid parameter \'startpoint\'!!');
            throw new BadParameterException($msg);
        }

        // 1 is used when a node is deleted and children are attached to the parent
        if (!isset($gapsize) || $gapsize < 1) {
            $gapsize = 2;
        }

        if (!isset($endpoint) || !is_numeric($endpoint)) {
            $endpoint = null;
        }

        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();

        $sql_left  = "UPDATE $xartable[comments]
                         SET left_id = (left_id - $gapsize)
                       WHERE left_id > $startpoint";

        $sql_right = "UPDATE $xartable[comments]
                         SET right_id = (right_id - $gapsize)
                       WHERE right_id >= $startpoint";

        // if we have an endpoint, use it :)
        if (!empty($endpoint) && $endpoint !== null) {
            $sql_left   .= " AND left_id <= $endpoint";
            $sql_right  .= " AND right_id <= $endpoint";
        }
        // if we have a modid, use it
        if (!empty($modid)) {
            $sql_left   .= " AND modid = $modid";
            $sql_right  .= " AND modid = $modid";
        }
        // if we have an itemtype, use it (0 is acceptable too here)
        if (isset($itemtype)) {
            $sql_left   .= " AND itemtype = $itemtype";
            $sql_right  .= " AND itemtype = $itemtype";
        }
        // if we have an objectid, use it
        if (!empty($objectid)) {
            $sql_left   .= " AND objectid = '$objectid'";
            $sql_right  .= " AND objectid = '$objectid'";
        }

        $result1 = & $dbconn->Execute($sql_left);
        $result2 = & $dbconn->Execute($sql_right);

        if (!$result1 || !$result2) {
            return;
        }

        return $dbconn->Affected_Rows();
    }
}
