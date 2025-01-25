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
use xarSecurity;
use xarMod;
use xarController;
use xarVar;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments userapi getitemlinks function
 * @extends MethodClass<UserApi>
 */
class GetitemlinksMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * utility function to pass individual item links to whoever
     * @param array<mixed> $args
     * @var mixed $itemtype item type (optional)
     * @var mixed $itemids array of item ids to get
     * @return array|void containing the itemlink(s) for the item(s).
     * @see UserApi::getitemlinks()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var UserApi $userapi */
        $userapi = $this->userapi();
        $itemlinks = [];
        if (!$this->sec()->checkAccess('ReadComments', 0)) {
            return $itemlinks;
        }

        if (empty($itemids)) {
            $itemids = [];
        }

        // FIXME: support retrieving several comments at once
        foreach ($itemids as $itemid) {
            $item = $userapi->get_one(['id' => $itemid]);
            if (!isset($item)) {
                return;
            }
            if (!empty($item) && !empty($item[0]['title'])) {
                $title = $item[0]['title'];
            } else {
                $title = $this->ml('Comment #(1)', $itemid);
            }
            $itemlinks[$itemid] = ['url'   => $this->mod()->getURL(
                'user',
                'display',
                ['id' => $itemid]
            ),
                'title' => $this->ml('Display Comment'),
                'label' => $this->var()->prep($title), ];
        }
        return $itemlinks;
    }
}
