<?php

/**
 * Comments Module
 *
 * @package modules
 * @subpackage comments
 * @category Third Party Xaraya Module
 * @version 2.4.0
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://xaraya.com/index.php/release/14.html
 * @author Carl P. Corliss <rabbitt@xaraya.com>
 */
sys::import('modules.comments.xarincludes.defines');
use Xaraya\Modules\Comments\Defines;

/**
 * This is a standard function to modify the configuration parameters of the
 * module
 */

function comments_admin_modifyconfig(array $args = [], $context = null)
{
    // Security Check
    if (!$this->sec()->checkAccess('AdminComments')) {
        return;
    }
    //$numstats       = $this->mod()->getVar('numstats');
    //  $rssnumitems    = $this->mod()->getVar('rssnumitems');

    if (empty($rssnumitems)) {
        //$this->mod('comments')->setVar('rssnumitems', 25);
    }
    if (empty($numstats)) {
        //$this->mod('comments')->setVar('numstats', 100);
    }

    $this->var()->find('phase', $phase, 'str:1:100', 'modify');
    $this->var()->find('tab', $data['tab'], 'str:1:100', 'comments_general');
    $this->var()->find('tabmodule', $tabmodule, 'str:1:100', 'comments');
    $hooks = xarModHooks::call('module', 'getconfig', 'comments');
    if (!empty($hooks) && isset($hooks['tabs'])) {
        foreach ($hooks['tabs'] as $key => $row) {
            $configarea[$key]  = $row['configarea'];
            $configtitle[$key] = $row['configtitle'];
            $configcontent[$key] = $row['configcontent'];
        }
        array_multisort($configtitle, SORT_ASC, $hooks['tabs']);
    } else {
        $hooks['tabs'] = [];
    }
    switch (strtolower($phase)) {
        case 'modify':
        default:
            switch ($data['tab']) {
                case 'comments_general':
                default:
                    //check for comments hook in case it's set independently elsewhere
                    if (xarHooks::isAttached('comments', 'roles')) {
                        $this->mod('comments')->setVar('usersetrendering', true);
                    } else {
                        $this->mod('comments')->setVar('usersetrendering', false);
                    }
                    break;
            }

            break;

        case 'update':
            // Confirm authorisation code
            // if (!xarSec::confirmAuthKey()) return;
            //$this->var()->find('itemsperpage', $itemsperpage, 'int', $this->mod()->getVar('itemsperpage'));
            //$this->var()->find('shorturls', $shorturls, 'checkbox', false);
            //$this->var()->find('modulealias', $useModuleAlias, 'checkbox', $this->mod()->getVar('useModuleAlias'));
            //$this->var()->find('aliasname', $aliasname, 'str', $this->mod()->getVar('aliasname'));
            $this->var()->find('editstamp', $editstamp, 'int', $this->mod()->getVar('editstamp'));

            $this->var()->find('wrap', $wrap, 'checkbox', $this->mod()->getVar('wrap'));
            $this->var()->find('numstats', $numstats, 'str', 20);

            $this->var()->find('rssnumitems', $rssnumitems, 'int', $this->mod()->getVar('rssnumitems'));
            $this->var()->find('showtitle', $showtitle, 'checkbox', $this->mod()->getVar('showtitle'));
            $this->var()->find('enable_comments', $showtitle, 'checkbox', $this->mod()->getVar('enable_comments'));

            $this->var()->find('filters_min_item_count', $filters_min_item_count, 'int', $this->mod()->getVar('filters_min_item_count'));
            $this->var()->find('filters_min_item_count', $filters_min_item_count, 'int', $this->mod()->getVar('filters_min_item_count'));

            $this->var()->find('postanon', $postanon, 'checkbox', $this->mod()->getVar('postanon'));
            $this->var()->find('useblacklist', $useblacklist, 'checkbox', $this->mod()->getVar('useblacklist'));
            $this->var()->find('useblacklist', $useblacklist, 'checkbox', 1);
            $this->var()->find('depth', $depth, 'str:1:', Defines::MAX_DEPTH);
            $this->var()->find('render', $render, 'str:1:', Defines::VIEW_THREADED);
            $this->var()->find('sortby', $sortby, 'str:1:', Defines::SORTBY_THREAD);
            $this->var()->find('order', $order, 'str:1:', Defines::SORT_ASC);
            // $this->var()->find('authorize', $authorize, 'checkbox', $this->mod()->getVar('authorize'));
            $this->var()->find('authorize', $authorize, 'checkbox', 1);
            $this->var()->find('usersetrendering', $usersetrendering, 'checkbox', $this->mod()->getVar('usersetrendering'));


            if ($data['tab'] == 'comments_general') {
                // $this->mod('comments')->setVar('itemsperpage', $itemsperpage);
                // $this->mod('comments')->setVar('supportshorturls', $shorturls);
                // $this->mod('comments')->setVar('useModuleAlias', $useModuleAlias);
                // $this->mod('comments')->setVar('aliasname', $aliasname);
                $this->mod('comments')->setVar('AllowPostAsAnon', $postanon);
                $this->mod('comments')->setVar('AuthorizeComments', $authorize);
                $this->mod('comments')->setVar('depth', $depth);
                $this->mod('comments')->setVar('render', $render);
                $this->mod('comments')->setVar('sortby', $sortby);
                $this->mod('comments')->setVar('order', $order);
                $this->mod('comments')->setVar('editstamp', $editstamp);
                $this->mod('comments')->setVar('wrap', $wrap);
                $this->mod('comments')->setVar('numstats', $numstats);
                $this->mod('comments')->setVar('rssnumitems', $rssnumitems);
                $this->mod('comments')->setVar('showtitle', $showtitle);
                $this->mod('comments')->setVar('useblacklist', $useblacklist);
                $this->mod('comments')->setVar('usersetrendering', $usersetrendering);
            }
            $regid = xarMod::getRegID($tabmodule);
            xarModItemVars::set('comments', 'AllowPostAsAnon', $postanon, $regid);
            xarModItemVars::set('comments', 'AuthorizeComments', $authorize, $regid);
            xarModItemVars::set('comments', 'depth', $depth, $regid);
            xarModItemVars::set('comments', 'render', $render, $regid);
            xarModItemVars::set('comments', 'sortby', $sortby, $regid);
            xarModItemVars::set('comments', 'order', $order, $regid);
            xarModItemVars::set('comments', 'editstamp', $editstamp, $regid);
            xarModItemVars::set('comments', 'wrap', $wrap, $regid);
            xarModItemVars::set('comments', 'numstats', $numstats, $regid);
            xarModItemVars::set('comments', 'rssnumitems', $rssnumitems, $regid);
            xarModItemVars::set('comments', 'showtitle', $showtitle, $regid);
            xarModItemVars::set('comments', 'useblacklist', $useblacklist, $regid);
            xarModItemVars::set('comments', 'usersetrendering', $usersetrendering, $regid);

            /* Blacklist feed unavailable
            $this->mod('comments')->setVar('useblacklist', $useblacklist);
            if ($useblacklist == true){
                if (!xarMod::apiFunc('comments', 'admin', 'import_blacklist')) return;
            }
            */
            if ($usersetrendering == true) {
                //check and hook Comments to roles if not already hooked
                if (!xarHooks::isAttached('comments', 'roles')) {
                    xarMod::apiFunc(
                        'modules',
                        'admin',
                        'enablehooks',
                        ['callerModName' => 'roles',
                            'hookModName' => 'comments', ]
                    );
                }
            } else {
                if (xarHooks::isAttached('comments', 'roles')) {
                    //unhook Comments from roles
                    xarMod::apiFunc(
                        'modules',
                        'admin',
                        'disablehooks',
                        ['callerModName' => 'roles',
                            'hookModName' => 'comments', ]
                    );
                }
            }

            $this->ctl()->redirect($this->mod()->getURL(
                'admin',
                'modifyconfig',
                ['tabmodule' => $tabmodule, 'tab' => $data['tab']]
            ));
            // Return
            return true;
    }
    $data['hooks'] = $hooks;
    $data['tabmodule'] = $tabmodule;
    $data['authid'] = xarSec::genAuthKey();
    return $data;
}
