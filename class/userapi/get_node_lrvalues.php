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
 * comments userapi get_node_lrvalues function
 * @extends MethodClass<UserApi>
 */
class GetNodeLrvaluesMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Grab the left and right values for a particular node
     * (aka comment) in the database
     * @author Carl P. Corliss (aka rabbitt)
     * @access public
     * @param int $id id of the comment to lookup
     * @return array an array containing the left and right values or an
     * empty array if the comment_id specified doesn't exist
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (empty($id)) {
            $msg = $this->translate('Missing or Invalid parameter \'id\'!!');
            throw new BadParameterException($msg);
        }

        $dbconn = xarDB::getConn();
        $xartable = & xarDB::getTables();

        $sql = "SELECT  left_id, right_id
                  FROM  $xartable[comments]
                 WHERE  id=$id";

        $result = & $dbconn->Execute($sql);

        if (!$result) {
            return;
        }

        if (!$result->EOF) {
            $lrvalues = $result->GetRowAssoc(false);
        } else {
            $lrvalues = [];
        }

        $result->Close();

        return $lrvalues;
    }
}
