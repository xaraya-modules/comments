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

namespace Xaraya\Modules\Comments\AdminGui;

use Xaraya\Modules\Comments\AdminGui;
use Xaraya\Modules\Comments\UserApi;
use Xaraya\Modules\MethodClass;
use sys;
use Exception;

sys::import('xaraya.modules.method');
sys::import('modules.comments.defines');

/**
 * comments admin stats function
 * @extends MethodClass<AdminGui>
 */
class StatsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * View Statistics about comments per module
     *
     * @see AdminGui::stats()
     */
    public function __invoke(array $args = [])
    {
        /** @var UserApi $userapi */
        $userapi = $this->userapi();
        // Security Check
        if (!$this->sec()->checkAccess('AdminComments')) {
            return;
        }

        $data['gt_items']     = 0;
        $data['gt_total']     = 0;
        $data['gt_inactive']  = 0;

        $modlist = $userapi->modcounts();

        $inactive = $userapi->modcounts(['status' => 'inactive']
        );

        $moditems = [];
        foreach ($modlist as $modid => $itemtypes) {
            $modinfo = $this->mod()->getInfo($modid);
            // Get the list of all item types for this module (if any)
            try {
                $mytypes = $this->mod()->apiFunc($modinfo['name'], 'user', 'getitemtypes');
            } catch (Exception $e) {
                $mytypes = [];
            }
            foreach ($itemtypes as $itemtype => $stats) {
                $moditem = [];
                $moditem['modid'] = $modid;
                $moditem['items'] = $stats['items'];
                $moditem['total'] = $stats['comments'];
                if (isset($inactive[$modid]) && isset($inactive[$modid][$itemtype])) {
                    $moditem['inactive'] = $inactive[$modid][$itemtype]['comments'];
                } else {
                    $moditem['inactive'] = 0;
                }
                if ($itemtype == 0) {
                    $moditem['modname'] = ucwords($modinfo['displayname']) . ': itemtype ' . $itemtype;
                    //    $moditem['modlink'] = $this->ctl()->getModuleURL($modinfo['name'],'user','main');
                } else {
                    if (isset($mytypes) && !empty($mytypes[$itemtype])) {
                        $moditem['modname'] = ucwords($modinfo['displayname']) . ': itemtype: ' . $itemtype . ' - ' . $mytypes[$itemtype]['label'];
                        //    $moditem['modlink'] = $mytypes[$itemtype]['url'];
                    } else {
                        $moditem['modname'] = ucwords($modinfo['displayname']) . ': itemtype ' . $itemtype;
                        //    $moditem['modlink'] = $this->ctl()->getModuleURL($modinfo['name'],'user','view',array('itemtype' => $itemtype));
                    }
                }
                $moditem['module_url'] = $this->mod()->getURL(
                    'admin',
                    'module_stats',
                    ['modid' => $modid,
                        'itemtype' => $itemtype, ]
                );

                $moditem['delete_url'] = $this->mod()->getURL(
                    'admin',
                    'delete',
                    ['dtype' => 'itemtype',
                        'modid' => $modid,
                        'redirect' => 'stats',
                        'itemtype' => $itemtype, ]
                );
                $moditems[] = $moditem;
                $data['gt_items'] += $moditem['items'];
                $data['gt_total'] += $moditem['total'];
                $data['gt_inactive'] += $moditem['inactive'];
            }
        }
        $data['moditems']             = $moditems;
        $data['delete_all_url']   = $this->mod()->getURL(
            'admin',
            'delete',
            ['dtype' => 'all', 'redirect' => 'stats']
        );

        return $data;
    }
}
