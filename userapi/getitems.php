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
use xarMod;
use xarSecurity;
use xarDB;
use Query;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments userapi getitems function
 * @extends MethodClass<UserApi>
 */
class GetitemsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get number of comments for all items or a list of items
     * @param array<mixed> $args
     * @var mixed $modname name of the module you want items from, or
     * @var mixed $modid module id you want items from
     * @var mixed $itemtype item type (optional)
     * @var mixed $itemids array of item IDs
     * @var mixed $status optional status to count: ALL (minus root nodes), ACTIVE, INACTIVE
     * @var mixed $numitems optional number of items to return
     * @var mixed $startnum optional start at this number (1-based)
     * @return array|void $array[$itemid] = $numcomments;
     * @see UserApi::getitems()
     */
    public function __invoke(array $args = [])
    {
        // Get arguments from argument array
        extract($args);

        if (!isset($moduleid) && isset($modname)) {
            $moduleid = $this->mod()->getRegID($modname);
        }

        /*if (!isset($itemtype)) {
            $itemtype = 0;
        }*/

        if (empty($status)) {
            $status = Defines::STATUS_ROOT_NODE;
        }

        switch ($status) {
            case 'active':
                $where_status = "status = " . Defines::STATUS_ON;
                $join = ' AND ';
                break;
            case 'inactive':
                $where_status = "status = " . Defines::STATUS_OFF;
                $join = ' AND ';
                break;
            default:
            case 'all':
                $where_status = "status != " . Defines::STATUS_ROOT_NODE;
                $join = ' AND ';
        }

        // Security check
        if (!isset($mask)) {
            $mask = 'ReadComments';
        }
        if (!$this->sec()->checkAccess($mask)) {
            return;
        }

        // Database information
        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();
        $commentstable = $xartable['comments'];

        $where = '';
        $join = '';

        if (isset($author)) {
            $where = $where . $join . 'author eq ' . $author;
            $join = ' AND ';
        }
        if (isset($itemtype) && !empty($itemtype)) {
            $where = $where . $join . 'itemtype eq ' . $itemtype;
            $join = ' AND ';
        }
        if (isset($left_id) && isset($right_id)) {
            $where = $where . $join . 'left_id ge ' . $left_id;
            $join = ' AND ';
            $where = $where . $join . 'right_id le ' . $right_id;
        }
        if (isset($moduleid)) {
            $where = $where . $join . 'moduleid eq ' . $moduleid;
            $join = ' AND ';
        }
        if (isset($itemid)) {
            $where = $where . $join . 'itemid eq ' . $itemid;
        }

        if (isset($startnum)) {
            $filters['startnum'] = $startnum;
        }
        if (isset($numitems)) {
            $filters['numitems'] = $numitems;
        }

        if (!empty($where)) {
            $filters['where'] = $where;
        } else {
            $filters = [];
        }

        sys::import('modules.dynamicdata.class.objects.factory');
        //    $list = $this->data()->getObjectList(array(
        //                            'name' => 'comments_comments'
        //        ));

        //    if (!is_object($list)) return;

        //    $items = $list->getItems($filters);

        //    return $items;

        sys::import('xaraya.structures.query');
        $tables = & $this->db()->getTables();
        $q = new Query('SELECT', $tables['comments']);
        $q->eq('module_id', $moduleid);
        $q->eq('itemtype', $itemtype);
        $q->eq('status', (int) $status);
        if (isset($itemids) && count($itemids) > 0) {
            $q->in('itemid', $itemids);
        } elseif (isset($itemid)) {
            $q->in('itemid', $itemid);
        }
        //    $q->setgroup('itemid');
        $q->setorder('itemid', 'ASC');
        if (!empty($numitems)) {
            if (empty($startnum)) {
                $startnum = 1;
            }
            $q->setstartat($startnum);
            $q->setrowstodo($numitems);
        }
        $q->run();
        $items = $q->output();

        // Get items
        $bindvars = [];
        $query = "SELECT id,parent_id,parent_url,title,text,left_id,right_id,module_id,itemtype,itemid,date,author,anonpost, COUNT(*)
                    FROM $commentstable
                   WHERE module_id = ?
                     AND itemtype = ?
                     AND $where_status ";
        $bindvars[] = (int) $moduleid;
        $bindvars[] = (int) $itemtype;
        if (isset($itemids) && count($itemids) > 0) {
            $bindmarkers = '?' . str_repeat(',?', count($itemids) - 1);
            $bindvars = array_merge($bindvars, $itemids);
            $query .= " AND itemid IN ($bindmarkers)";
        }
        $query .= " GROUP BY id, itemid
                    ORDER BY itemid";
        //                ORDER BY (1 + objectid";
        //
        // CHECKME: dirty trick to try & force integer ordering (CAST and CONVERT are for MySQL 4.0.2 and higher
        // <rabbitt> commented that line out because it won't work with PostgreSQL - not sure about others.

        if (!empty($numitems)) {
            if (empty($startnum)) {
                $startnum = 1;
            }
            $result = $dbconn->SelectLimit($query, $numitems, $startnum - 1, $bindvars);
        } else {
            $result = $dbconn->Execute($query, $bindvars);
        }

        if (!$result) {
            return;
        }

        /*    $items = array();
            while ($result->next()) {
                list($id,$parent_id,$parent_url,$title,$text,$left_id,$right_id,$moduleid,$itemtype,$itemid,$date,$author,$anonpost,$numcomments) = $result->fields;
                $items[$id] = array(
                                    'id' => $id,
                                    'parent_id' => $parent_id,
                                    'parent_url' => $parent_url,
                                    'title' => $title,
                                    'text' => $text,
                                    'left_id' => $left_id,
                                    'right_id' => $right_id,
                                    'moduleid' => $moduleid,
                                    'itemtype' => $itemtype,
                                    'itemid' => $itemid,
                                    'date' => $date,
                                    'author' => $author,
                                    'anonpost' => $anonpost,
                                    'number_comments' => $numcomments,
                );
            }*/
        $result->close();
        return $items;
    }
}
