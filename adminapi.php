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

use Xaraya\Modules\AdminApiClass;

/**
 * Handle the comments admin API
 *
 * @method mixed countComments(array $args)
 * @method mixed deleteBranch(array $args)
 * @method mixed deleteNode(array $args)
 * @method mixed getmenulinks(array $args)
 * @method mixed importBlacklist(array $args)
 * @method mixed removeModule(array $args)
 * @method mixed sort(array $args)
 * @extends AdminApiClass<Module>
 */
class AdminApi extends AdminApiClass
{
    // ...
}
