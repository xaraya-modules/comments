<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @copyright (C) 2002-2007 The copyright-placeholder
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage comments
 * @link http://xaraya.com/index.php/release/14.html
 * @author Carl P. Corliss <rabbitt@xaraya.com>
 */
function comments_userapi_get_blacklist($args)
{
    extract($args);
    // Optional arguments.
    if (empty($startnum)) {
        $startnum = 1;
    }
    if (!isset($numitems)) {
        $numitems = 5000;
    }
    $items = array();

    sys::import('modules.dynamicdata.class.objects.master');
    $list = DataObjectMaster::getObjectList(array(
                            'name' => 'blacklist',
                            'numitems' => $numitems,
                            'startnum' => $startnum
        ));

    if (!is_object($list)) return;

    $items = $list->getItems();

    $arr = array();

    foreach ($items as $val) {
        $arr[] = array('id'       => $val['id'],
                             'domain'   => $val['domain']
                            );
    }

    $items = $arr;


   /* // Get database setup
    $dbconn = xarDB::getConn();
    $xartable = xarDB::getTables();
    $btable = $xartable['blacklist'];
    $query = "SELECT id,
                     domain
              FROM $btable";
    $result =& $dbconn->SelectLimit($query, $numitems, $startnum-1);
    if (!$result) return;
    // Put items into result array.
    for (; !$result->EOF; $result->MoveNext()) {
        list($id, $domain) = $result->fields;
            $items[] = array('id'       => $id,
                             'domain'   => $domain);
    }
    $result->Close();*/
    return $items;
}
?>