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
use DataObjectFactory;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments userapi get_blacklist function
 * @extends MethodClass<UserApi>
 */
class GetBlacklistMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Comments Module
     * @package modules
     * @subpackage comments
     * @category Third Party Xaraya Module
     * @version 2.4.0
     * @copyright see the html/credits.html file in this release
     * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
     * @link http://xaraya.com/index.php/release/14.html
     * @author Carl P. Corliss <rabbitt@xaraya.com>
     * @see UserApi::getBlacklist()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        // Optional arguments.
        if (empty($startnum)) {
            $startnum = 1;
        }
        if (!isset($numitems)) {
            $numitems = 5000;
        }
        $items = [];

        sys::import('modules.dynamicdata.class.objects.factory');
        $list = $this->data()->getObjectList([
            'name' => 'comments_blacklist',
            'numitems' => $numitems,
            'startnum' => $startnum,
        ]);

        if (!is_object($list)) {
            return;
        }

        $items = $list->getItems();

        $arr = [];

        foreach ($items as $val) {
            $arr[] = ['id'       => $val['id'],
                'domain'   => $val['domain'],
            ];
        }

        $items = $arr;


        /* // Get database setup
         $dbconn = $this->db()->getConn();
         $xartable =& $this->db()->getTables();
         $btable = $xartable['blacklist'];
         $query = "SELECT id,
                          domain
                   FROM $btable";
         $result =& $dbconn->SelectLimit($query, $numitems, $startnum-1);
         if (!$result) return;
         // Put items into result array.
         while ($result->next()) {
             list($id, $domain) = $result->fields;
                 $items[] = array('id'       => $id,
                                  'domain'   => $domain);
         }
         $result->Close();*/
        return $items;
    }
}
