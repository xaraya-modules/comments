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
use Query;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments userapi get_author_count function
 * @extends MethodClass<UserApi>
 */
class GetAuthorCountMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Get the number of comments for a module based on the author
     * @author mikespub
     * @access public
     * @param int $moduleid the id of the module that these nodes belong to
     * @param int $itemtype the item type that these nodes belong to
     * @param int $author the id of the author you want to count comments for
     * @param int $status (optional) the status of the comments to tally up
     * @return int the number of comments for the particular modid/objectid pair,
     * or raise an exception and return false.
     * @see UserApi::getAuthorCount()
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        $exception = false;

        if (!isset($moduleid) || empty($moduleid)) {
            $msg = $this->ml(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                'moduleid',
                'userapi',
                'get_author_count',
                'comments'
            );
            throw new BadParameterException($msg);
        }


        if (!isset($author) || empty($author)) {
            $msg = $this->ml(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                'author',
                'userapi',
                'get_author_count',
                'comments'
            );
            throw new BadParameterException($msg);
        }

        if (!isset($status) || !is_numeric($status)) {
            $status = Defines::STATUS_ON;
        }

        $tables = & $this->db()->getTables();
        $q = new Query('SELECT', $tables['comments']);
        $q->addfield('COUNT(id) AS numitems');
        $q->eq('module_id', $moduleid);
        $q->eq('author', $author);
        $q->eq('status', $status);
        if (isset($itemtype) && is_numeric($itemtype)) {
            $q->eq('itemtype', $itemtype);
        }
        $q->run();
        $result = $q->row();

        return $result['numitems'];
    }
}
