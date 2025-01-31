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
 * Pass table names back to the framework
 * @return array
 */
function comments_xartables(?string $prefix = null)
{
    // Initialise table array
    $xartable = [];
    $prefix ??= xarDB::getPrefix();

    // Name for template database entities
    $comments_table     = $prefix . '_comments_comments';
    $blacklist_table    = $prefix . '_comments_blacklist';

    // Table name
    $xartable['comments']   = $comments_table;
    $xartable['blacklist']  = $blacklist_table;

    // Return table information
    return $xartable;
}
