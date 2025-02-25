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
/**
 * View Statistics about comments per module
 *
 */
function comments_admin_stats(array $args = [], $context = null)
{
    // Security Check
    if (!$this->sec()->checkAccess('AdminComments')) {
        return;
    }

    $output['gt_pages']     = 0;
    $output['gt_total']     = 0;
    $output['gt_inactive']  = 0;

    // get statistics for all comments (excluding root nodes)
    $modlist = xarMod::apiFunc('comments', 'user', 'getmodules');

    // get statistics for all inactive comments
    $inactive = xarMod::apiFunc(
        'comments',
        'user',
        'getmodules',
        ['status' => 'inactive']
    );

    $data = [];
    foreach ($modlist as $modid => $itemtypes) {
        $modinfo = xarMod::getInfo($modid);
        // Get the list of all item types for this module (if any)
        try {
            $mytypes = xarMod::apiFunc($modinfo['name'], 'user', 'getitemtypes');
        } catch (Exception $e) {
            $mytypes = [];
        }
        foreach ($itemtypes as $itemtype => $stats) {
            $moditem = [];
            $moditem['modid'] = $modid;
            $moditem['pages'] = $stats['items'];
            $moditem['total'] = $stats['comments'];
            if (isset($inactive[$modid]) && isset($inactive[$modid][$itemtype])) {
                $moditem['inactive'] = $inactive[$modid][$itemtype]['comments'];
            } else {
                $moditem['inactive'] = 0;
            }
            if ($itemtype == 0) {
                $moditem['modname'] = ucwords($modinfo['displayname']);
                //    $moditem['modlink'] = $this->ctl()->getModuleURL($modinfo['name'], 'user', 'main');
            } else {
                if (isset($mytypes) && !empty($mytypes[$itemtype])) {
                    $moditem['modname'] = ucwords($modinfo['displayname']) . ' ' . $itemtype . ' - ' . $mytypes[$itemtype]['label'];
                    //    $moditem['modlink'] = $mytypes[$itemtype]['url'];
                } else {
                    $moditem['modname'] = ucwords($modinfo['displayname']) . ' ' . $itemtype;
                    //    $moditem['modlink'] = $this->ctl()->getModuleURL($modinfo['name'], 'user', 'view', array('itemtype' => $itemtype));
                }
            }
            $moditem['module_url'] = $this->mod()->getURL(
                'admin',
                'module_stats',
                ['modid' => $modid,
                    'itemtype' => empty($itemtype) ? null : $itemtype, ]
            );
            $moditem['delete_url'] = $this->mod()->getURL(
                'admin',
                'delete',
                ['dtype' => 'module',
                    'modid' => $modid,
                    'itemtype' => empty($itemtype) ? null : $itemtype, ]
            );
            $data[] = $moditem;
            $output['gt_pages'] += $moditem['pages'];
            $output['gt_total'] += $moditem['total'];
        }
    }
    $output['data']             = $data;
    $output['delete_all_url']   = $this->mod()->getURL(
        'admin',
        'delete',
        ['dtype' => 'all']
    );

    return $output;
}
