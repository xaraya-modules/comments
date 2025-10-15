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
 * comments userapi setoptions function
 * @extends MethodClass<UserApi>
 */
class SetoptionsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Set a user's viewing options
     * @author Carl P. Corliss (aka rabbitt)
     * @access private
     * @return mixed description of return
     * @see UserApi::setoptions()
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (isset($depth)) {
            if ($depth == 0) {
                $depth = 1;
            }
            if ($depth > (Defines::MAX_DEPTH - 1)) {
                $depth = (Defines::MAX_DEPTH - 1);
            }
        } else {
            $depth = $this->mod()->getVar('depth');
        }

        if (empty($render)) {
            $render = $this->mod()->getVar('render');
        }

        if (empty($order)) {
            $order = $this->mod()->getVar('order');
        }

        if (empty($sortby)) {
            $sortby = $this->mod()->getVar('sortby');
        }

        if ($this->user()->isLoggedIn()) {
            // Grab user's depth setting.
            $this->mod()->setUserVar('depth', $depth);
            $this->mod()->setUserVar('render', $render);
            $this->mod()->setUserVar('sortby', $sortby);
            $this->mod()->setUserVar('order', $order);
        }

        return true;
    }
}
