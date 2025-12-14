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
use Exception;

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
     * @see AdminGui::modify()
     */
    public function __invoke(array $args = [])
    {
        $this->var()->check('id', $id, 'id');
        $this->var()->check('parent_url', $parent_url, 'str');
        $this->var()->find('confirm', $data['confirm'], 'bool', false);
        $this->var()->find('view', $data['view'], 'str', '');

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

        // Get the object name
        $commentsobject = $this->data()->getObject(['name' => 'comments']);
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
        $object = $this->data()->getObject(['name' => 'comments_comments']);
        $data['object'] = $object; // save for later

        $data['label'] = $object->label;

        $this->var()->find('confirm', $data['confirm'], 'bool', false);

        if ($data['confirm']) {
            // Check for a valid confirmation key
            if (!$this->sec()->confirmAuthKey()) {
                return $this->ctl()->badRequest('bad_author');
            }

            // Get the data from the form
            $isvalid = $data['object']->checkInput();

            if (!$isvalid) {
                return $data;
            } elseif (isset($data['preview'])) {
                // Show a preview, same thing as the above essentially
                return $data;
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
