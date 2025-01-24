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
        if (!$this->sec()->checkAccess('ManageComments')) {
            return;
        }

        if (!$this->var()->find('confirm', $data['confirm'], 'bool', false)) {
            return;
        }
        if (!$this->var()->find('deletebranch', $deletebranch, 'bool', false)) {
            return;
        }
        if (!$this->var()->find('id', $data['id'], 'int')) {
            return;
        }
        if (!$this->var()->find('parent_url', $data['parent_url'], 'str', '')) {
            return;
        }

        if (empty($data['id'])) {
            return $this->ctl()->notFound(null, $this->getContext());
        }

        sys::import('modules.dynamicdata.class.objects.factory');
        $data['object'] = $this->data()->getObject(['name' => 'comments_comments']);
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
                $this->ctl()->redirect($data['parent_url']);
                return true;
            } else {
                $data['object']->deleteItem(['itemid' => $data['id']]);
                $this->ctl()->redirect($data['parent_url']);
                return true;
            }
        }

        $data['package']['delete_url'] = $this->mod()->getURL('user', 'delete');

        $comments = xarMod::apiFunc('comments', 'user', 'get_one', ['id' => $data['id']]);
        if ($comments[0]['position_atomic']['right'] == $comments[0]['position_atomic']['left'] + 1) {
            $data['package']['haschildren'] = false;
        } else {
            $data['package']['haschildren'] = true;
        }

        return $data;
    }
}
