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

use Xaraya\Modules\UserGuiClass;
use sys;

sys::import('xaraya.modules.usergui');
sys::import('modules.comments.class.userapi');

/**
 * Handle the comments user GUI
 *
 * @method mixed delete(array $args)
 * @method mixed display(array $args)
 * @method mixed displayall(array $args)
 * @method mixed main(array $args)
 * @method mixed modify(array $args)
 * @method mixed reply(array $args)
 * @method mixed rss(array $args)
 * @method mixed search(array $args)
 * @method mixed usermenu(array $args)
 * @extends UserGuiClass<Module>
 */
class UserGui extends UserGuiClass
{
    /**
     * User main GUI function
     * @param array<string, mixed> $args
     * @return array<mixed>
     */
    public function main(array $args = [])
    {
        $args['description'] ??= 'Description of comments';

        // Pass along the context for xarTpl::module() if needed
        $args['context'] ??= $this->getContext();
        return $args;
    }
}