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

/**
 * comments userapi activate function
 * @extends MethodClass<UserApi>
 */
class ActivateMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Activate the specified comment
     * @author Carl P. Corliss (aka rabbitt)
     * @access public
     * @param int $id id of the comment to lookup
     * @return bool|void returns true on success, throws an exception and returns false otherwise
     * @see UserApi::activate()
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (empty($id)) {
            $msg = $this->ml('Missing or Invalid parameter \'id\'!!');
            throw new BadParameterException($msg);
        }

        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();

        // First grab the objectid and the modid so we can
        // then find the root node.
        $sql = "UPDATE $xartable[comments]
                SET status='" . Defines::STATUS_ON . "'
                WHERE id=?";
        $bindvars = [(int) $id];

        $result = & $dbconn->Execute($sql, $bindvars);

        if (!$result) {
            return;
        }
    }
}
