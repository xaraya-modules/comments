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
use Xaraya\Modules\Comments\Renderer;
use Xaraya\Modules\MethodClass;
use xarMod;
use xarDB;
use xarUser;
use xarModVars;
use xarLocale;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * comments userapi get_multiple function
 * @extends MethodClass<UserApi>
 */
class GetMultipleMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Get a single comment or a list of comments. Depending on the parameters passed
     * you can retrieve either a single comment, a complete list of comments, a complete
     * list of comments down to a certain depth or, lastly, a specific branch of comments
     * starting from a specified root node and traversing the complete branch
     * if you leave out the objectid, you -must- at least specify the author id
     * @author Carl P. Corliss (aka rabbitt)
     * @access public
     * @param int $moduleid the id of the module that these nodes belong to
     * @param int $itemtype the item type that these nodes belong to
     * @param int $objectid (optional) the id of the item that these nodes belong to
     * @param int $id (optional) the id of a comment
     * @param int $status (optional) only pull comments with this status
     * @param int $author (optional) only pull comments by this author
     * @param bool $reverse (optional) reverse sort order from the database
     * @return array an array of comments or an empty array if no comments
     * found for the particular modid/objectid pair, or raise an
     * exception and return false.
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (!isset($moduleid) || empty($moduleid)) {
            $msg = $this->translate(
                'Invalid #(1) [#(2)] for #(3) function #(4)() in module #(5)',
                'moduleid',
                $moduleid,
                'userapi',
                'get_multiple',
                'comments'
            );
            throw new Exception($msg);
        }

        if ((!isset($itemid) || empty($itemid)) && !isset($author)) {
            $msg = $this->translate(
                'Invalid #(1) [#(2)] for #(3) function #(4)() in module #(5)',
                'itemid',
                $itemid,
                'userapi',
                'get_multiple',
                'comments'
            );
            throw new Exception($msg);
        } elseif (!isset($objectid) && isset($author)) {
            $objectid = 0;
        }

        // is $id ever set in get_multiple?
        if (!isset($id) || !is_numeric($id)) {
            $id = 0;
        } else {
            $nodelr = xarMod::apiFunc(
                'comments',
                'user',
                'get_node_lrvalues',
                ['id' => $id]
            );
        }

        // Optional argument for Pager -
        // for those modules that use comments and require this
        if (!isset($startnum)) {
            $startnum = 1;
        }
        if (!isset($numitems)) {
            $numitems = -1;
        }

        if (!isset($status) || !is_numeric($status)) {
            $status = Defines::STATUS_ON;
        }

        //$dbconn = xarDB::getConn();
        //$xartable =& xarDB::getTables();

        // initialize the commentlist array
        $commentlist = [];

        if (isset($author) && $author > 0) {
            $args['author'] = $author;
        }

        // not sure if this ever happens
        if ($id > 0) {
            $args['left_id'] = (int) $nodelr['left_id'];
            $args['right_id'] = (int) $nodelr['right_id'];
        }

        $commentlist = xarMod::apiFunc('comments', 'user', 'getitems', $args);

        $arr = [];

        foreach ($commentlist as $row) {
            $row['postanon'] = $row['anonpost'];
            $row['datetime'] = $row['date'];
            $row['role_id'] = $row['author'];
            $row['author'] = xarUser::getVar('name', $row['author']);
            $arr[] = $row;
        }

        $commentlist = $arr;

        //Psspl:Modifided the Sql query for getting anonpost_to field.
        // if the depth is zero then we
        // only want one comment
        /*$sql = "SELECT  title AS title,
                        date AS datetime,
                        hostname AS hostname,
                        text AS text,
                        author AS author,
                        author AS role_id,
                        id AS id,
                        parent_id AS parent_id,
                        status AS status,
                        left_id AS left_id,
                        right_id AS right_id,
                        anonpost AS postanon
                  FROM  $xartable[comments]
                 WHERE  modid=?
                   AND  status=?";
        $bindvars = array();
        $bindvars[] = (int) $modid;
        $bindvars[] = (int) $status;

        if (isset($itemtype) && is_numeric($itemtype)) {
            $sql .= " AND itemtype=?";
            $bindvars[] = (int) $itemtype;
        }

        if (isset($objectid) && !empty($objectid)) {
            $sql .= " AND objectid=?";
            $bindvars[] = (string) $objectid; // yes, this is a string in the table
        }

        if (isset($author) && $author > 0) {
            $sql .= " AND author = ?";
            $bindvars[] = (int) $author;
        }

        if ($id > 0) {
            $sql .= " AND (left_id >= ?";
            $sql .= " AND  right_id <= ?)";
            $bindvars[] = (int) $nodelr['left_id'];
            $bindvars[] = (int) $nodelr['right_id'];
        }

        if (!empty($orderby)) {
            $sql .= " ORDER BY $orderby";
        } else {
            if (!empty($reverse)) {
              $sql .= " ORDER BY right_id DESC";
            } else {
                $sql .= " ORDER BY left_id";
            }
        }*/
        // cfr. xarcachemanager - this approach might change later
        //$expire = $this->getModVar('cache.userapi.get_multiple');

        //Add select limit for modules that call this function and need Pager
        /*if (isset($numitems) && is_numeric($numitems)) {
            if (!empty($expire)){
                $result =& $dbconn->CacheSelectLimit($expire, $sql, $numitems, $startnum-1,$bindvars);
            } else {
                $result =& $dbconn->SelectLimit($sql, $numitems, $startnum-1,$bindvars);
            }
        } else {
            if (!empty($expire)){
                $result =& $dbconn->CacheExecute($expire,$sql,$bindvars);
            } else {
                $result =& $dbconn->Execute($sql,$bindvars);
            }
        }*/
        /* if (!$result) return;

         // if we have nothing return empty
         if ($result->EOF) return array();

         if (!xarMod::load('comments','renderer')) {
             $msg = $this->translate('Unable to load #(1) #(2)','comments','renderer');
             throw new Exception($msg);
         }*/

        // zip through the list of results and
        // add it to the array we will return
        /*while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            // FIXME Delete after date testing
            // $row['date'] = xarLocale::formatDate("%B %d, %Y %I:%M %p",$row['datetime']);
            $row['date'] = $row['datetime'];
            $row['author'] = xarUser::getVar('name',$row['author']);
            Renderer::wrap_words($row['text'],80);
            $commentlist[] = $row;
            $result->MoveNext();
        }
        $result->Close();
    */

        if (!empty($commentlist) && !Renderer::array_markdepths_bypid($commentlist)) {
            $msg = $this->translate('#(1) Unable to create depth by pid', __FILE__ . '(' . __LINE__ . '):  ');
            throw new Exception($msg);
        }

        return $commentlist;
    }
}
