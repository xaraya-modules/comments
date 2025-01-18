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
use Xaraya\Modules\Comments\Defines;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarRoles;
use xarUser;
use xarVar;
use xarMod;
use xarModVars;
use Query;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments admin view function
 * @extends MethodClass<AdminGui>
 */
class ViewMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * This is a standard function to modify the configuration parameters of the
     * module
     * @return array|void
     */
    public function __invoke(array $args = [])
    {
        if (!$this->sec()->checkAccess('ManageComments')) {
            return;
        }

        // Only show top level documents, not translations
        sys::import('xaraya.structures.query');
        $q = new Query();
        $q->ne('status', Defines::STATUS_ROOT_NODE);

        // Suppress deleted items if not an admin
        // Remove this once listing property works with dataobject access
        if (!xarRoles::isParent('Administrators', xarUser::getVar('uname'))) {
            $q->ne('status', 0);
        }
        $data['conditions'] = $q;
        return $data;

        if (!$this->var()->find('startnum', $startnum, 'int', 1)) {
            return;
        }

        //how to sort if the URL or config say otherwise...
        $sort = xarMod::apiFunc('comments', 'admin', 'sort', [
            'sortfield_fallback' => 'date',
            'ascdesc_fallback' => 'DESC',
        ]);
        $data['sort'] = $sort;

        $object = DataObjectFactory::getObject(['name' => 'comments_comments']);
        $config = $object->configuration;
        $adminfields = reset($config['adminfields']);
        $numitems = $this->mod()->getVar('items_per_page');

        $filters = [];

        // Total number of comments for use in the pager
        $total = DataObjectFactory::getObjectList([
            'name' => 'comments_comments',
            'numitems' => null,
            'where' => 'status ne ' . Defines::STATUS_ROOT_NODE,
        ]);
        $data['total'] = $total->countItems();

        $filters_min_items = $this->mod()->getVar('filters_min_item_count');

        $data['makefilters'] = [];
        $data['showfilters'] = false;

        if (xarMod::isAvailable('filters') && $this->mod()->getVar('enable_filters') && $data['total'] >= $filters_min_items) {
            $data['showfilters'] = true;
            $filterfields = $config['filterfields'];
            $get_results = xarMod::apiFunc('filters', 'user', 'dd_get_results', [
                'filterfields' => $filterfields,
                'object' => 'comments',
            ]);
            $data = array_merge($data, $get_results);
            if (isset($data['filters'])) {
                $filters = $data['filters'];
            }
        }

        if (isset($filters['where'])) {
            $filters['where'] .=  ' and ';
        } else {
            $filters['where'] = '';
        }

        $filters['where'] .= 'status ne ' . Defines::STATUS_ROOT_NODE;

        $list = DataObjectFactory::getObjectList([
            'name' => 'comments_comments',
            'sort' => $sort,
            'startnum' => $startnum,
            'numitems' => $numitems,
            'fieldlist' => $adminfields,
        ]);

        if (!is_object($list)) {
            return;
        }

        $list->getItems($filters);

        $data['list'] = $list;

        return $data;
    }
}
