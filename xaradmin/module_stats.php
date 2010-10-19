<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @copyright (C) 2002-2007 The copyright-placeholder
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage comments
 * @link http://xaraya.com/index.php/release/14.html
 * @author Carl P. Corliss <rabbitt@xaraya.com>
 */
function comments_admin_module_stats( )
{

    // Security Check
    if(!xarSecurityCheck('AdminComments')) return;
    if (!xarVarFetch('modid','int:1',$modid)) return;
    if (!xarVarFetch('itemtype','int:0',$itemtype,0,XARVAR_NOT_REQUIRED)) return;

    if (!isset($modid) || empty($modid)) {
        $msg = xarML('Invalid or Missing Parameter \'modid\'');
        throw new BadParameterException($msg);
    }

    $modinfo = xarModGetInfo($modid);
    if (empty($itemtype)) {
        $output['modname'] = ucwords($modinfo['displayname']);
        $itemtype = 0;
    } else {
        // Get the list of all item types for this module (if any)
        $mytypes = xarMod::apiFunc($modinfo['name'],'user','getitemtypes',
                                 // don't throw an exception if this function doesn't exist
                                 array(), 0);
        if (isset($mytypes) && !empty($mytypes[$itemtype])) {
            $output['modname'] = ucwords($modinfo['displayname']) . ' ' . $itemtype . ' - ' . $mytypes[$itemtype]['label'];
        //    $output['modlink'] = $mytypes[$itemtype]['url'];
        } else {
            $output['modname'] = ucwords($modinfo['displayname']) . ' ' . $itemtype;
        //    $output['modlink'] = xarModURL($modinfo['name'],'user','view',array('itemtype' => $itemtype));
        }
    }

    $numstats = xarModVars::get('comments','numstats');
    if (empty($numstats)) {
        $numstats = 100;
    }
    if (!xarVarFetch('startnum', 'id', $startnum, NULL, XARVAR_DONT_SET)) return;
    if (empty($startnum)) {
        $startnum = 1;
    }

    // get all items and their number of comments (excluding root nodes) for this module
    $moditems = xarMod::apiFunc('comments','user','getitems',
                              array('modid' => $modid,
                                    'itemtype' => $itemtype,
                                    'numitems' => $numstats,
                                    'startnum' => $startnum));

    // get the number of inactive comments for these items
    $inactive = xarMod::apiFunc('comments','user','getitems',
                              array('modid' => $modid,
                                    'itemtype' => $itemtype,
                                    'itemids' => array_keys($moditems),
                                    'status' => 'inactive'));

    // get the title and url for the items
    $showtitle = xarModVars::get('comments','showtitle');
    if (!empty($showtitle)) {
       $itemids = array_keys($moditems);
       try{
           $itemlinks = xarMod::apiFunc($modinfo['name'],'user','getitemlinks',
                                      array('itemtype' => $itemtype,
                                            'itemids' => $itemids)); // don't throw an exception here
        } catch (Exception $e) {}
    } else {
       $itemlinks = array();
    }

    $pages = array();

    $output['gt_total']     = 0;
    $output['gt_inactive']  = 0;

    foreach ($moditems as $itemid => $numcomments) {
        $pages[$itemid] = array();
        $pages[$itemid]['pageid'] = $itemid;
        $pages[$itemid]['total'] = $numcomments;
        $pages[$itemid]['delete_url'] = xarModURL('comments','admin', 'delete',
                                                  array('dtype' => 'object',
                                                        'modid' => $modid,
                                                        'itemtype' => $itemtype,
                                                        'objectid' => $itemid));
        $output['gt_total'] += $numcomments;
        if (isset($inactive[$itemid])) {
            $pages[$itemid]['inactive'] = $inactive[$itemid];
            $output['gt_inactive'] += $inactive[$itemid];
        } else {
            $pages[$itemid]['inactive'] = 0;
        }
        if (isset($itemlinks[$itemid])) {
            $pages[$itemid]['link'] = $itemlinks[$itemid]['url'];
            $pages[$itemid]['title'] = $itemlinks[$itemid]['label'];
        }
    }

    $output['data']             = $pages;
    $output['delete_all_url']   = xarModURL('comments','admin','delete',
                                            array('dtype' => 'module',
                                                  'modid' => $modid,
                                                  'itemtype' => $itemtype));

    // get statistics for all comments (excluding root nodes)
    $modlist = xarMod::apiFunc('comments','user','getmodules',
                             array('modid' => $modid,
                                   'itemtype' => $itemtype));
    if (isset($modlist[$modid]) && isset($modlist[$modid][$itemtype])) {
        $numitems = $modlist[$modid][$itemtype]['items'];
    } else {
        $numitems = 0;
    }
    if ($numstats < $numitems) {
        $output['pager'] = xarTplPager::getPager($startnum,
                                          $numitems,
                                          xarModURL('comments','admin','module_stats',
                                                    array('modid' => $modid,
                                                          'itemtype' => $itemtype,
                                                          'startnum' => '%%')),
                                          $numstats);
    } else {
        $output['pager'] = '';
    }

    return $output;

}

?>