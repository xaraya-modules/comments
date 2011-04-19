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
/**
 * Delete a comment or a group of comments
 *
 * @author Carl P. Corliss (aka rabbitt)
 * @access private
 * @returns mixed description of return
 */
function comments_user_delete()
{

    if (!xarSecurityCheck('DeleteComments'))
        return;
    if (!xarVarFetch('confirm',    'bool',   $data['confirm'], false,       XARVAR_NOT_REQUIRED)) return;
    if (!xarVarFetch('deletebranch',    'bool',   $deletebranch, false,       XARVAR_NOT_REQUIRED)) return;

    $header   = xarController::getVar('header');
    //$receipt = xarRequest::getVar('receipt');

    xarVarFetch('objecturl', 'str', $data['objecturl'], '', XARVAR_NOT_REQUIRED);

    // Make sure some action was submitted
    /*if (!array_key_exists('action', $receipt))
        $receipt['action'] = 'confirm-delete';*/

    if (!isset($header['itemtype'])) {
        $header['itemtype'] = 0;
    }

    if ($data['confirm']) {
        $comments = xarMod::apiFunc('comments','user','get_one',
                                   array('id' => $header['id']));

        $header['modid'] = $comments[0]['modid'];
        $header['itemtype'] = $comments[0]['itemtype'];
        $header['objectid'] = $comments[0]['objectid'];

        // get the title and link of the original object
        $modinfo = xarModGetInfo($header['modid']);
        try{
            $itemlinks = xarMod::apiFunc($modinfo['name'],'user','getitemlinks',
                                       array('itemtype' => $header['itemtype'],
                                             'itemids' => array($header['objectid'])));
        } catch (Exception $e) {}
        if (!empty($itemlinks) && !empty($itemlinks[$header['objectid']])) {
            $url = $itemlinks[$header['objectid']]['url'];
            $header['objectlink'] = $itemlinks[$header['objectid']]['url'];
            $header['objecttitle'] = $itemlinks[$header['objectid']]['label'];
        } else {
            $url = xarModURL($modinfo['name'],'user','main');
        }
       /* if (empty($receipt['returnurl'])) {
            $receipt['returnurl'] = array('encoded' => rawurlencode($url),
                                          'decoded' => $url);
        }*/

        if ($deletebranch) {
            xarMod::apiFunc('comments','admin','delete_branch',
                              array('node' => $header['id']));
               xarResponse::redirect($data['objecturl']);
                return true;
        } else {
                xarMod::apiFunc('comments','admin','delete_node',
                              array('node' => $header['id'],
                                    'pid'  => $header['pid']));
                xarResponse::redirect($data['objecturl']);
                return true;
        }
    }

    $data['header'] = $header;
    //$data['receipt'] = $receipt;
    $data['package']['delete_url'] = xarModURL('comments','user','delete');

    $comments = xarMod::apiFunc('comments','user','get_one',
                                       array('id' => $header['id']));
    if ($comments[0]['right_id'] == $comments[0]['left_id'] + 1) {
        $data['package']['haschildren'] = false;
    } else {
        $data['package']['haschildren'] = true;
    }

    return $data;
}

?>