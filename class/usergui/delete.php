<?php

/**
 * @package modules\comments
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Comments\UserGui;


use Xaraya\Modules\Comments\UserGui;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarVar;
use xarController;
use xarMod;
use DataObjectFactory;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments user delete function
 * @extends MethodClass<UserGui>
 */
class DeleteMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Delete a comment or a group of comments
     * @author Carl P. Corliss (aka rabbitt)
     * @access private
     * @return mixed description of return
     */
    public function __invoke(array $args = [])
    {
        if (!xarSecurity::check('ManageComments')) {
            return;
        }

        if (!xarVar::fetch('confirm', 'bool', $data['confirm'], false, xarVar::NOT_REQUIRED)) {
            return;
        }
        if (!xarVar::fetch('deletebranch', 'bool', $deletebranch, false, xarVar::NOT_REQUIRED)) {
            return;
        }
        if (!xarVar::fetch('id', 'int', $data['id'], null, xarVar::NOT_REQUIRED)) {
            return;
        }
        if (!xarVar::fetch('parent_url', 'str', $data['parent_url'], '', xarVar::NOT_REQUIRED)) {
            return;
        }

        if (empty($data['id'])) {
            return xarController::notFound(null, $this->getContext());
        }

        sys::import('modules.dynamicdata.class.objects.factory');
        $data['object'] = DataObjectFactory::getObject(['name' => 'comments_comments']);
        $data['object']->getItem(['itemid' => $data['id']]);
        $values = $data['object']->getFieldValues();
        foreach ($values as $key => $val) {
            $data[$key] = $val;
        }

        if ($data['confirm']) {
            if ($deletebranch) {
                xarMod::apiFunc(
                    'comments',
                    'admin',
                    'delete_branch',
                    ['node' => $header['id']]
                );
                xarController::redirect($data['parent_url'], null, $this->getContext());
                return true;
            } else {
                $data['object']->deleteItem(['itemid' => $data['id']]);
                xarController::redirect($data['parent_url'], null, $this->getContext());
                return true;
            }
        }

        $data['package']['delete_url'] = xarController::URL('comments', 'user', 'delete');

        $comments = xarMod::apiFunc('comments', 'user', 'get_one', ['id' => $data['id']]);
        if ($comments[0]['position_atomic']['right'] == $comments[0]['position_atomic']['left'] + 1) {
            $data['package']['haschildren'] = false;
        } else {
            $data['package']['haschildren'] = true;
        }

        return $data;
    }
}
