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
use xarController;
use xarModVars;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

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
     */
    public function __invoke(array $args = [])
    {
        $menulinks[] = ['url'   => $this->getUrl('admin', 'stats'),
            'title' => $this->translate('View comments per module statistics'),
            'label' => $this->translate('View Statistics'), ];
        /* Comment blacklist unavailable at 2005-10-12
        if ($this->getModVar('useblacklist') == true){
            $menulinks[] = Array('url'   => $this->getUrl('admin', 'importblacklist'),
                                 'title' => $this->translate('Import the latest blacklist'),
                                 'label' => $this->translate('Import Blacklist'));
        }
        */

        $menulinks[] = ['url'   => $this->getUrl('admin', 'modifyconfig'),
            'title' => $this->translate('Modify the comments module configuration'),
            'label' => $this->translate('Modify Config'), ];

        if (empty($menulinks)) {
            $menulinks = '';
        }

        return $menulinks;
    }
}
