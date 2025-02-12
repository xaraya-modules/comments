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


use Xaraya\Modules\Comments\AdminApi;
use Xaraya\Modules\Comments\UserApi;
use Xaraya\Modules\MethodClass;
use xarMod;
use xarModHooks;
use xarDB;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments adminapi delete_node function
 * @extends MethodClass<AdminApi>
 */
class DeleteNodeMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Delete a node from the tree and reassign it's children to it's parent
     * @author Carl P. Corliss (aka rabbitt)
     * @access private
     * @param int $node the id of the node to delete
     * @param int $parent_id the deletion node's parent id (used to reassign the children)
     * @return bool|null true on success, false otherwise
     * @see AdminApi::deleteNode()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var UserApi $userapi */
        $userapi = $this->userapi();

        if (empty($node)) {
            $msg = $this->ml('Missing or Invalid comment id!');
            throw new BadParameterException($msg);
        }

        if (empty($parent_id)) {
            $msg = $this->ml('Missing or Invalid parent id!');
            throw new BadParameterException($msg);
        }

        // Grab the deletion node's left and right values
        $comments = $userapi->get_one(['id' => $node]
        );
        $left = $comments[0]['left_id'];
        $right = $comments[0]['right_id'];
        $modid = $comments[0]['modid'];
        $itemtype = $comments[0]['itemtype'];
        $objectid = $comments[0]['objectid'];

        // Call delete hooks for categories, hitcount etc.
        $args['module'] = 'comments';
        $args['itemtype'] = $itemtype;
        $args['itemid'] = $node;
        xarModHooks::call('item', 'delete', $node, $args);

        //Now delete the item ....
        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();

        // delete the node
        $sql = "DELETE
                  FROM  $xartable[comments]
                 WHERE  id = ?";
        $bindvars1 = [$node];
        // reset all parent id's == deletion node's id to that of
        // the deletion node's parent id
        $sql2 = "UPDATE $xartable[comments]
                    SET parent_id = ?
                  WHERE parent_id = ?";
        $bindvars2 = [$parent_id, $node];
        if (!$dbconn->Execute($sql, $bindvars1)) {
            return;
        }

        if (!$dbconn->Execute($sql2, $bindvars2)) {
            return;
        }

        // Go through and fix all the l/r values for the comments
        // First we subtract 1 from all the deletion node's children's left and right values
        // and then we subtract 2 from all the nodes > the deletion node's right value
        // and <= the max right value for the table
        if ($right > $left + 1) {
            $userapi->remove_gap(['startpoint' => $left,
                'endpoint'   => $right,
                'modid'      => $modid,
                'objectid'   => $objectid,
                'itemtype'   => $itemtype,
                'gapsize'    => 1, ]);
        }
        $userapi->remove_gap(['startpoint' => $right,
            'modid'      => $modid,
            'objectid'   => $objectid,
            'itemtype'   => $itemtype,
            'gapsize'    => 2, ]);



        return $dbconn->Affected_Rows();
    }
}
