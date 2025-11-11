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

/**
 * comments userapi getoptions function
 * @extends MethodClass<UserApi>
 */
class GetoptionsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Grabs the list of viewing options in the following order of precedence:
     * 1. POST/GET
     * 2. User Settings (if user is logged in)
     * 3. Module Defaults
     * 4. internal defaults
     * @author Carl P. Corliss (aka rabbitt)
     * @access public
     * @return array|void list of viewing options (depth, render style, order, and sortby)
     * @see UserApi::getoptions()
     */
    public function __invoke(array $args = [])
    {
        $this->var()->find('depth', $depth, 'int');
        $this->var()->find('render', $render, 'str');
        $this->var()->find('order', $order, 'int');
        $this->var()->find('sortby', $sortby, 'int');

        // if one of the settings configured, the all should be.
        // Order of precedence for determining which
        // settings to use.  (User_Defined is (obviously)
        // dependant on the user being logged in.):
        // Get/Post->[user_defined->]admin_defined

        if (isset($depth)) {
            if ($depth == 0) {
                $settings['depth'] = 1;
            } else {
                $settings['depth'] = $depth;
            }
        } else {
            // Not doing user settings for now
            /*if ($this->user()->isLoggedIn()) {
                // Grab user's depth setting.
                $settings['depth'] = $this->mod()->getUserVar('depth');
            } else {*/
            $settings['depth'] = $this->mod()->getVar('depth');
            /*}*/
        }

        if (isset($render) && !empty($render)) {
            $settings['render'] = $render;
        } else {
            /*if ($this->user()->isLoggedIn()) {
                // Grab user's depth setting.
                $settings['render'] = $this->mod()->getUserVar('render');
            } else {*/
            $settings['render'] = $this->mod()->getVar('render');
            /*}*/
        }

        if (isset($order) && !empty($order)) {
            $settings['order'] = $order;
        } else {
            /*if ($this->user()->isLoggedIn()) {
                // Grab user's depth setting.
                $settings['order'] = $this->mod()->getUserVar('order');
            } else {*/
            $settings['order'] = $this->mod()->getVar('order');
            /*}*/
        }

        if (isset($sortby) && !empty($sortby)) {
            $settings['sortby'] = $sortby;
        } else {
            /*if ($this->user()->isLoggedIn()) {
                // Grab user's depth setting.
                $settings['sortby'] = $this->mod()->getUserVar('sortby');
            } else {*/
            $settings['sortby'] = $this->mod()->getVar('sortby');
            /*}*/
        }

        if (!isset($settings['depth']) || $settings['depth'] > (Defines::MAX_DEPTH - 1)) {
            $settings['depth'] = (Defines::MAX_DEPTH - 1);
        }

        if (empty($settings['render'])) {
            $settings['render'] = Defines::VIEW_THREADED;
        }
        if (empty($settings['order'])) {
            $settings['order'] = Defines::SORT_ASC;
        }
        if (empty($settings['sortby'])) {
            $settings['sortby'] = Defines::SORTBY_THREAD;
        }

        return $settings;
    }
}
