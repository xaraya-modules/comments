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
use BadParameterException;

/**
 * comments userapi create_gap function
 * @extends MethodClass<UserApi>
 */
class CreateGapMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Open a gap in the celko tree for inserting nodes
     * @author Carl P. Corliss (aka rabbitt)
     * @access private
     * @param int $startpoint the point at wich the node will be inserted
     * @param int $endpoint end point for creating gap (used mostly for moving branches around)
     * @param int $gapsize the size of the gap to make (defaults to 2 for inserting a single node)
     * @param int $modid the module id
     * @param int $itemtype the item type
     * @param string $objectid the item id
     * @return int|void number of affected rows or false [0] on error
     * @see UserApi::createGap()
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (!isset($startpoint)) {
            $msg = $this->ml('Missing or Invalid parameter \'startpoint\'!!');
            throw new BadParameterException($msg);
        }

        if (!isset($endpoint) || !is_numeric($endpoint)) {
            $endpoint = null;
        }

        if (!isset($gapsize) || $gapsize <= 1) {
            $gapsize = 2;
        }

        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();

        $sql_left  = "UPDATE $xartable[comments]
                         SET left_id = (left_id + $gapsize)
                       WHERE left_id > $startpoint";

        $sql_right = "UPDATE $xartable[comments]
                         SET right_id = (right_id + $gapsize)
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

        // see if we support transactions here
        if ($dbconn->hasTransactions) {
            // try 3 times with increasing delay
            for ($i = 0; $i < 3; $i++) {
                if ($i > 0) {
                    // sleep 10 msec the second time, 100 msec the third time
                    $delay = 1000 * pow(10, $i);
                    usleep($delay);
                }
                // start the transaction
                $dbconn->StartTrans();
                // note: we don't do explicit row locking here, because it takes longer
                //       and we end up with more deadlocks (ask the Postgres people why ?)
                // start by increasing the right side
                $result = & $dbconn->Execute($sql_right);
                if ($result) {
                    // this should at least affect the parent
                    $affected = $dbconn->Affected_Rows();
                    // then increase the left side if necessary
                    $result = & $dbconn->Execute($sql_left);
                }
                // if the transaction succeeded
                if ($dbconn->CompleteTrans()) {
                    // return the number of affected rows
                    return $affected;
                }
                // otherwise we roll back and try again
            }
            return;
        } else {
            // start by increasing the right side
            $result = & $dbconn->Execute($sql_right);
            if ($result) {
                // this should at least affect the parent
                $affected = $dbconn->Affected_Rows();
                // then increase the left side if necessary
                $result = & $dbconn->Execute($sql_left);
            }
            if (!$result) {
                return;
            }
            return $affected;
        }
    }
}
