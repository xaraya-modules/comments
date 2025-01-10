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

use Xaraya\Modules\MethodClass;
use xarController;
use xarModVars;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments adminapi getmenulinks function
 */
class GetmenulinksMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * utility function pass individual menu items to the main menu
     * @return array containing the menulinks for the main menu items.
     */
    public function __invoke(array $args = [])
    {
        $menulinks[] = ['url'   => xarController::URL(
            'comments',
            'admin',
            'stats'
        ),
            'title' => xarML('View comments per module statistics'),
            'label' => xarML('View Statistics'), ];
        /* Comment blacklist unavailable at 2005-10-12
        if (xarModVars::get('comments', 'useblacklist') == true){
            $menulinks[] = Array('url'   => xarController::URL('comments',
                                                      'admin',
                                                      'importblacklist'),
                                 'title' => xarML('Import the latest blacklist'),
                                 'label' => xarML('Import Blacklist'));
        }
        */

        $menulinks[] = ['url'   => xarController::URL(
            'comments',
            'admin',
            'modifyconfig'
        ),
            'title' => xarML('Modify the comments module configuration'),
            'label' => xarML('Modify Config'), ];

        if (empty($menulinks)) {
            $menulinks = '';
        }

        return $menulinks;
    }
}
