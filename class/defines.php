<?php

/**
 * @package modules\comments
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
 * @author Carl P. Corliss <rabbitt@xaraya.com>
**/

namespace Xaraya\Modules\Comments;

/**
 * Defines constants for the comments module (from xarincludes/defines.php)
 */
class Defines
{
    // the following two defines specify the sorting direction which
    // can be either ascending or descending
    public const SORT_ASC = 1;
    public const SORT_DESC = 2;

    // the following four defines specify the sort order which can be any of
    // the following: author, date, topic, lineage
    // TODO: Add Rank sorting
    public const SORTBY_AUTHOR = 1;
    public const SORTBY_DATE = 2;
    public const SORTBY_THREAD = 3;
    public const SORTBY_TOPIC = 4;

    // the following define is for $id when
    // you want to retrieve all comments as opposed
    // to entering in a real comment id and getting
    // just that specific comment
    public const RETRIEVE_ALL = 1;
    public const VIEW_FLAT = 'flat';
    public const VIEW_NESTED = 'nested';
    public const VIEW_THREADED = 'threaded';

    // the following defines are for the $depth variable
    // the -1 (FULL_TREE) tells it to get the full
    // tree/branch and the the 0 (TREE_LEAF) tells the function
    // to acquire just that specific leaf on the tree.
    //
    public const FULL_TREE = -1;
    public const TREE_LEAF = 1;

    // Maximum allowable branch depth
    public const MAX_DEPTH = 10;

    // Status of comment nodes
    public const STATUS_DELETED = 0;
    public const STATUS_OFF = 1;
    public const STATUS_ON = 3;
    public const STATUS_ROOT_NODE = 4;
}
