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
function comments_admin_module_stats(array $args = [], $context = null)
{
    // Security Check
    if (!$this->sec()->checkAccess('AdminComments')) {
        return;
    }
    if (!$this->var()->get('modid', $modid, 'int:1')) {
        return;
    }
    if (!$this->var()->find('itemtype', $itemtype, 'int:0', 0)) {
        return;
    }

    if (!isset($modid) || empty($modid)) {
        $msg = xarML('Invalid or Missing Parameter \'modid\'');
        throw new BadParameterException($msg);
    }

    $modinfo = xarMod::getInfo($modid);
    if (empty($itemtype)) {
        $data['modname'] = ucwords($modinfo['displayname']);
        $itemtype = 0;
    } else {
        // Get the list of all item types for this module (if any)
        try {
            $mytypes = xarMod::apiFunc($modinfo['name'], 'user', 'getitemtypes');
        } catch (Exception $e) {
            $mytypes = [];
        }
        if (isset($mytypes) && !empty($mytypes[$itemtype])) {
            $data['modname'] = ucwords($modinfo['displayname']) . ' ' . $itemtype . ' - ' . $mytypes[$itemtype]['label'];
            //    $data['modlink'] = $mytypes[$itemtype]['url'];
        } else {
            $data['modname'] = ucwords($modinfo['displayname']) . ' ' . $itemtype;
            //    $data['modlink'] = xarController::URL($modinfo['name'],'user','view',array('itemtype' => $itemtype));
        }
    }

    $numstats = xarModVars::get('comments', 'numstats');
    if (empty($numstats)) {
        $numstats = 100;
    }
    if (!$this->var()->check('startnum', $startnum, 'id')) {
        return;
    }
    if (empty($startnum)) {
        $startnum = 1;
    }

    // get all items and their number of comments (excluding root nodes) for this module
    $moditems = xarMod::apiFunc(
        'comments',
        'user',
        'getitems',
        ['moditemscommentcount' => true,
            'modid' => $modid,
            'itemtype' => $itemtype,
            'numitems' => $numstats,
            'startnum' => $startnum, ]
    );

    // get the number of inactive comments for these items
    $inactive = xarMod::apiFunc(
        'comments',
        'user',
        'getitems',
        ['modid' => $modid,
            'itemtype' => $itemtype,
            'itemids' => array_keys($moditems),
            'status' => 'inactive', ]
    );

    // get the title and url for the items
    $showtitle = xarModVars::get('comments', 'showtitle');
    if (!empty($showtitle)) {
        $itemids = array_keys($moditems);
        try {
            $itemlinks = xarMod::apiFunc(
                $modinfo['name'],
                'user',
                'getitemlinks',
                ['itemtype' => $itemtype,
                    'itemids' => $itemids]
            );
        } catch (Exception $e) {
            $itemlinks = [];
        }
    } else {
        $itemlinks = [];
    }

    $pages = [];

    $data['gt_total']     = 0;
    $data['gt_inactive']  = 0;

    foreach ($moditems as $itemid => $numcomments) {
        $pages[$itemid] = [];
        $pages[$itemid]['pageid'] = $itemid;
        $pages[$itemid]['total'] = $numcomments;
        $pages[$itemid]['delete_url'] = $this->mod()->getURL(
            'admin',
            'delete',
            ['dtype' => 'object',
                'modid' => $modid,
                'itemtype' => $itemtype,
                'objectid' => $itemid, ]
        );
        $data['gt_total'] .= $numcomments;
        if (isset($inactive[$itemid])) {
            $pages[$itemid]['inactive'] = $inactive[$itemid];
            $data['gt_inactive'] += $inactive[$itemid];
        } else {
            $pages[$itemid]['inactive'] = 0;
        }
        if (isset($itemlinks[$itemid])) {
            $pages[$itemid]['link'] = $itemlinks[$itemid]['url'];
            $pages[$itemid]['title'] = $itemlinks[$itemid]['label'];
        }
    }

    $data['data']             = $pages;
    $data['delete_all_url']   = $this->mod()->getURL(
        'admin',
        'delete',
        ['dtype' => 'module',
            'modid' => $modid,
            'itemtype' => $itemtype, ]
    );

    // get statistics for all comments (excluding root nodes)
    $modlist = xarMod::apiFunc(
        'comments',
        'user',
        'getmodules',
        ['modid' => $modid,
            'itemtype' => $itemtype, ]
    );
    if (isset($modlist[$modid]) && isset($modlist[$modid][$itemtype])) {
        $numitems = $modlist[$modid][$itemtype]['items'];
    } else {
        $numitems = 0;
    }
    if ($numstats < $numitems) {
        sys::import('modules.base.class.pager');
        $data['pager'] = xarTplPager::getPager(
            $startnum,
            $numitems,
            $this->mod()->getURL(
                'admin',
                'module_stats',
                ['modid' => $modid,
                    'itemtype' => $itemtype,
                    'startnum' => '%%', ]
            ),
            $numstats
        );
    } else {
        $data['pager'] = '';
    }

    return $data;
}
