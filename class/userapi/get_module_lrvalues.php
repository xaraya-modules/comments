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
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments userapi get_module_lrvalues function
 * @extends MethodClass<UserApi>
 */
class GetModuleLrvaluesMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Grab the left and right values for each object of a particular module
     * @author Carl P. Corliss (aka rabbitt)
     * @access public
     * @param int $modid id of the module to gather info on
     * @return array an array containing the left and right values or an
     * empty array if the modid specified doesn't exist
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (!isset($modid) || empty($modid)) {
            $msg = $this->ml('Missing or Invalid parameter \'modid\'!!');
            throw new BadParameterException($msg);
        }

        if (empty($itemtype)) {
            $itemtype = 0;
        }

        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();

        $sql = "SELECT  objectid AS objectid,
                        MIN(left_id) AS left_id,
                        MAX(right_id) AS right_id
                  FROM  $xartable[comments]
                 WHERE  modid=$modid
                   AND  itemtype=$itemtype
              GROUP BY  objectid";

        $result = & $dbconn->Execute($sql);

        if (!$result) {
            return;
        }

        if (!$result->EOF) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $lrvalues[] = $row;
                $result->MoveNext();
            }
        } else {
            $lrvalues = [];
        }

        $result->Close();

        return $lrvalues;
    }
}
