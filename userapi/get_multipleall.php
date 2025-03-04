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
use xarMod;
use xarLocale;
use xarUser;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments userapi get_multipleall function
 * @extends MethodClass<UserApi>
 */
class GetMultipleallMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Get a list of comments from one or several modules + item types
     * @author Andrea Moro modified from Carl P. Corliss (aka rabbitt) userapi
     * @access public
     * @param array<mixed> $args
     * @param array $modarray array of module names + itemtypes to look for
     * @param string $order sort order (ASC or DESC date)
     * @param int $howmany number of comments to retrieve
     * @param int $first start number
     * @return array|void an array of comments or an empty array if no comments
     * found for the particular modules, or raise an
     * exception and return false.
     * @see UserApi::getMultipleall()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        // $modid
        if (!isset($modarray) || empty($modarray) || !is_array($modarray)) {
            $modarray = ['all'];
        }
        if (empty($order) || $order != 'ASC') {
            $order = 'DESC';
        } else {
            $order = 'ASC';
        }

        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();

        $commentlist = [];

        $query = "SELECT  title AS subject,
                          text AS comment,
                          date AS datetime,
                          author AS author,
                          id AS id,
                          status AS status,
                          anonpost AS postanon,
                          modid AS modid,
                          itemtype AS itemtype,
                          objectid AS objectid
                    FROM  $xartable[comments]
                   WHERE  status=" . Defines::STATUS_ON . " ";

        if (count($modarray) > 0 && $modarray[0] != 'all') {
            $where = [];
            foreach ($modarray as $modname) {
                if (strstr($modname, '.')) {
                    [$module, $itemtype] = explode('.', $modname);
                    $modid = $this->mod()->getRegID($module);
                    if (empty($itemtype)) {
                        $itemtype = 0;
                    }
                    $where[] = "(modid = $modid AND itemtype = $itemtype)";
                } else {
                    $modid = $this->mod()->getRegID($modname);
                    $where[] = "(modid = $modid)";
                }
            }
            if (count($where) > 0) {
                $query .= " AND ( " . join(' OR ', $where) . " ) ";
            }
        }

        $query .= " ORDER BY datetime $order ";

        if (empty($howmany) || !is_numeric($howmany)) {
            $howmany = 5;
        }
        if (empty($first) || !is_numeric($first)) {
            $first = 1;
        }

        $result = $dbconn->SelectLimit($query, $howmany, $first - 1);
        if (!$result) {
            return;
        }

        while ($result->next()) {
            $row = $result->GetRowAssoc(false);
            // FIXME delete after date output testing
            // $row['date'] = $this->mls()->formatDate("%B %d, %Y %I:%M %p",$row['datetime']);
            $row['date'] = $row['datetime'];
            $row['author'] = $this->user($row['author'])->getName();
            $commentlist[] = $row;
        }
        $result->Close();

        return $commentlist;
    }
}
