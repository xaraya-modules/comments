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

use Xaraya\Modules\Comments\Defines;
use Xaraya\Modules\Comments\Renderer;
use Xaraya\Modules\MethodClass;
use xarDB;
use xarUser;
use xarMod;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments userapi search function
 */
class SearchMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Searches all active comments based on a set criteria
     * @author Carl P. Corliss (aka rabbitt)
     * @access private
     * @return mixed description of return
     */
    public function __invoke(array $args = [])
    {
        if (empty($args) || count($args) < 1) {
            return;
        }

        extract($args);

        $dbconn = xarDB::getConn();
        $xartable = & xarDB::getTables();

        $where = '';

        // initialize the commentlist array
        $commentlist = [];

        $bindvars = [Defines::STATUS_ON];

        $sql = "SELECT  title AS title,
                        date AS date,
                        author AS author,
                        id AS id,
                        parent_id AS parent_id,
                        left_id AS left_id,
                        right_id AS right_id,
                        postanon AS postanon,
                        modid  AS modid,
                        itemtype  AS itemtype,
                        objectid as objectid
                  FROM  $xartable[comments]
                 WHERE  status = ?
                   AND  (";

        if (isset($title)) {
            $sql .= "title LIKE ?";
            $bindvars[] = $title;
        }

        if (isset($text)) {
            if (isset($title)) {
                $sql .= " OR ";
            }
            $sql .= "comment LIKE ?";
            $bindvars[] = $text;
        }

        if (isset($author)) {
            if (isset($title) || isset($text)) {
                $sql .= " OR ";
            }
            if ($author == 'anonymous') {
                $sql .= " author = ? OR postanon = ?";
                $bindvars[] = $role_id;
                $bindvars[] = 1;
            } else {
                $sql .= " author = ? AND postanon != ?";
                $bindvars[] = $role_id;
                $bindvars[] = 1;
            }
        }

        $sql .= ") ORDER BY left_id";

        $result = & $dbconn->Execute($sql, $bindvars);
        if (!$result) {
            return;
        }

        // if we have nothing to return
        // we return nothing ;) duh? lol
        if ($result->EOF) {
            return [];
        }

        // zip through the list of results and
        // add it to the array we will return
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $row['author'] = xarUser::getVar('name', $row['author']);
            $commentlist[] = $row;
            $result->MoveNext();
        }
        $result->Close();

        if (!xarMod::load('comments', 'renderer')) {
            $msg = xarML('Unable to load #(1) #(2)', 'comments', 'renderer');
            throw new BadParameterException($msg);
        }

        if (!Renderer::array_markdepths_bypid($commentlist)) {
            $msg = xarML('Unable to create depth by parent_id');
            throw new BadParameterException($msg);
        }

        Renderer::array_sort($commentlist, Defines::SORTBY_TOPIC, Defines::SORT_ASC);
        // FIXME: excess depth cannot be pruned using this function without knowing
        // the module/itemtype/objectid, which we don't know at this point.
        /*$commentlist = Renderer::array_prune_excessdepth(
            array(
                'array_list' => $commentlist,
                'cutoff' => Defines::MAX_DEPTH,
            )
        );*/

        Renderer::array_maptree($commentlist);

        return $commentlist;
    }
}
