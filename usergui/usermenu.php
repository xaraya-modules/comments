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
use Xaraya\Modules\Comments\UserApi;
use Xaraya\Modules\Comments\Defines;
use Xaraya\Modules\MethodClass;
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
     * @see UserGui::usermenu()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var UserApi $userapi */
        $userapi = $this->userapi();

        // Security Check
        if ($this->sec()->checkAccess('ReadComments', 0)) {
            $this->var()->find('phase', $phase, 'str', 'menu');

            $this->tpl()->setPageTitle($this->mod('themes')->getVar('SiteName') . ' :: ' .
                               $this->var()->prep($this->ml('Comments'))
                               . ' :: ' . $this->var()->prep($this->ml('Your Account Preferences')));

            switch (strtolower($phase)) {
                case 'menu':

                    $icon = $this->tpl()->getImage('comments.gif', 'comments');
                    $data = $this->mod()->template(
                        'usermenu_icon',
                        [
                            'icon' => $icon,
                            'usermenu_form_url' => $this->mod()->getURL( 'user', 'usermenu', ['phase' => 'form']),
                        ]
                    );
                    break;

                case 'form':

                    $settings = $userapi->getoptions();
                    $settings['max_depth'] = Defines::MAX_DEPTH - 1;
                    $authid = $this->sec()->genAuthKey();
                    $data = $this->mod()->template('usermenu_form', ['authid'   => $authid,
                        'settings' => $settings, ]);
                    break;

                case 'update':

                    $this->var()->find('settings', $settings, 'array', []);

                    if (count($settings) <= 0) {
                        $msg = $this->ml('Settings passed from form are empty!');
                        throw new BadParameterException($msg);
                    }

                    // Confirm authorisation code.
                    if (!$this->sec()->confirmAuthKey()) {
                        return;
                    }

                    $userapi->setoptions($settings);

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
