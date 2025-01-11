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

use Xaraya\Modules\Comments\Defines;
use Xaraya\Modules\MethodClass;
use xarDB;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments userapi get_childcountlist function
 */
class GetChildcountlistMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Get the number of children comments for a list of comment ids
     * @author mikespub
     * @access public
     * @param int $left the left limit for the list of comment ids
     * @param int $right the right limit for the list of comment ids
     * @param int $moduleid /$itemtype/$itemid of the module selected
     * @return array the number of child comments for each comment id,
     * or raise an exception and return false.
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (!isset($left) || !is_numeric($left) || !isset($right) || !is_numeric($right)) {
            $msg = xarML('Invalid #(1)', 'left/right');
            throw new BadParameterException($msg);
        }

        $dbconn = xarDB::getConn();
        $xartable = & xarDB::getTables();

        $bind = [(int) $left, (int) $right, Defines::STATUS_ON, (int) $moduleid, (int) $itemid, (int) $itemtype];

        $sql = "SELECT P1.id, COUNT(P2.id) AS numitems"
            . " FROM $xartable[comments] AS P1, $xartable[comments] AS P2"
            . " WHERE P1.module_id = P2.module_id AND P1.itemtype = P2.itemtype AND P1.itemid = P2.itemid"
            . " AND P2.left_id >= P1.left_id AND P2.left_id <= P1.right_id"
            . " AND P1.left_id >= ? AND P1.right_id <= ?"
            . " AND P2.status = ?"
            . " AND P1.module_id = ? AND P1.itemid = ? AND P1.itemtype = ?"
            . " GROUP BY P1.id";

        $result = $dbconn->Execute($sql, $bind);
        if (!$result) {
            return;
        }

        if ($result->EOF) {
            return [];
        }

        $count = [];
        while (!$result->EOF) {
            [$id, $numitems] = $result->fields;
            // return total count - 1 ... the -1 is so we don't count the comment root.
            $count[$id] = $numitems - 1;
            $result->MoveNext();
        }
        $result->Close();

        return $count;
    }
}
