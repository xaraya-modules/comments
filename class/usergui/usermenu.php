<?php

/**
 * @package modules\comments
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Comments\UserGui;


use Xaraya\Modules\Comments\UserGui;
use Xaraya\Modules\Comments\Defines;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarVar;
use xarTpl;
use xarModVars;
use xarController;
use xarMod;
use xarSec;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments user usermenu function
 * @extends MethodClass<UserGui>
 */
class UsermenuMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * The user menu that is used in roles/account
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        // Security Check
        if ($this->sec()->checkAccess('ReadComments', 0)) {
            if (!$this->var()->find('phase', $phase, 'str', 'menu')) {
                return;
            }

            $this->tpl()->setPageTitle(xarModVars::get('themes', 'SiteName') . ' :: ' .
                               $this->var()->prep($this->ml('Comments'))
                               . ' :: ' . $this->var()->prep($this->ml('Your Account Preferences')));

            switch (strtolower($phase)) {
                case 'menu':

                    $icon = xarTpl::getImage('comments.gif', 'comments');
                    $data = $this->mod()->template(
                        'usermenu_icon',
                        [
                            'icon' => $icon,
                            'usermenu_form_url' => $this->mod()->getURL( 'user', 'usermenu', ['phase' => 'form']),
                        ]
                    );
                    break;

                case 'form':

                    $settings = xarMod::apiFunc('comments', 'user', 'getoptions');
                    $settings['max_depth'] = Defines::MAX_DEPTH - 1;
                    $authid = $this->sec()->genAuthKey();
                    $data = $this->mod()->template('usermenu_form', ['authid'   => $authid,
                        'settings' => $settings, ]);
                    break;

                case 'update':

                    if (!$this->var()->find('settings', $settings, 'array', [])) {
                        return;
                    }

                    if (count($settings) <= 0) {
                        $msg = $this->ml('Settings passed from form are empty!');
                        throw new BadParameterException($msg);
                    }

                    // Confirm authorisation code.
                    if (!$this->sec()->confirmAuthKey()) {
                        return;
                    }

                    xarMod::apiFunc('comments', 'user', 'setoptions', $settings);

                    // Redirect
                    $this->ctl()->redirect($this->ctl()->getModuleURL('roles', 'user', 'account'));

                    break;
            }
        } else {
            $data = ''; //make sure hooks in usermenu don't fail because this function returns unset
        }
        return $data;
    }
}
