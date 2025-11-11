<?php

/**
 * @package modules\comments
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Comments;

use Xaraya\Modules\UserApiClass;

/**
 * Handle the comments user API
 *
 * @method mixed activate(array $args)
 * @method mixed add(array $args)
 * @method mixed addRootnode(array $args)
 * @method mixed createGap(array $args)
 * @method mixed deactivate(array $args)
 * @method mixed decodeShorturl(array $args)
 * @method mixed encodeShorturl(array $args)
 * @method mixed formhooks(array $args)
 * @method mixed getAuthorCount(array $args)
 * @method mixed getBlacklist(array $args)
 * @method mixed getChildcountlist(array $args)
 * @method mixed getCount(array $args)
 * @method mixed getCountlist(array $args)
 * @method mixed getHostname(array $args)
 * @method mixed getModuleLrvalues(array $args)
 * @method mixed getMultiple(array $args)
 * @method mixed getMultipleall(array $args)
 * @method mixed getNodeLrvalues(array $args)
 * @method mixed getNodeRoot(array $args)
 * @method mixed getObjectList(array $args)
 * @method mixed getOne(array $args)
 * @method mixed getitemlinks(array $args)
 * @method mixed getitems(array $args)
 * @method mixed getoptions(array $args)
 * @method mixed modcounts(array $args)
 * @method mixed modify(array $args)
 * @method mixed moditemcounts(array $args)
 * @method mixed removeGap(array $args)
 * @method mixed search(array $args)
 * @method mixed setoptions(array $args)
 * @extends UserApiClass<Module>
 */
class UserApi extends UserApiClass
{
    // ...
}
