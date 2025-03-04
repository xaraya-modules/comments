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
use xarDB;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments userapi get_node_root function
 * @extends MethodClass<UserApi>
 */
class GetNodeRootMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Grab the id, left and right values for the
     * root node of a particular comment.
     * @author Carl P. Corliss (aka rabbitt)
     * @access public
     * @param array<mixed> $args
     *     int modid      The module that comment is attached to
     *     int objectid   The particular object within that module
     *     int itemtype   The itemtype of that object
     * @return array|void an array containing the left and right values or an
     * empty array if the comment_id specified doesn't exist
     * @see UserApi::getNodeRoot()
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        $exception = false;

        if (!isset($modid) || empty($modid)) {
            $msg = $this->ml('Missing or Invalid parameter \'modid\'!!');
            throw new BadParameterException($msg);
        }

        if (!isset($objectid) || empty($objectid)) {
            $msg = $this->ml('Missing or Invalid parameter \'objectid\'!!');
            throw new BadParameterException($msg);
        }

        if ($exception) {
            return;
        }

        if (empty($itemtype)) {
            $itemtype = 0;
        }

        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();

        // grab the root node's id, left and right values
        // based on the objectid/modid pair
        $sql = "SELECT  id, left_id, right_id
                  FROM  $xartable[comments]
                 WHERE  modid=?
                   AND  itemtype=?
                   AND  objectid=?
                   AND  status=?";
        // objectid is still a string for now
        $bindvars = [(int) $modid, (int) $itemtype, (string) $objectid, (int) Defines::STATUS_ROOT_NODE];

        $result = & $dbconn->Execute($sql, $bindvars);

        if (!$result) {
            return;
        }

        $count = $result->RecordCount();

        assert($count == 1 | $count == 0);

        if ($result->first()) {
            $node = $result->GetRowAssoc(false);
        } else {
            $node = [];
        }
        $result->Close();

        return $node;
    }
}
