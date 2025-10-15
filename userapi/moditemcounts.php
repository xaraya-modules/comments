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
use sys;

sys::import('xaraya.modules.method');

/**
 * comments userapi moditemcounts function
 * @extends MethodClass<UserApi>
 */
class ModitemcountsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     *
     * @see UserApi::moditemcounts()
     */
    public function __invoke(array $args = [])
    {
        /** @var UserApi $userapi */
        $userapi = $this->userapi();
        $moditemcounts = [];

        $items = $userapi->getitems($args);

        extract($args);

        sys::import('modules.dynamicdata.class.objects.factory');

        foreach ($items as $item) {
            if (!isset($itemid) || $itemid != $item['itemid']) {
                $filters['where'] = 'itemid eq ' . $item['itemid'];
                if (isset($itemtype)) {
                    $filters['where'] .= ' and itemtype eq ' . $itemtype;
                }

                if (isset($status) && $status == 'inactive') {
                    $filters['where'] .= ' and status eq ' . Defines::STATUS_OFF;
                } else {
                    $filters['where'] .= ' and status ne ' . Defines::STATUS_ROOT_NODE;
                }
                $list = $this->data()->getObjectList([
                    'name' => 'comments_comments',
                ]);
                $items = $list->getItems($filters);
                $count = count($items);
                $id = $item['itemid'];
                $itemtype = $item['itemtype'];

                $moditemcounts[$id] = ['count' => $count, 'itemtype' => $itemtype];
            }
            $objectid = $item['objectid'];
        }


        return $moditemcounts;
    }
}
