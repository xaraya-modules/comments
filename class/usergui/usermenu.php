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
        if ($this->checkAccess('ReadComments', 0)) {
            if (!$this->fetch('phase', 'str', $phase, 'menu', xarVar::NOT_REQUIRED)) {
                return;
            }

            xarTpl::setPageTitle(xarModVars::get('themes', 'SiteName') . ' :: ' .
                               xarVar::prepForDisplay($this->translate('Comments'))
                               . ' :: ' . xarVar::prepForDisplay($this->translate('Your Account Preferences')));

            switch (strtolower($phase)) {
                case 'menu':

                    $icon = xarTpl::getImage('comments.gif', 'comments');
                    $data = xarTpl::module(
                        'comments',
                        'user',
                        'usermenu_icon',
                        ['icon' => $icon,
                            'usermenu_form_url' => $this->getUrl( 'user', 'usermenu', ['phase' => 'form']),
                        ]
                    );
                    break;

                case 'form':

                    $settings = xarMod::apiFunc('comments', 'user', 'getoptions');
                    $settings['max_depth'] = Defines::MAX_DEPTH - 1;
                    $authid = xarSec::genAuthKey('comments');
                    $data = xarTpl::module('comments', 'user', 'usermenu_form', ['authid'   => $authid,
                        'settings' => $settings, ]);
                    break;

                case 'update':

                    if (!$this->fetch('settings', 'array', $settings, [], xarVar::NOT_REQUIRED)) {
                        return;
                    }

                    if (count($settings) <= 0) {
                        $msg = $this->translate('Settings passed from form are empty!');
                        throw new BadParameterException($msg);
                    }

                    // Confirm authorisation code.
                    if (!$this->confirmAuthKey()) {
                        return;
                    }

                    xarMod::apiFunc('comments', 'user', 'setoptions', $settings);

                    // Redirect
                    $this->redirect(xarController::URL('roles', 'user', 'account'));

                    break;
            }
        } else {
            $data = ''; //make sure hooks in usermenu don't fail because this function returns unset
        }
        return $data;
    }
}
