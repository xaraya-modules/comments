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

use xarMLS;
use xarMod;
use xarModVars;
use xarTpl;
use BadParameterException;
use Exception;

/**
 * Render comments in pre-defined ways with static methods (from xarrenderer.php)
 */
class Renderer
{
    // These defines are threaded view specific and should be here
    // Used for creation of the visual (threaded) tree
    public const NO_CONNECTOR = 0;
    public const O_CONNECTOR = 1;
    public const P_CONNECTOR = 2;
    public const DASH_CONNECTOR = 3;
    public const T_CONNECTOR = 4;
    public const L_CONNECTOR = 5;
    public const I_CONNECTOR = 6;
    public const BLANK_CONNECTOR = 7;
    public const CUTOFF_CONNECTOR = 8;

    /**
     * Takes a an array of related (parent -> child) values and assigns a depth to
     * each one -- requires that each node in the array has the 'children' field
     * telling how many children it [the current node] has
     * List passed as argument MUST be an ordered list - in the order of
     * Parent1 -> child2-> child3 -> child4 -> subchild5 -> sub-subchild6-> subchild7-> child8-> child9-> subchild10 -> Parent11 ->....
     * for example, the below list is an -ordered list- in thread order (ie., parent to child relation ships):
     * <pre>
     *
     *   ID | VISUAL       |   DEPTH
     *   ===+==============+=========
     *    1 | o            |   0
     *      | |            |
     *    2 | +--          |   1
     *      | |            |
     *    3 | +--          |   1
     *      | |            |
     *    4 | +--o         |   1
     *      | |  |         |
     *    5 | +  +--o      |   2
     *      | |  |  |      |
     *    6 | +  +  +--    |   3
     *      | |  |         |
     *    7 | +  +--       |   2
     *      | |            |
     *    8 | +--          |   1
     *      | |            |
     *    9 | +--o         |   1
     *      |    |         |
     *   10 |    +--       |   2
     *      |              |
     *   11 | o            |   0
     *      | |            |
     *   12 | +--o         |   1
     *      |    |         |
     *   13 |    +--       |   2
     *
     * </pre>
     *
     * @author Carl P. Corliss (aka rabbitt)
     * @access public
     * @param array &$comments_list  A reference (pointer) to an array or related items in parent -> child order (see above)
     * @return bool true on success, false otherwise
     *
     */
    public static function array_markdepths_bychildren(&$comments_list)
    {
        // check to make sure we got passed an array,
        // return false if we got no array or it has no items in it
        if (!is_array($comments_list) || !count($comments_list)) {
            return false;
        }

        // figure out how man total nodes are in this array,
        $total_nodes = count($comments_list);

        // check to see if this array has the depth field in it already,
        // if not, it's the first time this array has been parsed through
        // this function so initialize each node to have a depth of zero:
        if (!isset($comments_list[0]['depth'])) {
            for ($node = 0; $node < $total_nodes; $node++) {
                $comments_list[$node]['depth'] = 0;
            }
        }

        for ($node = 0; $node < $total_nodes; $node++) {
            // if the current node has zero (or less) children,
            // skip to the next one
            if ($comments_list[$node]['children'] <= 0) {
                continue;
            } else {
                // otherwise, the node has children so figure out it's last child's index number
                $last_child = $node + $comments_list[$node]['children'];
            }

            // now we increment starting at the node's first child up
            // to it's last one adding one to each of it's kids
            for ($index = $node + 1; $index <= $last_child && $index < $total_nodes; $index++) {
                $comments_list[$index]['depth'] += 1;
            }
        }

        return true;
    }

    /**
     * Takes a an array of related (parent -> child) values and assigns a depth to each one
    *
    * Requires that each node in the array has the parent id field
    * List passed as argument MUST be an ordered list - in the order of
    * Parent1 -> child2-> child3 -> child4 -> subchild5 -> sub-subchild6-> subchild7-> child8-> child9-> subchild10 -> Parent11 ->....
    * This function is exactly like comments_display_array_markdepths but tailored for
    * use with parent id's instead.
    *
    * @author Carl P. Corliss (aka rabbitt)
    * @access public
    * @param array   &$comments_list    an array of related (array) items - each item -must- contain a parent id field
    * @return bool True on success, False otherwise
    */
    public static function array_markdepths_bypid(&$comments_list)
    {
        if (empty($comments_list) || !count($comments_list)) {
            $msg = xarMLS::translate('Empty comments list');
            throw new BadParameterException($msg);
        }

        // start the initial depth off at zero
        $depth = 0;

        $parents = [];
        $new_list = [];
        $prep_list = [];

        // Initialize parents array and make the first key in it equal
        // to the first node in the array's parentid
        $parents['PID_' . $comments_list[0]['parent_id']] = $depth;

        // setup the keys for each comment so that we can
        // easily reference them further down
        foreach ($comments_list as $node) {
            $new_list[$node['id']] = $node;
        }
        $comments_list = $new_list;

        // re-initialize the new_list array
        $new_list = [];

        // foreach node in the array, check to see if we
        // have it's parent id marked in memory and, if so
        // set the current nodes depth equal to that of
        // the marked parent id. If not, then we need to
        // add the depth for the current parent id to the
        // parents list for future use :)
        foreach ($comments_list as $key => $node) {
            // if the current node's parent isn't yet
            // defined, then add it to the list of parents
            // and give it a depth equal to it's parent's depth + 1
            if (!array_key_exists("PID_" . $node['parent_id'], $parents)) {
                if (!array_key_exists($node['parent_id'], $comments_list)) {
                    $comments_list[$node['parent_id']]['parent_id'] = 0;
                    $comments_list[$node['parent_id']]['id'] = 0;
                    $comments_list[$node['parent_id']]['remove'] = 'remove';
                    $parents["PID_" . $node['parent_id']] = -1;
                }
                $ppidkey = "PID_" . $comments_list[$node['parent_id']]['parent_id'];

                // CHECKME: when we start with a category 2+ levels deep, $parents['PID_0'] is undefined here
                if (!isset($parents[$ppidkey])) {
                    $parents[$ppidkey] = -1;
                }
                $parents["PID_" . $node['parent_id']] = $parents[$ppidkey] + 1;
            }

            // if the current nodes parent already has
            // has a defined depth and that depth is
            // zero, then reset the $depth counter to zero
            if (0 == $parents['PID_' . $node['parent_id']]) {
                $depth = 0;
            }

            $prep_list[$key] = $node;
            $prep_list[$key]['depth'] = $parents["PID_" . $node['parent_id']];
        }

        // now we go through and find all the nodes that were marked
        // as parent nodes and add the 'haschildren' field to them
        // setting it to true -- otherwise, if the node wasn't a
        // parent ID we set it's 'haschildren' equal to false
        foreach ($prep_list as $node) {
            if (isset($parents['PID_' . $node['id']])) {
                $node['children'] = 1;
                unset($parents['PID_' . $node['id']]);
            } else {
                $node['children'] = 0;
            }
            $new_list[] = $node;
        }

        $comments_list = [];

        // remove any items that aren't really a part of the array
        // and are just excess baggage from previous code
        foreach ($new_list as $node) {
            if (!array_key_exists('remove', $node)) {
                $comments_list[] = $node;
            }
        }

        return true;
    }


    /**
     * Remove any comments from the list with a depth greater than
    * the cutoff point. If the depth of any particular node is equal
    * to (cutoff + 1), then just the id and the depth for that particular
    * node are included in the array. Reason: it allows us to show that
    * there are more comments in that direction. This is used by
    * comments_userapi_get() to limit the comments pulled by depth.
    *
    * @access private
    * @author Carl P. Corliss (aka rabbitt)
    * @param array<mixed> $args
    * @var array $array_list    list of comments to check
    * @var integer $cutoff        depth cutoff point
    * @return mixed void if no array is passed or the array has no nodes return void
    */
    public static function array_prune_excessdepth(array $args = [], $context = null)
    {
        extract($args);
        if (!is_array($array_list) || !count($array_list)) {
            return;
        }

        // TODO: find better way to get min. left & max. right for this list
        foreach ($array_list as $node) {
            if (!isset($left)) {
                $left = $node['left_id'];
            }
            if (!isset($right)) {
                $right = $node['right_id'];
            }
            if ($node['left_id'] < $left) {
                $left = $node['left_id'];
            }
            if ($node['right_id'] > $right) {
                $right = $node['right_id'];
            }
        }

        $countlist = xarMod::apiFunc(
            'comments',
            'user',
            'get_childcountlist',
            [
                'left' => $left,
                'right' => $right,
                'moduleid' => $moduleid,
                'itemtype' => $itemtype,
                'itemid' => $itemid,
            ]
        );

        $new_list = [];
        foreach ($array_list as $node) {
            if (isset($countlist[$node['id']])) {
                $childcount = $countlist[$node['id']];
            } else {
                $childcount = 0;
            }

            if ($cutoff == $node['depth']) {
                if ($childcount) {
                    // TODO: change childcount -> childcount
                    // TODO: change children --> children
                    $node['branchout']      = true;
                    $node['childcount']         = $childcount;
                    $node['children']           = (int) '-1';

                    // TODO: change thread_text / nested_text --> children_short_text / children_long_text
                    if ($childcount > 1) {
                        $node['thread_text'] = xarMLS::translate('(#(1) children.)', $childcount);
                        $node['nested_text'] = xarMLS::translate('#(1) children beneath this comment.', $childcount);
                    } else {
                        $node['thread_text'] = xarMLS::translate('(#(1) child.)', $childcount);
                        $node['nested_text'] = xarMLS::translate('#(1) child beneath this comment.', $childcount);
                    }

                    $new_list[] = $node;
                } else {
                    // if the comment doesn't have any children, then
                    // display it normally...
                    $node['branchout'] = 0;
                    $new_list[] = $node;
                }
            } elseif ($node['depth'] > $cutoff) {
                continue;
            } else {
                $node['branchout'] = 0;
                $node['childcount']    = $childcount;
                $new_list[] = $node;
            }
        }
        $array_list = $new_list;
        unset($new_list);

        return $array_list;
    }

    /**
     * Used internally by self::array_maptree() to keep track
    * of depths while mapping out the visual tree structure
    *
    * @access private
    * @author Carl P. Corliss (aka rabbitt)
    * @param string     $action    get or set
    * @param integer    $depth     the depth to set or get
    * @param bool       $value     true if the depth is set or false if unset
    * @return bool true if the specified depth is set, false otherwise
    */
    public static function array_depthbuoy($action, $depth, $value = true)
    {
        static $matrix = [];

        if (empty($matrix)) {
            $matrix = array_pad([0 => 0], Defines::MAX_DEPTH, self::NO_CONNECTOR);
        }

        if (strtolower($action) == 'set') {
            $matrix[($depth)] = (bool) $value;
        }

        if ($depth < 0) {
            return 0;
        } else {
            return $matrix[($depth)];
        }
    }

    /**
     * Maps out the visual structure of a tree based on each
    * node's 'depth' and 'children' fields
    *
    * @author Carl P. Corliss (aka rabbitt)
    * @access  public
    * @param   array   $CommentList    List of related comments
    * @param   string  $modName        the name of the module to use when pulling thread images (defaults to comments module)
    * @return  array   an array of comments with an extra field ('map') for each comment
    *                  that's contains the visual representation for that particular node
    */
    public static function array_maptree(&$CommentList, $modName = null)
    {
        // if $CommentList isn't an array or it is empty,
        // return an empty array
        if (!is_array($CommentList) || count($CommentList) == 0) {
            return [];
        }

        // if comments in the list don't have depth then we can't generate
        // the visual image -- so, in that case, see if the comments
        // have a children field. If they do, setup the depths for each
        // comment based on that -- if not, check for a parent_id field and
        // then set up the depth fields for each if that is present,
        // otherwise -- raise an exception.  Also, sort them after
        // assigning depths.

        $current_depth  = 0;         // depth of the current comment in the array
        $next_depth     = 0;         // depth of the next comment in the array (closer to beginning of array)
        $prev_depth     = 0;         // depth of the previous comment in the array (closer to end of array)
        $matrix         = [];   // initialize the matrix to a null array

        $listsize = (count($CommentList) - 1);
        $total = count($CommentList);

        // create the matrix starting from the end and working our way towards
        // the beginning.
        for ($counter = $listsize; $counter >= 0; $counter = $counter - 1) {
            // unmapped matrix for current comment
            $matrix = array_pad([0 => 0], Defines::MAX_DEPTH, self::NO_CONNECTOR);

            // make sure to $depth = $depth modulus Defines::MAX_DEPTH  - because we are only ever showing
            // ten levels of depth -- anything more than that and the display doesn't look good
            $current_depth  = @$CommentList[$counter]['depth'] % Defines::MAX_DEPTH;
            $next_depth     = (($counter - 1) < 0 ? -1 : @$CommentList[$counter - 1]['depth'] % Defines::MAX_DEPTH);
            $prev_depth     = (($counter + 1) > $listsize ? -1 : @$CommentList[$counter + 1]['depth'] % Defines::MAX_DEPTH);

            // first start by placing the depth point in the matrix
            // if the current comment has children place a P connetor
            if ($CommentList[$counter]['children'] === true || $CommentList[$counter]['children'] > 0) {
                $matrix[$current_depth] = self::P_CONNECTOR;
            } elseif ($CommentList[$counter]['children'] < 0) {
                $matrix[$current_depth] = self::CUTOFF_CONNECTOR;
            } else {
                // if the current comment doesn't have children
                // and it is at depth ZERO it is an O connector
                // otherwise use a dash connector
                if (!$current_depth) {
                    $matrix[$current_depth] = self::O_CONNECTOR;
                } else {
                    $matrix[$current_depth] = self::DASH_CONNECTOR;
                }
            }

            // if the current depth is zero then all that it requires is an O or P connector
            // soooo if the current depth is -not- zero then we have other connectors so
            // below we figure out what the other connectors are...
            if (0 != $current_depth) {
                if (($current_depth != $prev_depth)) {
                    $matrix[$current_depth - 1] = self::L_CONNECTOR;
                }

                // in order to have a T connector the current depth -must-
                // be less then or equal to the previous depth
                if ($current_depth <= $prev_depth) {
                    // if there is a DepthBuoy set for (current depth -1)
                    // then
                    if (self::array_depthbuoy('get', ($current_depth - 1)) === true) {
                        // the DepthBuoy for this depth can now be turned off.
                        self::array_depthbuoy('set', ($current_depth - 1), false);
                        $matrix[($current_depth - 1)] = self::T_CONNECTOR;
                    }

                    if ($current_depth == $prev_depth) {
                        $matrix[($current_depth - 1)] = self::T_CONNECTOR;
                    }
                }

                // Once we've got the T and L connectors done, we need to go through
                // the matrix working our way from the indice equal to the current comment
                // depth towards the begginning of the array - checking for I connectors
                // and Blank connectors.
                for ($node = $current_depth; $node >= 0; $node -= 1) {
                    // be sure not to overwrite another node in the matrix
                    if (!$matrix[$node]) {
                        // if a depth buoy was set for this depth, add I connector
                        if (self::array_depthbuoy('get', $node) == true) {
                            $matrix[($node)] = self::I_CONNECTOR;
                        } else {
                            // otherwise add a blank.gif
                            $matrix[($node)] = self::BLANK_CONNECTOR;
                        }
                    }
                }
            }

            // Set depth buoy if the next depth is greater then the current,
            // this way we can remember where to set an I connector :)
            if ($next_depth > $current_depth && $current_depth != 0) {
                self::array_depthbuoy('set', $current_depth - 1, true);
            }

            // ok -- once that's all done, take this segment of the whole matrix map (ie.,
            // this comment's matrix) create the array of images that will represent this
            // comments place on the "threaded map."

            // if modName == NULL or empty then we default to using the comments api's
            //  thread images otherwise, we use images from the calling module
            if (empty($modName) || $nodName == null) {
                $CommentList[$counter]['map'] = self::array_image_substitution($matrix, 'comments');
            } else {
                $CommentList[$counter]['map'] = self::array_image_substitution($matrix);
            }
        }

        return $CommentList;
    }

    /**
     * Used internally by self::array_maptree(). Takes the nodes in a matrix created for
    * a particular comment and translates them into the visual (html'ified) segments of the full map.
    *
    * @author  Carl P. Corliss (aka rabbitt)
    * @access  private
    * @param   array   $matrix  The current node's tree matrix
    * @param   string  $modName (optional) the module name to use when grabbing the image location
    * @return  array   a list of the images needed for displaying this particular node in the tree
    */
    public static function array_image_substitution($matrix, $modName = null)
    {
        $map = [];

        foreach ($matrix as $value) {
            switch ($value) {
                case self::O_CONNECTOR:
                    $map[] = xarTpl::getImage('n_nosub.gif', $modName);
                    break;
                case self::P_CONNECTOR:
                    $map[] = xarTpl::getImage('n_sub.gif', $modName);
                    break;
                case self::T_CONNECTOR:
                    $map[] = xarTpl::getImage('n_sub_branch_t.gif', $modName);
                    break;
                case self::L_CONNECTOR:
                    $map[] = xarTpl::getImage('n_sub_branch_l.gif', $modName);
                    break;
                case self::I_CONNECTOR:
                    $map[] = xarTpl::getImage('n_sub_line.gif', $modName);
                    break;
                case self::BLANK_CONNECTOR:
                    $map[] = xarTpl::getImage('n_spacer.gif', $modName);
                    break;
                case self::DASH_CONNECTOR:
                    $map[] = xarTpl::getImage('n_sub_end.gif', $modName);
                    break;
                case self::CUTOFF_CONNECTOR:
                    $map[] = xarTpl::getImage('n_sub_cutoff.gif', $modName);
                    break;
                default:
                case self::NO_CONNECTOR:
                    break;
            }
        }
        return $map;
    }

    /**
     * Used internally by self::array_sort(). facilitates
    * sorting of comments whereby the only ones that are sorted in reverse
    * are the top level comments -- all other comments are sorted in ascending order
    * maintaining parent->child relationships
    *
    * @access private
    * @author Carl P. Corliss (aka rabbitt)
    * @param  string    $a     Lineage to compare
    * @param  string    $b     Lineage to compare
    * @return integer  -1 if a < b, 0 if a == b, 1 if a > b
    *
    */
    public static function array_fieldrelation_compare($a, $b)
    {
        // get the sort value
        $sort = self::array_sortvalue();

        // first we start off by putting the array key into
        // array format with each id that makes up
        // the lineage having it's own array index.
        // As well, we find out how many id's there
        // are for each Lineage.
        $Family_A = explode(':', $a);
        $Family_A_count = count($Family_A);

        $Family_B = explode(':', $b);
        $Family_B_count = count($Family_B);

        // We need the lineage with the least amount of id's in
        // it for use in our for loop.
        if ($Family_A_count == $Family_B_count) {
            // if they are both equal we could just as easily
            // set this to Family_B instead.. doesn't really
            // matter
            $members_count = $Family_A_count;
        } else {
            $members_count = ($Family_A_count < $Family_B_count ? $Family_A_count : $Family_B_count);
        }
        // here we do the sorting of the toplevel comments in
        // the list by comparing the first ID's in the lineage
        // which are always the top level id's.
        if (is_numeric($Family_A[0]) && is_numeric($Family_B[0])) {
            if ((int) $Family_A[0] != (int) $Family_B[0]) {
                if ($sort == Defines::SORT_ASC) {
                    return ((int) $Family_A[0] < (int) $Family_B[0]) ? -1 : 1;
                } elseif ($sort == Defines::SORT_DESC) {
                    return ((int) $Family_A[0] < (int) $Family_B[0]) ? 1 : -1;
                } else {
                    // in the event that sort is set to some unexpected value
                    // assume sort = ASC
                    return ((int) $Family_A[0] < (int) $Family_B[0]) ? -1 : 1;
                }
            }
        } else {
            if (strcasecmp($Family_A[0], $Family_B[0]) != 0) {
                if ($sort == Defines::SORT_ASC) {
                    return strcasecmp($Family_A[0], $Family_B[0]);
                } elseif ($sort == Defines::SORT_DESC) {
                    return (int) -(strcasecmp($Family_A[0], $Family_B[0]));
                } else {
                    // in the event that sort is set to some unexpected value
                    // assume sort = ASC
                    return strcasecmp($Family_A[0], $Family_B[0]);
                }
            }
        }
        // now we do an id to id comparison but only up to the number of
        // elements (comment ids) of the smallest lineage.
        for ($i = 1; $i < $members_count; $i++) {
            if ((int) $Family_A[$i] != (int) $Family_B[$i]) {
                return ((int) $Family_A[$i] < (int) $Family_B[$i]) ? -1 : 1;
            }
        }

        // Since we are here it means that both lineages matched up to the
        // length of the smallest lineage soo-, the one that has the most
        // elements (comment ids) is obviously of higher value. If however they
        // have the same amount of elements, then the lineages are the same --
        // [Note]: this should NEVER happen.
        if ($Family_A_count != $Family_B_count) {
            return ($Family_A_count < $Family_B_count) ? -1 : 1;
        } else {
            return 0;
        }
    }

    /**
     * Used to set/retrieve the current value of sort. -- used internally
    * and should not be utilized outside of this function group.
    *
    * @access  private
    * @author  Carl P. Corliss (aka rabbitt)
    * @param   string  $value  'ASC' for Ascending, 'DESC' for descending sort order
    * @return  string  The current sort value
    *
    */
    public static function array_sortvalue($value = null)
    {
        static $sort;

        if ($value != null) {
            switch (strtolower($value)) {
                case Defines::SORT_DESC:
                    $sort = Defines::SORT_DESC;
                    break;
                case Defines::SORT_ASC:
                default:
                    $sort = Defines::SORT_ASC;
            }
        }
        return $sort;
    }

    /**
     * Sorts the specified array by the specified 'sortby' value in the direction specified by 'direction'
    *
    * @author Carl P. Corliss (aka rabbitt)
    * @access public
    * @param    string  $sortby         represents the field to sort by
    * @param    string  $direction      represents the direction to sort (ascending / descending )
    * @param    array<mixed>   $comment_list   List of comments to sort
    * @return   array<mixed>   nothing
    */
    public static function array_sort(&$comment_list, $sortby, $direction)
    {
        if (!isset($comment_list) || !is_array($comment_list)) {
            $msg = xarMLS::translate(
                'Missing or invalid argument [#(1)] for #(2) function #(3) in module #(4)',
                'comment_list',
                'renderer',
                'array_sort',
                $modName ?? ''
            );
            throw new Exception($msg);
        }

        $index      = [];
        $new_list   = [];

        self::array_sortvalue($direction);

        if ($sortby == Defines::SORTBY_THREAD) {
            foreach ($comment_list as $node) {
                if ($node['depth'] == 0) {
                    $key = $node['id'];
                    $index[$node['id']] = $key;
                } else {
                    $key = $index[$node['parent_id']] . ":" . $node['id'];
                    $index[$node['id']] = $key;
                }
                $new_list[$key] = $node;
            }
        } else {
            // Initial presort for non threaded sort - We do a presort to
            // get all the comments in order by the key that we're sorting
            // by -- otherwise, when we assign parents and children
            // (further below) there will  be a chance that some will be
            // out of order and mess up the rendering
            foreach ($comment_list as $node) {
                switch ($sortby) {
                    case Defines::SORTBY_TOPIC:
                        $key = str_replace("\:", " ", $node['title']);
                        break;
                    case Defines::SORTBY_DATE:
                        $key = 'a' . $node['datetime'];
                        break;
                    default:
                    case Defines::SORTBY_AUTHOR:
                        $key = $node['author'];
                        break;
                        // default to sorting by author
                }

                $new_list[$key . ":" . $node['id']] = $node;
            }
            $comment_list = $new_list;
            $new_list = [];

            uksort($comment_list, [self::class, 'array_fieldrelation_compare']);
            // End of PreSORT

            foreach ($comment_list as $node) {
                switch ($sortby) {
                    case Defines::SORTBY_TOPIC:
                        $key = str_replace("\:", " ", $node['title']);
                        break;
                    case Defines::SORTBY_DATE:
                        $key = 'a' . $node['datetime'];
                        break;
                    default:
                    case Defines::SORTBY_AUTHOR:
                        $key = $node['author'];
                        break;
                        // default to sorting by author
                }

                if (!isset($index[$key])) {
                    $index[$key]['depth'] = 0;
                    $index[$key]['children'] = 0;
                    $new_list[$key . ":0"] = $node;
                    $new_list[$key . ":0"]['depth'] = $index[$key]['depth'];
                    $new_list[$key . ":0"]['children'] = $index[$key]['children'];
                } else {
                    $key2 = $key . ":" . $node['id'];
                    $new_list[$key2] = $node;
                    $new_list[$key2]['depth'] = 1;
                    $new_list[$key2]['children'] = 0;
                    $new_list[$key . ":0"]['children'] += 1;
                }
            }
        }
        $comment_list = $new_list;

        uksort($comment_list, [self::class, 'array_fieldrelation_compare']);

        // reset the indexes on the comments_list
        $comments = [];
        foreach ($comment_list as $comment) {
            $comments[] = $comment;
        }

        $comment_list = $comments;
        unset($comments);

        return $comment_list;
    }

    /**
     * Wraps words at the specified length
    *
    * @author Carl P. Corliss (aka rabbitt)
    * @access public
    * @param    string  &$str  the string to perform word wrapping on
    * @param    integer $chars the amount of characters to word wrap at
    * @return   void the word-wrapped string
    * @todo do we need this function? \
    * @todo is this the correct place for wrap modvar checking?
    */
    public static function wrap_words(&$str, $chars)
    {
        if (xarModVars::get('comments', 'wrap')) {
            // Added for bug 4210 wrapping on multibyte words
            $before_lt = "[\\x21-\\x3B]"; //"space" is x20 and "<" is x3C
            $equal = "[\\x3D]";           //"=" is x3D
            $after_gt = "[\\x3F-\\x7F]";  //">" is x3E
            $single = $before_lt . "|" . $equal . "|" . $after_gt;
            $pattern = "/(" . $single . "){" . $chars . "," . $chars . "}/";
            $str = preg_replace($pattern, '\0 ', $str);
        }
        //$str = preg_replace('/([^\s\<\>]{'.$chars.','.$chars.'})/', '\1 ', $str);
    }
}
