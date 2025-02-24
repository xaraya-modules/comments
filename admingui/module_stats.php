<?php

/**
 * @package modules\comments
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Comments\AdminGui;

use Xaraya\Modules\Comments\AdminGui;
use Xaraya\Modules\Comments\UserApi;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarVar;
use xarMod;
use xarController;
use xarModVars;
use xarTplPager;
use sys;
use BadParameterException;
use Exception;

sys::import('xaraya.modules.method');

/**
 * comments admin module_stats function
 * @extends MethodClass<AdminGui>
 */
class ModuleStatsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Comments Module
     * @package modules
     * @subpackage comments
     * @category Third Party Xaraya Module
     * @version 2.4.0
     * @copyright see the html/credits.html file in this release
     * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
     * @link http://xaraya.com/index.php/release/14.html
     * @author Carl P. Corliss <rabbitt@xaraya.com>
     * @see AdminGui::moduleStats()
     */
    public function __invoke(array $args = [])
    {
        /** @var UserApi $userapi */
        $userapi = $this->userapi();
        // Security Check
        if (!$this->sec()->checkAccess('AdminComments')) {
            return;
        }
        $this->var()->get('modid', $modid, 'int:1');
        $this->var()->find('itemtype', $urlitemtype, 'int:0', 0);

        if (!isset($modid) || empty($modid)) {
            $msg = $this->ml('Invalid or Missing Parameter \'modid\'');
            throw new BadParameterException($msg);
        }

        $modinfo = $this->mod()->getInfo($modid);
        $data['modname'] = ucwords($modinfo['displayname']);
        if (empty($urlitemtype)) {
            $urlitemtype = -1;
        } else {
            $data['itemtype'] = $urlitemtype;
            // Get the list of all item types for this module (if any)
            try {
                $mytypes = $this->mod()->apiFunc($modinfo['name'], 'user', 'getitemtypes');
            } catch (Exception $e) {
                $mytypes = [];
            }
            if (isset($mytypes) && !empty($mytypes[$urlitemtype])) {
                $data['itemtypelabel'] = $mytypes[$urlitemtype]['label'];
                //$data['modlink'] = $mytypes[$urlitemtype]['url'];
            } else {
                //$data['modlink'] = $this->ctl()->getModuleURL($modinfo['name'],'user','view',array('itemtype' => $urlitemtype));
            }
        }

        $numstats = $this->mod()->getVar('numstats');
        if (empty($numstats)) {
            $numstats = 100;
        }
        $this->var()->check('startnum', $startnum, 'id');
        if (empty($startnum)) {
            $startnum = 1;
        }

        $args = ['modid' => $modid, 'numitems' => $numstats, 'startnum' => $startnum];
        $this->var()->find('itemtype', $itemtypearg, 'int');
        if (isset($itemtypearg)) {
            $args['itemtype'] = $itemtypearg;
        }
        // all the items and their number of comments (excluding root nodes) for this module
        $moditems = $userapi->moditemcounts($args
        );

        // inactive
        $args['status'] = 'inactive';
        $inactive = $userapi->moditemcounts($args
        );

        // get the title and url for the items
        $showtitle = $this->mod()->getVar('showtitle');
        if (!empty($showtitle)) {
            $itemids = array_keys($moditems);
            try {
                $itemlinks = $this->mod()->apiFunc(
                    $modinfo['name'],
                    'user',
                    'getitemlinks',
                    ['itemtype' => $urlitemtype,
                        'itemids' => $itemids]
                );
            } catch (Exception $e) {
                $itemlinks = [];
            }
        } else {
            $itemlinks = [];
        }

        $stats = [];

        $data['gt_total']     = 0;
        $data['gt_inactive']  = 0;

        foreach ($moditems as $itemid => $info) {
            $stats[$itemid] = [];
            $stats[$itemid]['pageid'] = $itemid;
            $stats[$itemid]['total'] = $info['count'];
            $stats[$itemid]['delete_url'] = $this->mod()->getURL(
                'admin',
                'delete',
                ['dtype' => 'object',
                    'modid' => $modid,
                    'itemtype' => $info['itemtype'],
                    'objectid' => $itemid,
                    'redirect' => $modid,
                ]
            );
            $data['gt_total'] += $info['count'];
            if (isset($inactive[$itemid])) {
                $stats[$itemid]['inactive'] = $inactive[$itemid]['count'];
                $data['gt_inactive'] += (int) $inactive[$itemid]['count'];
            } else {
                $stats[$itemid]['inactive'] = 0;
            }
            if (isset($itemlinks[$itemid])) {
                $stats[$itemid]['link'] = $itemlinks[$itemid]['url'];
                $stats[$itemid]['title'] = $itemlinks[$itemid]['label'];
            }
        }

        $data['data']             = $stats;
        if (isset($urlitemtype) && $urlitemtype > 0) {
            $dalltype = 'itemtype';
        } else {
            $dalltype = 'module';
        }
        $data['delete_all_url']   = $this->mod()->getURL(
            'admin',
            'delete',
            ['dtype' => $dalltype,
                'modid' => $modid,
                'itemtype' => $urlitemtype,
                'redirect' => 'stats',
            ]
        );

        // get statistics for all comments (excluding root nodes)
        $modlist = $userapi->modcounts(['modid' => $modid,
                'itemtype' => $urlitemtype, ]
        );
        if (isset($modlist[$modid]) && isset($modlist[$modid][$urlitemtype])) {
            $numitems = $modlist[$modid][$urlitemtype]['items'];
        } else {
            $numitems = 0;
        }
        if ($numstats < $numitems) {
            $data['pager'] = $this->tpl()->getPager(
                $startnum,
                $numitems,
                $this->mod()->getURL(
                    'admin',
                    'module_stats',
                    ['modid' => $modid,
                        'itemtype' => $urlitemtype,
                        'startnum' => '%%', ]
                ),
                $numstats
            );
        } else {
            $data['pager'] = '';
        }

        return $data;
    }
}
