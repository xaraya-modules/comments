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
 */
class ModifyMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * modify an item
     * This function shows a form in which the user can modify the item
     * @param array $args
     * with
     *     id itemid The id of the dynamic data item to modify
     */
    public function __invoke(array $args = [])
    {
        if (!xarVar::fetch('id', 'id', $id, null, xarVar::DONT_SET)) {
            return;
        }
        if (!xarVar::fetch('parent_url', 'str', $parent_url, null, xarVar::DONT_SET)) {
            return;
        }
        if (!xarVar::fetch('confirm', 'bool', $data['confirm'], false, xarVar::NOT_REQUIRED)) {
            return;
        }
        if (!xarVar::fetch('view', 'str', $data['view'], '', xarVar::NOT_REQUIRED)) {
            return;
        }

        // Check if we still have no id of the item to modify.
        if (empty($id)) {
            $msg = xarML(
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
            return xarTpl::module('base', 'message', 'notfound', ['msg' => $msg]);
        }

        if (!xarSecurity::check('EditComments', 0)) {
            return;
        }

        $data['pathval'] = '';

        // Get the object we'll be working with
        $object = DataObjectFactory::getObject(['name' => 'comments_comments']);
        $data['object'] = $object; // save for later

        $data['label'] = $object->label;

        if (!xarVar::fetch('confirm', 'bool', $data['confirm'], false, xarVar::NOT_REQUIRED)) {
            return;
        }

        if ($data['confirm']) {
            // Check for a valid confirmation key
            if (!xarSec::confirmAuthKey()) {
                return xarController::badRequest('bad_author', $this->getContext());
            }

            // Get the data from the form
            $isvalid = $data['object']->checkInput();

            if (!$isvalid) {
                return xarTpl::module('comments', 'admin', 'modify', $data);
            } elseif (isset($data['preview'])) {
                // Show a preview, same thing as the above essentially
                return xarTpl::module('comments', 'admin', 'modify', $data);
            } else {
                // Good data: update the item

                $data['object']->updateItem(['itemid' => $id]);

                $values = $data['object']->getFieldValues();

                if (!empty($data['view'])) {
                    xarController::redirect($values['parent_url'], null, $this->getContext());
                } else {
                    xarController::redirect(xarController::URL('comments', 'admin', 'modify', ['id' => $id]), null, $this->getContext());
                }
                return true;
            }
        } else {
            $data['object']->getItem(['itemid' => $id]);
        }

        return $data;
    }
}
