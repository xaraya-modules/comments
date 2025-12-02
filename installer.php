<?php

/**
 * Handle module installer functions
 *
 * @package modules\comments
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Comments;

use Xaraya\Modules\InstallerClass;
use xarTableDDL;
use xarModHooks;
use xarPrivileges;
use xarMasks;
use sys;

/**
 * Handle module installer functions
 *
 * @todo add extra use ...; statements above as needed
 * @todo replaced comments_*() function calls with $this->*() calls
 * @extends InstallerClass<Module>
 */
class Installer extends InstallerClass
{
    /**
     * Configure this module - override this method
     *
     * @todo use this instead of init() etc. for standard installation
     * @return void
     */
    public function configure()
    {
        $this->objects = [
            // add your DD objects here
            //'comments_object',
        ];
        $this->variables = [
            // add your module variables here
            'hello' => 'world',
        ];
        $this->oldversion = '2.4.1';
    }

    /** xarinit.php functions imported by bermuda_cleanup */

    /**
     * Comments Initialization Function
     * @author Carl P. Corliss (aka Rabbitt)
     */
    public function init()
    {
        //Load Table Maintenance API
        sys::import('xaraya.tableddl');

        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();
        //Psspl:Added the code for anonpost_to field.
        $fields = [
            'id' => ['type' => 'integer', 'unsigned' => true, 'null' => false, 'increment' => true, 'primary_key' => true],
            'date'      => ['type' => 'integer',  'null' => false],
            'author'    => ['type' => 'integer',  'null' => false,  'size' => 'medium','default' => 1],
            'title'     => ['type' => 'varchar',  'null' => false,  'size' => 100],
            'text'      => ['type' => 'text',     'null' => true,   'size' => 'medium'],
            'parent_id' => ['type' => 'integer',  'null' => false, 'default' => '0'],
            'parent_url' => ['type' => 'text',     'null' => false,  'size' => 'medium'],
            'module_id' => ['type' => 'integer',  'null' => true],
            'itemtype'  => ['type' => 'integer',  'null' => false],
            'itemid'    => ['type' => 'varchar',  'null' => false,  'size' => 255],
            'hostname'  => ['type' => 'varchar',  'null' => false,  'size' => 255],
            'left_id'   => ['type' => 'integer',  'null' => false, 'default' => '0'],
            'right_id'  => ['type' => 'integer',  'null' => false, 'default' => '0'],
            'anonpost'  => ['type' => 'integer',  'null' => true,   'size' => 'tiny', 'default' => 0],
            'status'    => ['type' => 'integer',  'null' => false,  'size' => 'tiny'],
        ];

        $query = xarTableDDL::createTable($xartable['comments'], $fields);

        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        $index = ['name'      => 'i_' . $this->db()->getPrefix() . '_comments_left',
            'fields'    => ['left_id'],
            'unique'    => false, ];

        $query = xarTableDDL::createIndex($xartable['comments'], $index);

        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        $index = ['name'      => 'i_' . $this->db()->getPrefix() . '_comments_right',
            'fields'    => ['right_id'],
            'unique'    => false, ];

        $query = xarTableDDL::createIndex($xartable['comments'], $index);

        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        $index = ['name'      => 'i_' . $this->db()->getPrefix() . '_comments_parent_id',
            'fields'    => ['parent_id'],
            'unique'    => false, ];

        $query = xarTableDDL::createIndex($xartable['comments'], $index);

        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        $index = ['name'      => 'i_' . $this->db()->getPrefix() . '_comments_moduleid',
            'fields'    => ['module_id'],
            'unique'    => false, ];

        $query = xarTableDDL::createIndex($xartable['comments'], $index);

        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        $index = ['name'      => 'i_' . $this->db()->getPrefix() . '_comments_itemtype',
            'fields'    => ['itemtype'],
            'unique'    => false, ];

        $query = xarTableDDL::createIndex($xartable['comments'], $index);

        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        $index = ['name'      => 'i_' . $this->db()->getPrefix() . '_comments_itemid',
            'fields'    => ['itemid'],
            'unique'    => false, ];

        $query = xarTableDDL::createIndex($xartable['comments'], $index);

        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        $index = ['name'      => 'i_' . $this->db()->getPrefix() . '_comments_status',
            'fields'    => ['status'],
            'unique'    => false, ];

        $query = xarTableDDL::createIndex($xartable['comments'], $index);

        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        $index = ['name'      => 'i_' . $this->db()->getPrefix() . '_comments_author',
            'fields'    => ['author'],
            'unique'    => false, ];

        $query = xarTableDDL::createIndex($xartable['comments'], $index);

        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        // Create blacklist tables

        $btable = $xartable['blacklist'];
        $bbtable = &$xartable['blacklist_column'];

        $fields = [
            'id'       => ['type' => 'integer',  'null' => false,  'increment' => true, 'primary_key' => true],
            'domain'   => ['type' => 'varchar',  'null' => false,  'size' => 255],
        ];

        $query = xarTableDDL::createTable($xartable['blacklist'], $fields);

        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        $module = 'comments';
        $objects = [
            'comments_comments',
            'comments_module_settings',
            'comments_blacklist',
        ];

        if (!$this->mod()->apiFunc('modules', 'admin', 'standardinstall', ['module' => $module, 'objects' => $objects])) {
            return;
        }

        # --------------------------------------------------------
        #
        # Set up modvars
        #
        $this->mod()->setVar('render', Defines::VIEW_THREADED);
        $this->mod()->setVar('sortby', Defines::SORTBY_THREAD);
        $this->mod()->setVar('order', Defines::SORT_ASC);
        $this->mod()->setVar('depth', Defines::MAX_DEPTH);
        $this->mod()->setVar('AllowPostAsAnon', 1);
        $this->mod()->setVar('AuthorizeComments', 0);
        $this->mod()->setVar('AllowCollapsableThreads', 1);
        $this->mod()->setVar('CollapsedBranches', serialize([]));
        $this->mod()->setVar('editstamp', 1);
        $this->mod()->setVar('usersetrendering', false);
        $this->mod()->setVar('allowhookoverride', false);
        $this->mod()->setVar('edittimelimit', 0);
        $this->mod()->setVar('numstats', 100);
        $this->mod()->setVar('rssnumitems', 25);
        $this->mod()->setVar('wrap', false);
        $this->mod()->setVar('showtitle', false);
        $this->mod()->setVar('useblacklist', false);
        $this->mod()->setVar('enable_filters', 1);
        $this->mod()->setVar('filters_min_item_count', 3);

        # --------------------------------------------------------
        #
        # Set up configuration modvars (general)
        #
        $module_settings = $this->mod()->apiFunc('base', 'admin', 'getmodulesettings', ['module' => 'comments']);
        $module_settings->initialize();

        # --------------------------------------------------------
        #
        # Set up hooks
        #
        // TODO: add delete hook

        // display hook
        if (!xarModHooks::register('item', 'display', 'GUI', 'comments', 'user', 'display')) {
            return false;
        }

        // usermenu hook
        if (!xarModHooks::register('item', 'usermenu', 'GUI', 'comments', 'user', 'usermenu')) {
            return false;
        }

        // search hook
        if (!xarModHooks::register('item', 'search', 'GUI', 'comments', 'user', 'search')) {
            return false;
        }

        // module delete hook
        if (!xarModHooks::register('module', 'remove', 'API', 'comments', 'admin', 'remove_module')) {
            return false;
        }

        # --------------------------------------------------------
        #
        # Define instances for this module
        # Format is
        #  setInstance(Module, Type, ModuleTable, IDField, NameField,
        #             ApplicationVar, LevelTable, ChildIDField, ParentIDField)
        #
        $ctable = $xartable['comments'];
        $query1 = "SELECT DISTINCT $xartable[modules].name
                              FROM $ctable
                         LEFT JOIN $xartable[modules]
                                ON modid = $xartable[modules].regid";

        $query2 = "SELECT DISTINCT objectid
                              FROM $ctable";

        $query3 = "SELECT DISTINCT id
                              FROM $ctable
                             WHERE status != '" . Defines::STATUS_ROOT_NODE . "'";
        $instances = [
            ['header' => 'Module ID:',
                'query' => $query1,
                'limit' => 20,
            ],
            ['header' => 'Module Page ID:',
                'query' => $query2,
                'limit' => 20,
            ],
            ['header' => 'Comment ID:',
                'query' => $query3,
                'limit' => 20,
            ],
        ];
        xarPrivileges::defineInstance('comments', 'All', $instances);

        # --------------------------------------------------------
        #
        # Set up masks
        #
        xarMasks::register('ReadComments', 'All', 'comments', 'All', 'All:All:All', 'ACCESS_READ', 'See and Read Comments');
        xarMasks::register('PostComments', 'All', 'comments', 'All', 'All:All:All', 'ACCESS_COMMENT', 'Post a new Comment');
        xarMasks::register('ReplyComments', 'All', 'comments', 'All', 'All:All:All', 'ACCESS_COMMENT', 'Reply to a Comment');
        xarMasks::register('ModerateComments', 'All', 'comments', 'All', 'All:All:All', 'ACCESS_MODERATE', 'Moderate Comments');
        xarMasks::register('EditComments', 'All', 'comments', 'All', 'All:All:All', 'ACCESS_EDIT', 'Edit Comments');
        xarMasks::register('AddComments', 'All', 'comments', 'All', 'All:All:All', 'ACCESS_ADD', 'Add Comments');
        xarMasks::register('ManageComments', 'All', 'comments', 'All', 'All:All:All', 'ACCESS_DELETE', 'Delete a Comment or Comments');
        xarMasks::register('AdminComments', 'All', 'comments', 'All', 'All:All:All', 'ACCESS_ADMIN', 'Administrate Comments');

        xarPrivileges::register('ViewComments', 'All', 'comments', 'All', 'All', 'ACCESS_OVERVIEW');
        xarPrivileges::register('ReadComments', 'All', 'comments', 'All', 'All', 'ACCESS_READ');
        xarPrivileges::register('CommmentComments', 'All', 'comments', 'All', 'All', 'ACCESS_COMMENT');
        xarPrivileges::register('ModerateComments', 'All', 'comments', 'All', 'All', 'ACCESS_MODERATE');
        xarPrivileges::register('EditComments', 'All', 'comments', 'All', 'All', 'ACCESS_EDIT');
        xarPrivileges::register('AddComments', 'All', 'comments', 'All', 'All', 'ACCESS_ADD');
        xarPrivileges::register('ManageComments', 'All', 'comments', 'All', 'All:All', 'ACCESS_DELETE');
        xarPrivileges::register('AdminComments', 'All', 'comments', 'All', 'All', 'ACCESS_ADMIN');

        // Initialisation successful
        return true;
    }

    /**
     * Upgrade the comments module from an old version
     */
    public function upgrade($oldversion)
    {
        // Upgrade dependent on old version number
        switch ($oldversion) {
            case '2.0':
                // Code to upgrade from version 2.0 goes here
                // fall through to the next upgrade
            case '2.5':
                // Code to upgrade from version 2.5 goes here
                break;
        }
        return true;
    }

    /**
     * uninstall the comments module
     */
    public function delete()
    {
        return $this->mod()->apiFunc('modules', 'admin', 'standarddeinstall', ['module' => 'comments']);
    }
}
