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
use Xaraya\Modules\MethodClass;

/**
 * comments adminapi getmenulinks function
 * @extends MethodClass<AdminApi>
 */
class GetmenulinksMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * utility function pass individual menu items to the main menu
     * @return array containing the menulinks for the main menu items.
     * @see AdminApi::getmenulinks()
     */
    public function __invoke(array $args = [])
    {
        $menulinks[] = ['url'   => $this->mod()->getURL('admin', 'stats'),
            'title' => $this->ml('View comments per module statistics'),
            'label' => $this->ml('View Statistics'), ];
        /* Comment blacklist unavailable at 2005-10-12
        if ($this->mod()->getVar('useblacklist') == true){
            $menulinks[] = Array('url'   => $this->mod()->getURL('admin', 'importblacklist'),
                                 'title' => $this->ml('Import the latest blacklist'),
                                 'label' => $this->ml('Import Blacklist'));
        }
        */

        $menulinks[] = ['url'   => $this->mod()->getURL('admin', 'modifyconfig'),
            'title' => $this->ml('Modify the comments module configuration'),
            'label' => $this->ml('Modify Config'), ];

        if (empty($menulinks)) {
            $menulinks = '';
        }

        return $menulinks;
    }
}
