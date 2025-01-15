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

use Xaraya\Modules\AdminGuiClass;
use sys;

sys::import('xaraya.modules.admingui');
sys::import('modules.comments.class.adminapi');

/**
 * Handle the comments admin GUI
 *
 * @method mixed importblacklist(array $args)
 * @method mixed main(array $args)
 * @method mixed modify(array $args)
 * @method mixed modifyconfig(array $args)
 * @method mixed moduleStats(array $args)
 * @method mixed overview(array $args)
 * @method mixed stats(array $args)
 * @method mixed view(array $args)
 * @extends AdminGuiClass<Module>
 */
class AdminGui extends AdminGuiClass
{
    // ...
}
