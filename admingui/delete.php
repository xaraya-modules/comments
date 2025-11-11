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
function comments_admin_delete(array $data = [], $context = null)
{
    if (!$this->sec()->checkAccess('ManageComments')) {
        return;
    }

    $this->var()->find('confirm', $data['confirm'], 'bool', false);
    $this->var()->find('deletebranch', $deletebranch, 'bool', false);
    $this->var()->find('redirect', $data['redirect'], 'str', '');
    $this->var()->find('itemtype', $data['itemtype'], 'str');
    $this->var()->find('dtype', $data['dtype'], 'str', "");

    if (empty($data['dtype'])) {
        return $this->ctl()->notFound();
    }


    switch (strtolower($data['dtype'])) {
        case 'item': // delete just one comment
            $this->var()->get('itemid', $itemid, 'int');

            $object = DataObjectFactory::getObject(['name' => 'comments_comments']);
            $object->getItem(['itemid' => $itemid]);
            $values = $object->getFieldValues();
            foreach ($values as $key => $val) {
                $data[$key] = $val;
            }

            $delete_args['id'] = $itemid;

            break;
        case 'object': // delete all comments for a content item
            $this->var()->find('itemtype', $itemtype, 'int', 0);
            $this->var()->get('modid', $modid, 'int:1');
            $this->var()->get('objectid', $objectid, 'int:1');

            $filters['where'] = 'itemtype eq ' . $itemtype . ' and modid eq ' . $modid . ' and objectid eq ' . $objectid;

            $delete_args['itemtype'] = $itemtype;
            $delete_args['modid'] = $modid;
            $delete_args['objectid'] = $objectid;

            break;
        case 'itemtype': // delete all comments for an itemtype
            $this->var()->get('itemtype', $itemtype, 'int');
            $this->var()->get('modid', $modid, 'int:1');

            $filters['where'] = 'itemtype eq ' . $itemtype . ' and modid eq ' . $modid;

            $delete_args['itemtype'] = $itemtype;
            $delete_args['modid'] = $modid;

            break;
        case 'module':  // delete all comments for a module
            $this->var()->get('modid', $modid, 'int:1');

            $filters['where'] = 'modid eq ' . $modid;

            $delete_args['modid'] = $modid;

            break;
        case 'all': // delete all comments
            $filters = [];
            $delete_args = [];
            break;
    }

    if ($data['dtype'] != 'item') { // multiple items
        $list = DataObjectFactory::getObjectList([
            'name' => 'comments',
        ]);
        $data['items'] = $list->getItems($filters);

        $countlist = DataObjectFactory::getObjectList([
            'name' => 'comments',
        ]);
        if ($data['dtype'] == 'all') {
            $filters['where'] = 'status ne 3';
        } else {
            $filters['where'] .= ' and status ne 3';
            $modinfo = xarMod::getInfo($modid);
            $data['modname'] = $modinfo['displayname'];
        }
        $countitems = $countlist->getItems($filters);
        $data['count'] = count($countitems);

        if ($data['confirm'] && is_array($data['items'])) {
            if (!$this->sec()->confirmAuthKey()) {
                return;
            }

            if (!empty($data['items'])) {
                foreach ($data['items'] as $val) {
                    $object = DataObjectFactory::getObject([
                        'name' => 'comments_comments',
                    ]);
                    if (!is_object($object)) {
                        return;
                    }
                    $object->deleteItem(['itemid' => $val['id']]);
                }
            }
        }
    } else { // $data['dtype'] == 'item'
        if ($data['confirm']) {
            if (!$this->sec()->confirmAuthKey()) {
                return;
            }
            if ($deletebranch) {
                xarMod::apiFunc('comments', 'admin', 'delete_branch', ['node' => $id]);
            } else {
                xarMod::apiFunc('comments', 'admin', 'delete_node', ['node' => $id, 'parent_id' => $values['parent_id']]);
            }
        } else {
            $comments = xarMod::apiFunc('comments', 'user', 'get_one', ['id' => $itemid]);

            if ($comments[0]['position_atomic']['right'] == $comments[0]['position_atomic']['left'] + 1) {
                $data['haschildren'] = false;
            } else {
                $data['haschildren'] = true;
            }
        }
    }

    $data['authid'] = xarSec::genAuthKey();

    $data['delete_args'] = $delete_args;

    if ($data['confirm'] && !empty($data['redirect'])) {
        if ($data['redirect'] == 'view') {
            $this->ctl()->redirect($this->mod()->getURL('admin', 'view'));
        } elseif ($data['redirect'] == 'stats') {
            $this->ctl()->redirect($this->mod()->getURL('admin', 'stats'));
        } elseif (is_numeric($data['redirect'])) {
            $this->ctl()->redirect($this->mod()->getURL(
                'admin',
                'module_stats',
                ['modid' => $data['redirect']]
            ));
        }
    }

    return $data;
}
