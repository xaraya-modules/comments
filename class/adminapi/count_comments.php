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
use Xaraya\Modules\Comments\Defines;
use Xaraya\Modules\MethodClass;
use xarDB;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments adminapi count_comments function
 * @extends MethodClass<AdminApi>
 */
class CountCommentsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Count comments by modid/objectid/all and active/inactive/all
     * @author Carl P. Corliss (aka rabbitt)
     * @access private
     * @param string type     What to gather for: ALL, MODULE, or OBJECT (object == modid/objectid pair)
     * @param string status   What status' to count: ALL (minus root nodes), ACTIVE, INACTIVE
     * @param int modid    Module to gather info on (only used with type == module|object)
     * @param int itemtype Item type in that module to gather info on (only used with type == module|object)
     * @param int objectid ObjectId to gather info on (only used with type == object)
     * @return int|null total comments
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        $dbconn         = $this->db()->getConn();
        $xartable       = & $this->db()->getTables();
        $total          = 0;
        $status         = strtolower($status);
        $type           = strtolower($type);
        $where_type     = '';
        $where_status   = '';

        if (empty($type) || !preg_match('/^(all|module|object)$/', $type)) {
            $msg = $this->ml('Invalid Parameter \'type\' to function count_comments(). \'type\' must be: all, module, or object.');
            throw new BadParameterException($msg);
        } else {
            switch ($type) {
                case 'object':
                    if (empty($objectid)) {
                        $msg = $this->ml('Missing or Invalid Parameter \'objectid\'');
                        throw new BadParameterException($msg);
                    }

                    $where_type = "objectid = '$objectid' AND ";

                    // Allow the switch to fall through if type == object because
                    // we need modid for object in addition to objectid
                    // hence, no break statement here :-)

                    // no break
                case 'module':
                    if (empty($modid)) {
                        $msg = $this->ml('Missing or Invalid Parameter \'modid\'');
                        throw new BadParameterException($msg);
                    }

                    $where_type .= "modid = $modid";

                    if (isset($itemtype) && is_numeric($itemtype)) {
                        $where_type .= " AND itemtype = $itemtype";
                    }
                    break;

                default:
                case 'all':
                    $where_type = "1";
            }
        }
        if (empty($status) || !preg_match('/^(all|inactive|active)$/', $status)) {
            $msg = $this->ml('Invalid Parameter \'status\' to function count_module_comments(). \'status\' must be: all, active, or inactive.');
            throw new BadParameterException($msg);
        } else {
            switch ($status) {
                case 'active':
                    $where_status = "status = " . Defines::STATUS_ON;
                    break;
                case 'inactive':
                    $where_status = "status = " . Defines::STATUS_OFF;
                    break;
                default:
                //case 'active':
                    $where_status = "status != " . Defines::STATUS_ROOT_NODE;
            }
        }
        $query = "SELECT COUNT(id)
                    FROM $xartable[comments]
                   WHERE $where_type
                     AND $where_status";
        $result = & $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        if ($result->EOF) {
            return 0;
        }
        [$numitems] = $result->fields;
        $result->Close();
        return $numitems;
    }
}
