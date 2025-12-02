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
use Exception;

/**
 * comments userapi add function
 * @extends MethodClass<UserApi>
 */
class AddMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Adds a comment to the database based on the itemid/moduleid pair
     * @author Carl P. Corliss (aka rabbitt)
     * @access public
     * @param array<mixed> $args
     * @var int $moduleid   the module id
     * @var int $itemtype   the item type
     * @var string $itemid     the item id
     * @var int $parent_id        the parent id
     * @var string $title    the title (title) of the comment
     * @var string $comment    the text (body) of the comment
     * @var int $postanon   whether or not this post is gonna be anonymous
     * @var int $author     user id of the author (for API access)
     * @var string $hostname   hostname (for API access)
     * @var \datetime $date       date of the comment (for API access)
     * @var int $id        comment id (for API access - import only)
     * @return int|void the id of the new comment
     * @see UserApi::add()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var UserApi $userapi */
        $userapi = $this->userapi();

        if (!isset($moduleid) || empty($moduleid)) {
            $msg = $this->ml(
                'Missing #(1) for #(2) function #(3)() in module #(4)',
                'moduleid',
                'userapi',
                'add',
                'comments'
            );
            throw new BadParameterException($msg);
        }

        if (empty($itemtype) || !is_numeric($itemtype)) {
            $itemtype = 0;
        }

        if (!isset($itemid) || empty($itemid)) {
            $msg = $this->ml(
                'Missing #(1) for #(2) function #(3)() in module #(4)',
                'itemid',
                'userapi',
                'add',
                'comments'
            );
            throw new BadParameterException($msg);
        }

        if (!isset($parent_id) || empty($parent_id)) {
            $parent_id = 0;
        }

        if (!isset($title) || empty($title)) {
            $msg = $this->ml(
                'Missing #(1) for #(2) function #(3)() in module #(4)',
                'title',
                'userapi',
                'add',
                'comments'
            );
            throw new BadParameterException($msg);
        }

        if (!isset($comment) || empty($comment)) {
            $msg = $this->ml(
                'Missing #(1) for #(2) function #(3)() in module #(4)',
                'comment text',
                'userapi',
                'add',
                'comments'
            );
            throw new BadParameterException($msg);
        }

        if (!isset($postanon) || empty($postanon)) {
            $postanon = 0;
        }
        if (!isset($author)) {
            $author = $this->user()->getId();
        }

        if (!isset($hostname)) {
            $forwarded = $this->req()->getServerVar('HTTP_X_FORWARDED_FOR');
            if (!empty($forwarded)) {
                $hostname = preg_replace('/,.*/', '', $forwarded);
            } else {
                $hostname = $this->req()->getServerVar('REMOTE_ADDR');
            }
        }

        // Lets check the blacklist first before we process.
        // If the comment does not pass, we will return an exception
        // Perhaps in the future we can store the comment for later
        // review, but screw it for now...
        if ($this->mod()->getVar('useblacklist') == true) {
            $items = $userapi->get_blacklist();
            foreach ($items as $item) {
                if (preg_match("/$item[domain]/i", $comment)) {
                    $msg = $this->ml('Your entry has triggered comments moderation due to suspicious URL entry');
                    throw new BadParameterException($msg);
                }
            }
        }

        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();

        // parentid == zero then we need to find the root nodes
        // left and right values cuz we're adding the new comment
        // as a top level comment
        if ($parent_id == 0) {
            $root_lnr = $userapi->get_node_root(
                ['moduleid' => $moduleid,
                    'itemid'   => $itemid,
                    'itemtype' => $itemtype, ]
            );

            // ok, if the there was no root left and right values then
            // that means this is the first comment for this particular
            // moduleid/itemid combo -- so we need to create a dummy (root)
            // comment from which every other comment will branch from
            if (!count($root_lnr)) {
                $parent_id = $userapi->add_rootnode(
                    ['moduleid' => $moduleid,
                        'itemid'   => $itemid,
                        'itemtype' => $itemtype, ]
                );
            } else {
                $parent_id = $root_lnr['id'];
            }
        }

        // parent_id should now always have a value
        assert($parent_id != 0 && !empty($parent_id));

        // grab the left and right values from the parent
        $parent_lnr = $userapi->get_node_lrvalues(
            ['id' => $parent_id]
        );

        // there should be -at-least- one affected row -- if not
        // then raise an exception. btw, at the very least,
        // the 'right' value of the parent node would have been affected.
        if (!$userapi->create_gap(
            ['startpoint' => $parent_lnr['right_id'],
                'moduleid'   => $moduleid,
                'itemid'     => $itemid,
                'itemtype'   => $itemtype, ]
        )) {
            $msg  = $this->ml('Unable to create gap in tree for comment insertion! Comments table has possibly been corrupted.');
            $msg .= $this->ml('Please seek help on the public-developer list xaraya_public-dev@xaraya.com, or in the #support channel on Xaraya\'s IRC network.');
            throw new Exception($msg);
        }

        $cdate    = time();
        $left     = $parent_lnr['right_id'];
        $right    = $left + 1;
        if ($moduleid == $this->mod()->getID('comments')) {
            $status   = $this->mod()->getVar('AuthorizeComments') ? Defines::STATUS_OFF : Defines::STATUS_ON;
        } elseif (!isset($status) || !is_numeric($status)) {
            // no reasonable default for this, so we'll throw an error
            $msg = $this->ml('Missing or invalid status parameter');
            throw new BadParameterException($msg);
        }

        /*if (!isset($id)) {
            $id = $dbconn->GenId($xartable['comments']);
        }*/


        $object = $this->data()->getObject([
            'name' => 'comments_comments',
        ]);

        if (!is_object($object)) {
            return;
        }

        $fields = [
            'text',
            'module_id',
            'itemtype',
            'itemid',
            'author',
            'title',
            'hostname',
            'left_id',
            'right_id',
            'parent_url',
            'parent_id',
            'status', ];

        $text = $comment;
        $left_id = $left;
        $right_id = $right;

        foreach ($fields as $field) {
            $object->properties[$field]->setValue($$field);
        }
        $bdate = (isset($date)) ? $date : $cdate;
        $object->properties['date']->setValue($bdate);
        $bpostanon = isset($postanon) ? 0 : 1;
        $object->properties['anonpost']->setValue($bpostanon);

        $id = $object->createItem();

        /*$sql = "INSERT INTO $xartable[comments]
                    (id,
                     module_id,
                     itemtype,
                     itemid,
                     author,
                     title,
                     date,
                     hostname,
                     text,
                     left_id,
                     right_id,
                     parent_id,
                     status,
                     anonpost)
              VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $bdate = (isset($date)) ? $date : $cdate;
        $bpostanon = (empty($postanon)) ? 0 : 1;
        $bindvars = array($id, $moduleid, $itemtype, $itemid, $author, $title, $bdate, $hostname, $comment, $left, $right, $parent_id, $status, $bpostanon);

        $result = &$dbconn->Execute($sql,$bindvars);*/

        if (!is_numeric($id)) {
            return;
        } else {
            //$id = $dbconn->PO_Insert_ID($xartable['comments'], 'id');
            // CHECKME: find some cleaner way to update the page cache if necessary
            if (function_exists('xarOutputFlushCached')
                && $this->mod('cachemanager')->getVar('FlushOnNewComment')) {
                $modinfo = $this->mod()->getInfo($moduleid);
                xarOutputFlushCached("$modinfo[name]-");
                xarOutputFlushCached("comments-block");
            }
            // Call create hooks for categories, hitcount etc.
            $args['module'] = 'comments';
            $args['itemtype'] = 0;
            $args['itemid'] = $id;
            // pass along the current module & itemtype for pubsub (urgh)
            // FIXME: handle 2nd-level hook calls in a cleaner way - cfr. categories navigation, comments add etc.
            $args['id'] = 0; // dummy category
            $modinfo = $this->mod()->getInfo($moduleid);
            $args['current_module'] = $modinfo['name'];
            $args['current_itemtype'] = $itemtype;
            $args['current_itemid'] = $itemid;
            $this->mod()->callHooks('item', 'create', $id, $args);
            return $id;
        }
    }
}
