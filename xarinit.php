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
 * Comments API
 * @package Xaraya
 * @subpackage Comments_API
 */

sys::import('modules.comments.xarincludes.defines');

/**
 * Comments Initialization Function
 *
 * @author Carl P. Corliss (aka Rabbitt)
 *
 */
function comments_init()
{
    //Load Table Maintenance API
    sys::import('xaraya.tableddl');

    $dbconn = xarDB::getConn();
    $xartable = & xarDB::getTables();
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

    $index = ['name'      => 'i_' . xarDB::getPrefix() . '_comments_left',
                   'fields'    => ['left_id'],
                   'unique'    => false, ];

    $query = xarTableDDL::createIndex($xartable['comments'], $index);

    $result = $dbconn->Execute($query);
    if (!$result) {
        return;
    }

    $index = ['name'      => 'i_' . xarDB::getPrefix() . '_comments_right',
                   'fields'    => ['right_id'],
                   'unique'    => false, ];

    $query = xarTableDDL::createIndex($xartable['comments'], $index);

    $result = $dbconn->Execute($query);
    if (!$result) {
        return;
    }

    $index = ['name'      => 'i_' . xarDB::getPrefix() . '_comments_parent_id',
                   'fields'    => ['parent_id'],
                   'unique'    => false, ];

    $query = xarTableDDL::createIndex($xartable['comments'], $index);

    $result = $dbconn->Execute($query);
    if (!$result) {
        return;
    }

    $index = ['name'      => 'i_' . xarDB::getPrefix() . '_comments_moduleid',
                   'fields'    => ['module_id'],
                   'unique'    => false, ];

    $query = xarTableDDL::createIndex($xartable['comments'], $index);

    $result = $dbconn->Execute($query);
    if (!$result) {
        return;
    }

    $index = ['name'      => 'i_' . xarDB::getPrefix() . '_comments_itemtype',
                   'fields'    => ['itemtype'],
                   'unique'    => false, ];

    $query = xarTableDDL::createIndex($xartable['comments'], $index);

    $result = $dbconn->Execute($query);
    if (!$result) {
        return;
    }

    $index = ['name'      => 'i_' . xarDB::getPrefix() . '_comments_itemid',
                   'fields'    => ['itemid'],
                   'unique'    => false, ];

    $query = xarTableDDL::createIndex($xartable['comments'], $index);

    $result = $dbconn->Execute($query);
    if (!$result) {
        return;
    }

    $index = ['name'      => 'i_' . xarDB::getPrefix() . '_comments_status',
                   'fields'    => ['status'],
                   'unique'    => false, ];

    $query = xarTableDDL::createIndex($xartable['comments'], $index);

    $result = $dbconn->Execute($query);
    if (!$result) {
        return;
    }

    $index = ['name'      => 'i_' . xarDB::getPrefix() . '_comments_author',
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

    if (!xarMod::apiFunc('modules', 'admin', 'standardinstall', ['module' => $module, 'objects' => $objects])) {
        return;
    }

    # --------------------------------------------------------
    #
    # Set up modvars
    #
    xarModVars::set('comments', 'render', _COM_VIEW_THREADED);
    xarModVars::set('comments', 'sortby', _COM_SORTBY_THREAD);
    xarModVars::set('comments', 'order', _COM_SORT_ASC);
    xarModVars::set('comments', 'depth', _COM_MAX_DEPTH);
    xarModVars::set('comments', 'AllowPostAsAnon', 1);
    xarModVars::set('comments', 'AuthorizeComments', 0);
    xarModVars::set('comments', 'AllowCollapsableThreads', 1);
    xarModVars::set('comments', 'CollapsedBranches', serialize([]));
    xarModVars::set('comments', 'editstamp', 1);
    xarModVars::set('comments', 'usersetrendering', false);
    xarModVars::set('comments', 'allowhookoverride', false);
    xarModVars::set('comments', 'edittimelimit', 0);
    xarModVars::set('comments', 'numstats', 100);
    xarModVars::set('comments', 'rssnumitems', 25);
    xarModVars::set('comments', 'wrap', false);
    xarModVars::set('comments', 'showtitle', false);
    xarModVars::set('comments', 'useblacklist', false);
    xarModVars::set('comments', 'enable_filters', 1);
    xarModVars::set('comments', 'filters_min_item_count', 3);

    # --------------------------------------------------------
    #
    # Set up configuration modvars (general)
    #
    $module_settings = xarMod::apiFunc('base', 'admin', 'getmodulesettings', ['module' => 'comments']);
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
                         WHERE status != '" . _COM_STATUS_ROOT_NODE . "'";
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
function comments_upgrade($oldversion)
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
function comments_delete()
{
    return xarMod::apiFunc('modules', 'admin', 'standarddeinstall', ['module' => 'comments']);
}
