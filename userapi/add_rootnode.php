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
 * comments userapi add_rootnode function
 * @extends MethodClass<UserApi>
 */
class AddRootnodeMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Creates a root node for the specified objectid/modid
     * @author Carl P. Corliss (aka rabbitt)
     * @access private
     * @param int modid      The module that comment is attached to
     * @param int objectid   The particular object within that module
     * @param int itemtype   The itemtype of that object
     * @return int|void the id of the node that was created so it can be used as a parent id
     * @todo get rid of this notion of root node ?
     * @see UserApi::addRootnode()
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        $exception = false;

        if (!isset($modid) || empty($modid)) {
            $msg = $this->ml('Missing or Invalid parameter \'modid\'!!');
            throw new BadParameterException($msg);
            $exception |= true;
        }

        if (!isset($objectid) || empty($objectid)) {
            $msg = $this->ml('Missing or Invalid parameter \'objectid\'!!');
            throw new BadParameterException($msg);
            $exception |= true;
        }

        if ($exception) {
            return;
        }

        if (empty($itemtype)) {
            $itemtype = 0;
        }

        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();

        $commenttable = $xartable['comments'];

        // Each (modid + itemtype + objectid) has its own Celko tree now,
        // so we start over from 0 for the left and right positions
        $maxright = 0;

        // Set left and right values;
        $left  = $maxright + 1;
        $right = $maxright + 2;
        $cdate = time();

        // Get next ID in table.  For databases like MySQL, this value will
        // be zero but the auto_increment type on the column will create
        // the correct value.
        $nextId = $dbconn->GenId($commenttable);

        $sql = "INSERT INTO $xartable[comments]
                  (id, parent_id, text,
                   title, author, left_id,
                   right_id, status, objectid,
                   modid, itemtype,
                   hostname, date )
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $bindvars = [ $nextId,
            0,
            'This is for internal use and works only as a place holder. PLEASE do NOT delete this comment as it could have detrimental effects on the consistency of the comments table.',
            'ROOT NODE - PLACEHOLDER. DO NOT DELETE!',
            1,
            $left,
            $right,
            Defines::STATUS_ROOT_NODE,
            $objectid,
            $modid,
            $itemtype,
            '',
            $cdate,
        ];

        $result = & $dbconn->Execute($sql, $bindvars);

        if (!$result) {
            return;
        }

        // Return the id of the created record just now.
        $id = $dbconn->PO_Insert_ID($xartable['comments'], 'id');

        return $id;
    }
}
