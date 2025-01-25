<?php

/**
 * @package modules\comments
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Comments\UserApi;


use Xaraya\Modules\Comments\UserApi;
use Xaraya\Modules\MethodClass;
use xarMod;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments userapi encode_shorturl function
 * @extends MethodClass<UserApi>
 */
class EncodeShorturlMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * return the path for a short URL to xarController::URL for this module
     * @author the Comments module development team
     * @param mixed $args the function and arguments passed to xarController::URL
     * @return string|void path to be added to index.php for a short URL, or empty if failed
     * @see UserApi::encodeShorturl()
     */
    public function __invoke(array $args = [])
    {
        // Get arguments from argument array
        extract($args);

        // Check if we have something to work with
        if (!isset($func)) {
            return;
        }

        // Note : make sure you don't pass the following variables as arguments in
        // your module too - adapt here if necessary

        // default path is empty -> no short URL
        $path = '';
        // if we want to add some common arguments as URL parameters below
        $join = '?';
        // we can't rely on xarMod::getName() here -> you must specify the modname !
        $module = 'comments';

        // specify some short URLs relevant to your module
        if ($func == 'display') {
            // check for required parameters
            if (!empty($id) && is_numeric($id)) {
                $path = '/' . $module . '/' . $id;
            }
        } else {
            // anything else that you haven't defined a short URL equivalent for
            // -> don't create a path here
        }

        // add some other module arguments as standard URL parameters
        if (!empty($path) && isset($startnum)) {
            $path .= $join . 'startnum=' . $startnum;
        }

        return $path;
    }
}
