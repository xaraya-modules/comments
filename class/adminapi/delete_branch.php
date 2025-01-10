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

use Xaraya\Modules\MethodClass;
use xarMod;
use xarDB;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments adminapi delete_branch function
 */
class DeleteBranchMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Delete a branch from the tree
     * @author Carl P. Corliss (aka rabbitt)
     * @access private
     * @param int $node the id of the node to delete
     * @return bool true on success, false otherwise
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (empty($node)) {
            $msg = xarML('Invalid or Missing Parameter \'node\'!!');
            throw new BadParameterException($msg);
        }

        // Grab the deletion node's left and right values
        $comments = xarMod::apiFunc(
            'comments',
            'user',
            'get_one',
            ['id' => $node]
        );
        $left = $comments[0]['left_id'];
        $right = $comments[0]['right_id'];
        $modid = $comments[0]['modid'];
        $itemtype = $comments[0]['itemtype'];
        $objectid = $comments[0]['objectid'];

        $dbconn = xarDB::getConn();
        $xartable = & xarDB::getTables();

        $sql = "DELETE
                  FROM  $xartable[comments]
                 WHERE  left_id    >= $left
                   AND  right_id   <= $right
                   AND  modid    = $modid
                   AND  itemtype = $itemtype
                   AND  objectid = '$objectid'";

        $result = & $dbconn->Execute($sql);

        if (!$dbconn->Affected_Rows()) {
            return false;
        }

        // figure out the adjustment value for realigning the left and right
        // values of all the comments
        $adjust_value = (($right - $left) + 1);


        // Go through and fix all the l/r values for the comments
        if (xarMod::apiFunc('comments', 'user', 'remove_gap', ['startpoint' => $left,
            'modid'      => $modid,
            'objectid'   => $objectid,
            'itemtype'   => $itemtype,
            'gapsize'    => $adjust_value, ])) {
            return $dbconn->Affected_Rows();
        }
    }
}
