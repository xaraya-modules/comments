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
use BadParameterException;

/**
 * comments userapi get_count function
 * @extends MethodClass<UserApi>
 */
class GetCountMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Get the number of comments for a module item
     * @author mikespub
     * @access public
     * @param int $modid the id of the module that these nodes belong to
     * @param int $itemtype the item type that these nodes belong to
     * @param int $objectid the id of the item that these nodes belong to
     * @param int $status the status of the comment: 2 - active, 1 - inactive, 3 - root node
     * @return int|void the number of comments for the particular modid/objectid pair,
     * or raise an exception and return false.
     * @see UserApi::getCount()
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        $exception = false;

        if (!isset($modid) || empty($modid)) {
            $msg = $this->ml(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                'modid',
                'userapi',
                'get_count',
                'comments'
            );
            throw new BadParameterException($msg);
        }

        if (!isset($status) || !is_numeric($status)) {
            $status = Defines::STATUS_ON;
        }

        if (!isset($objectid) || empty($objectid)) {
            $msg = $this->ml(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                'objectid',
                'userapi',
                'get_count',
                'comments'
            );
            throw new BadParameterException($msg);
        }

        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();

        $sql = "SELECT  COUNT(id) as numitems
                  FROM  $xartable[comments]
                 WHERE  objectid = ? AND modid = ?
                   AND  status = ?";
        // Note: objectid is not an integer here (yet ?)
        $bindvars = [(string) $objectid, (int) $modid, (int) $status];

        if (isset($itemtype) && is_numeric($itemtype)) {
            $sql .= " AND itemtype = ?";
            $bindvars[] = (int) $itemtype;
        }

        $result = & $dbconn->Execute($sql, $bindvars);
        if (!$result) {
            return;
        }

        if (!$result->first()) {
            return 0;
        }

        [$numitems] = $result->fields;

        $result->Close();

        return $numitems;
    }
}
