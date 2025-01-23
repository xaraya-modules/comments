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
 * Show comments deletion form
 *
 * This form allows one to delete comments for all hooked modules
 */
function comments_admin_delete(array $args = [], $context = null)
{
    if (!$this->sec()->checkAccess('AdminComments')) {
        return;
    }
    if (!$this->var()->get('dtype', $dtype, 'str:1:')) {
        return;
    }
    $delete_args = [];

    if (!isset($dtype) || !preg_match('/^(all|module|object)$/', $dtype)) {
        $msg = xarML('Invalid or Missing Parameter \'dtype\'');
        throw new BadParameterException($msg);
    } else {
        $delete_args['dtype'] = $dtype;
        $output['dtype'] = $dtype;

        switch (strtolower($dtype)) {
            case 'object':
                if (!$this->var()->get('objectid', $objectid, 'int:1')) {
                    return;
                }

                if (!isset($objectid) || empty($objectid)) {
                    $msg = xarML('Invalid or Missing Parameter \'objectid\'');
                    throw new BadParameterException($msg);
                }
                $output['objectid'] = $objectid;
                $delete_args['objectid'] = $objectid;

                // if dtype == object, then fall through to
                // the module section below cuz we need both
                // the module id and the object id
                // no break
            case 'module':
                if (!$this->var()->get('modid', $modid, 'int:1')) {
                    return;
                }

                if (!isset($modid) || empty($modid)) {
                    $msg = xarML('Invalid or Missing Parameter \'modid\'');
                    throw new BadParameterException($msg);
                }
                if (!$this->var()->get('itemtype', $itemtype, 'int:1')) {
                    return;
                }
                if (empty($itemtype)) {
                    $itemtype = 0;
                }
                $modinfo = xarMod::getInfo($modid);
                $output['modname']    = $modinfo['name'];
                $delete_args['modid'] = $modid;
                $delete_args['itemtype'] = $itemtype;
                break;
            case 'all':
                $output['modname']    = '\'ALL MODULES\'';
                break;
            default:
                $msg = xarML('Invalid or Missing Parameter \'dtype\'');
                throw new Exception($msg);
        }
    }

    if (!$this->var()->find('submitted', $submitted, 'str:1:', '')) {
        return;
    }
    // if we're gathering submitted info form the delete
    // confirmation then we are ok to check delete choice,
    // then delete in the manner specified (or not) and
    // then redirect to the Comment's Statistics page
    if (isset($submitted) && !empty($submitted)) {
        // Confirm authorisation code
        if (!xarSec::confirmAuthKey()) {
            return;
        }

        if (!$this->var()->get('choice', $choice, 'str:1:')) {
            return;
        }

        // if choice isn't set or it has an incorrect value,
        // redirect back to the choice page
        if (!isset($choice) || !preg_match('/^(yes|no|true|false)$/', $choice)) {
            $this->ctl()->redirect($this->mod()->getURL('admin', 'delete', $delete_args));
        }

        if ($choice == 'yes' || $choice == 'true') {
            if (!xarMod::apiLoad('comments', 'user')) {
                $this->exit("COULDN'T LOAD API!!!");
            }
            $retval = true;

            switch (strtolower($dtype)) {
                case 'module':
                    xarMod::apiFunc(
                        'comments',
                        'admin',
                        'delete_module_nodes',
                        ['modid' => $modid,
                            'itemtype' => $itemtype, ]
                    );
                    break;
                case 'object':
                    xarMod::apiFunc(
                        'comments',
                        'admin',
                        'delete_object_nodes',
                        ['modid'    => $modid,
                            'itemtype' => $itemtype,
                            'objectid' => $objectid, ]
                    );
                    break;
                case 'all':
                    $dbconn = $this->db()->getConn();
                    $xartable = & $this->db()->getTables();

                    $sql = "DELETE
                              FROM  $xartable[comments]";

                    $result = & $dbconn->Execute($sql);

                    break;
                default:
                    $retval = false;
            }

            if (!$retval) {
                $msg = xarML('Unable to delete comments!');
                throw new BadParameterException($msg);
            }
        } else {
            if (isset($modid)) {
                $this->ctl()->redirect($this->mod()->getURL(
                    'admin',
                    'module_stats',
                    ['modid' => $modid,
                        'itemtype' => empty($itemtype) ? null : $itemtype, ]
                ));
            } else {
                $this->ctl()->redirect($this->mod()->getURL('admin', 'stats'));
            }
        }

        if (isset($modid) && strtolower($dtype) == 'object') {
            $this->ctl()->redirect($this->mod()->getURL(
                'admin',
                'module_stats',
                ['modid' => $modid,
                    'itemtype' => empty($itemtype) ? null : $itemtype, ]
            ));
        } else {
            $this->ctl()->redirect($this->mod()->getURL('admin', 'stats'));
        }
    }
    // If we're here, then we haven't received authorization
    // to delete any comments yet - so here we ask for confirmation.
    $output['authid'] = xarSec::genAuthKey();
    $output['delete_url'] = $this->mod()->getURL('admin', 'delete', $delete_args);

    return $output;
}
