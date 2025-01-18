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
        //xarModVars::set('comments', 'rssnumitems', 25);
    }
    if (empty($numstats)) {
        //xarModVars::set('comments', 'numstats', 100);
    }

    if (!$this->var()->find('phase', $phase, 'str:1:100', 'modify')) {
        return;
    }
    if (!$this->var()->find('tab', $data['tab'], 'str:1:100', 'comments_general')) {
        return;
    }
    if (!$this->var()->find('tabmodule', $tabmodule, 'str:1:100', 'comments')) {
        return;
    }
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
                    if (xarModHooks::isHooked('comments', 'roles')) {
                        xarModVars::set('comments', 'usersetrendering', true);
                    } else {
                        xarModVars::set('comments', 'usersetrendering', false);
                    }
                    break;
            }

            break;

        case 'update':
            // Confirm authorisation code
            // if (!xarSec::confirmAuthKey()) return;
            //if (!$this->var()->find('itemsperpage', $itemsperpage, 'int', $this->mod()->getVar('itemsperpage'))) return;
            //if (!$this->var()->find('shorturls', $shorturls, 'checkbox', false)) return;
            //if (!$this->var()->find('modulealias', $useModuleAlias, 'checkbox', $this->mod()->getVar('useModuleAlias'))) return;
            //if (!$this->var()->find('aliasname', $aliasname, 'str', $this->mod()->getVar('aliasname'))) return;
            if (!$this->var()->find('editstamp', $editstamp, 'int', $this->mod()->getVar('editstamp'))) {
                return;
            }

            if (!$this->var()->find('wrap', $wrap, 'checkbox', $this->mod()->getVar('wrap'))) {
                return;
            }
            if (!$this->var()->find('numstats', $numstats, 'str', 20)) {
                return;
            }

            if (!$this->var()->find('rssnumitems', $rssnumitems, 'int', $this->mod()->getVar('rssnumitems'))) {
                return;
            }
            if (!$this->var()->find('showtitle', $showtitle, 'checkbox', $this->mod()->getVar('showtitle'))) {
                return;
            }
            if (!$this->var()->find('enable_comments', $showtitle, 'checkbox', $this->mod()->getVar('enable_comments'))) {
                return;
            }

            if (!$this->var()->find('filters_min_item_count', $filters_min_item_count, 'int', $this->mod()->getVar('filters_min_item_count'))) {
                return;
            }
            if (!$this->var()->find('filters_min_item_count', $filters_min_item_count, 'int', $this->mod()->getVar('filters_min_item_count'))) {
                return;
            }

            if (!$this->var()->find('postanon', $postanon, 'checkbox', $this->mod()->getVar('postanon'))) {
                return;
            }
            if (!$this->var()->find('useblacklist', $useblacklist, 'checkbox', $this->mod()->getVar('useblacklist'))) {
                return;
            }
            if (!$this->var()->find('useblacklist', $useblacklist, 'checkbox', 1)) {
                return;
            }
            if (!$this->var()->find('depth', $depth, 'str:1:', Defines::MAX_DEPTH)) {
                return;
            }
            if (!$this->var()->find('render', $render, 'str:1:', Defines::VIEW_THREADED)) {
                return;
            }
            if (!$this->var()->find('sortby', $sortby, 'str:1:', Defines::SORTBY_THREAD)) {
                return;
            }
            if (!$this->var()->find('order', $order, 'str:1:', Defines::SORT_ASC)) {
                return;
            }
            // if (!$this->var()->find('authorize', $authorize, 'checkbox', $this->mod()->getVar('authorize'))) return;
            if (!$this->var()->find('authorize', $authorize, 'checkbox', 1)) {
                return;
            }
            if (!$this->var()->find('usersetrendering', $usersetrendering, 'checkbox', $this->mod()->getVar('usersetrendering'))) {
                return;
            }


            if ($data['tab'] == 'comments_general') {
                // xarModVars::set('comments', 'itemsperpage', $itemsperpage);
                // xarModVars::set('comments', 'supportshorturls', $shorturls);
                // xarModVars::set('comments', 'useModuleAlias', $useModuleAlias);
                // xarModVars::set('comments', 'aliasname', $aliasname);
                xarModVars::set('comments', 'AllowPostAsAnon', $postanon);
                xarModVars::set('comments', 'AuthorizeComments', $authorize);
                xarModVars::set('comments', 'depth', $depth);
                xarModVars::set('comments', 'render', $render);
                xarModVars::set('comments', 'sortby', $sortby);
                xarModVars::set('comments', 'order', $order);
                xarModVars::set('comments', 'editstamp', $editstamp);
                xarModVars::set('comments', 'wrap', $wrap);
                xarModVars::set('comments', 'numstats', $numstats);
                xarModVars::set('comments', 'rssnumitems', $rssnumitems);
                xarModVars::set('comments', 'showtitle', $showtitle);
                xarModVars::set('comments', 'useblacklist', $useblacklist);
                xarModVars::set('comments', 'usersetrendering', $usersetrendering);
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
            xarModVars::set('comments', 'useblacklist', $useblacklist);
            if ($useblacklist == true){
                if (!xarMod::apiFunc('comments', 'admin', 'import_blacklist')) return;
            }
            */
            if ($usersetrendering == true) {
                //check and hook Comments to roles if not already hooked
                if (!xarModHooks::isHooked('comments', 'roles')) {
                    xarMod::apiFunc(
                        'modules',
                        'admin',
                        'enablehooks',
                        ['callerModName' => 'roles',
                            'hookModName' => 'comments', ]
                    );
                }
            } else {
                if (xarModHooks::isHooked('comments', 'roles')) {
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
