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
 * comments userapi get_object_list function
 * @extends MethodClass<UserApi>
 */
class GetObjectListMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Acquire a list of objectid's associated with a
     * particular Module ID in the comments table
     * @author Carl P. Corliss (aka rabbitt)
     * @access private
     * @param int $modid the id of the module that the objectids are associated with
     * @param int $itemtype the item type that these nodes belong to
     * @return array A list of objectid's
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (!isset($modid) || empty($modid)) {
            $msg = $this->ml(
                'Missing #(1) for #(2) function #(3)() in module #(4)',
                'modid',
                'userapi',
                'get_object_list',
                'comments'
            );
            throw new BadParameterException($msg);
        }

        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();
        $sql     = "SELECT DISTINCT objectid AS pageid
                               FROM $xartable[comments]
                              WHERE modid = $modid";

        if (isset($itemtype) && is_numeric($itemtype)) {
            $sql .= " AND itemtype=$itemtype";
        }

        $result = & $dbconn->Execute($sql);
        if (!$result) {
            return;
        }

        // if it's an empty set, return array()
        if ($result->EOF) {
            return [];
        }

        // zip through the list of results and
        // create the return array
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $ret[$row['pageid']]['pageid'] = $row['pageid'];
            $result->MoveNext();
        }
        $result->Close();

        return $ret;
    }
}
