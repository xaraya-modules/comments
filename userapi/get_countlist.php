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
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments userapi get_countlist function
 * @extends MethodClass<UserApi>
 */
class GetCountlistMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Get the number of comments for a list of module items
     * @author mikespub
     * @access public
     * @param array<mixed> $args
     * @param int $modid the id of the module that these nodes belong to
     * @param int $itemtype the item type that these nodes belong to
     * @param array $objectids (optional) the list of ids of the items that these nodes belong to
     * @param int $startdate (optional) comments posted at startdate or later
     * @return array|bool|void the number of comments for the particular modid/objectids pairs,
     * or raise an exception and return false.
     * @see UserApi::getCountlist()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        // $modid, $objectids

        $exception = false;

        if (!isset($modid) || empty($modid)) {
            $msg = $this->ml(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                'modid',
                'userapi',
                'get_countlist',
                'comments'
            );
            throw new BadParameterException($msg);
            $exception |= true;
        }


        if (!isset($objectids) || !is_array($objectids)) {
            $msg = $this->ml(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                'objectids',
                'userapi',
                'get_countlist',
                'comments'
            );
            throw new BadParameterException($msg);
            $exception |= true;
        }

        if ($exception) {
            return false;
        }

        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();

        $sql = "SELECT  objectid, COUNT(id) as numitems
                  FROM  $xartable[comments]
                 WHERE  modid=$modid
                   AND  status=" . Defines::STATUS_ON;

        if (isset($itemtype) && is_numeric($itemtype)) {
            $sql .= " AND itemtype=$itemtype";
        }

        if (isset($objectids) && is_array($objectids)) {
            $sql .= " AND  objectid IN ('" . join("', '", $objectids) . "')";
        }

        if (!empty($startdate) && is_numeric($startdate)) {
            $sql .= " AND date>=$startdate";
        }

        $sql .= " GROUP BY  objectid";

        $result = & $dbconn->Execute($sql);
        if (!$result) {
            return;
        }

        $count = [];
        while ($result->next()) {
            [$id, $numitems] = $result->fields;
            $count[$id] = $numitems;
        }
        $result->Close();

        return $count;
    }
}
