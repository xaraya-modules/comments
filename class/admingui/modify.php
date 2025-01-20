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
use Xaraya\Modules\MethodClass;
use xarVar;
use xarTpl;
use xarSecurity;
use xarSec;
use xarController;
use DataObjectFactory;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * comments admin modify function
 * @extends MethodClass<AdminGui>
 */
class ModifyMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * modify an item
     * This function shows a form in which the user can modify the item
     * @param array<mixed> $args
     *     id itemid The id of the dynamic data item to modify
     */
    public function __invoke(array $args = [])
    {
        if (!$this->var()->check('id', $id, 'id')) {
            return;
        }
        if (!$this->var()->check('parent_url', $parent_url, 'str')) {
            return;
        }
        if (!$this->var()->find('confirm', $data['confirm'], 'bool', false)) {
            return;
        }
        if (!$this->var()->find('view', $data['view'], 'str', '')) {
            return;
        }

        // Check if we still have no id of the item to modify.
        if (empty($id)) {
            $msg = $this->ml(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                'item id',
                'admin',
                'modify',
                'comments'
            );
            throw new Exception($msg);
        }

        $data['id'] = $id;

        // Load the DD master object class. This line will likely disappear in future versions
        sys::import('modules.dynamicdata.class.objects.factory');

        // Get the object name
        $commentsobject = DataObjectFactory::getObject(['name' => 'comments']);
        $check = $commentsobject->getItem(['itemid' => $id]);
        if (empty($check)) {
            $msg = 'There is no comment with an itemid of ' . $id;
            return $this->tpl()->module('base', 'message', 'notfound', ['msg' => $msg]);
        }

        if (!$this->sec()->checkAccess('EditComments', 0)) {
            return;
        }

        $data['pathval'] = '';

        // Get the object we'll be working with
        $object = DataObjectFactory::getObject(['name' => 'comments_comments']);
        $data['object'] = $object; // save for later

        $data['label'] = $object->label;

        if (!$this->var()->find('confirm', $data['confirm'], 'bool', false)) {
            return;
        }

        if ($data['confirm']) {
            // Check for a valid confirmation key
            if (!$this->sec()->confirmAuthKey()) {
                return $this->ctl()->badRequest('bad_author', $this->getContext());
            }

            // Get the data from the form
            $isvalid = $data['object']->checkInput();

            if (!$isvalid) {
                return $this->mod()->template('modify', $data);
            } elseif (isset($data['preview'])) {
                // Show a preview, same thing as the above essentially
                return $this->mod()->template('modify', $data);
            } else {
                // Good data: update the item

                $data['object']->updateItem(['itemid' => $id]);

                $values = $data['object']->getFieldValues();

                if (!empty($data['view'])) {
                    $this->ctl()->redirect($values['parent_url']);
                } else {
                    $this->ctl()->redirect($this->mod()->getURL('admin', 'modify', ['id' => $id]));
                }
                return true;
            }
        } else {
            $data['object']->getItem(['itemid' => $id]);
        }

        return $data;
    }
}
