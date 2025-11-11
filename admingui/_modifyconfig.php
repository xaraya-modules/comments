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
        //$this->mod()->setVar('rssnumitems', 25);
    }
    if (empty($numstats)) {
        //$this->mod()->setVar('numstats', 100);
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
                        $this->mod()->setVar('usersetrendering', true);
                    } else {
                        $this->mod()->setVar('usersetrendering', false);
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
                // $this->mod()->setVar('itemsperpage', $itemsperpage);
                // $this->mod()->setVar('supportshorturls', $shorturls);
                // $this->mod()->setVar('useModuleAlias', $useModuleAlias);
                // $this->mod()->setVar('aliasname', $aliasname);
                $this->mod()->setVar('AllowPostAsAnon', $postanon);
                $this->mod()->setVar('AuthorizeComments', $authorize);
                $this->mod()->setVar('depth', $depth);
                $this->mod()->setVar('render', $render);
                $this->mod()->setVar('sortby', $sortby);
                $this->mod()->setVar('order', $order);
                $this->mod()->setVar('editstamp', $editstamp);
                $this->mod()->setVar('wrap', $wrap);
                $this->mod()->setVar('numstats', $numstats);
                $this->mod()->setVar('rssnumitems', $rssnumitems);
                $this->mod()->setVar('showtitle', $showtitle);
                $this->mod()->setVar('useblacklist', $useblacklist);
                $this->mod()->setVar('usersetrendering', $usersetrendering);
            }
            $regid = xarMod::getRegID($tabmodule);
            $this->mod()->setItemVar('AllowPostAsAnon', $postanon, $regid);
            $this->mod()->setItemVar('AuthorizeComments', $authorize, $regid);
            $this->mod()->setItemVar('depth', $depth, $regid);
            $this->mod()->setItemVar('render', $render, $regid);
            $this->mod()->setItemVar('sortby', $sortby, $regid);
            $this->mod()->setItemVar('order', $order, $regid);
            $this->mod()->setItemVar('editstamp', $editstamp, $regid);
            $this->mod()->setItemVar('wrap', $wrap, $regid);
            $this->mod()->setItemVar('numstats', $numstats, $regid);
            $this->mod()->setItemVar('rssnumitems', $rssnumitems, $regid);
            $this->mod()->setItemVar('showtitle', $showtitle, $regid);
            $this->mod()->setItemVar('useblacklist', $useblacklist, $regid);
            $this->mod()->setItemVar('usersetrendering', $usersetrendering, $regid);

            /* Blacklist feed unavailable
            $this->mod()->setVar('useblacklist', $useblacklist);
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
