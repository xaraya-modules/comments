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

use Xaraya\Modules\MethodClass;
use xarModAlias;
use xarMod;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments userapi decode_shorturl function
 */
class DecodeShorturlMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * extract function and arguments from short URLs for this module, and pass
     * them back to xarGetRequestInfo()
     * @author the Comments module development team
     * @param mixed $params array containing the different elements of the virtual path
     * @return array|void containing func the function to be called and args the query
     * string arguments, or empty if it failed
     */
    public function __invoke(array $params = [])
    {
        // Initialise the argument list we will return
        $args = [];

        // Analyse the different parts of the virtual path
        // $params[1] contains the first part after index.php/example

        // In general, you should be strict in encoding URLs, but as liberal
        // as possible in trying to decode them...

        if (empty($params[1])) {
            // nothing specified -> we'll go to the main function
            return ['main', $args];
        } elseif (preg_match('/^(\d+)/', $params[1], $matches)) {
            // something that starts with a number must be for the display function
            // Note : make sure your encoding/decoding is consistent ! :-)
            $id = $matches[1];
            $args['id'] = $id;
            return ['display', $args];

            /* to be reviewed - probably better to redirect to the module item itself if we know all this
                } elseif (preg_match('/^(\w+)/',$params[1],$matches)) {
                    // something that starts with a name might be for the display function
                    // Note : make sure your encoding/decoding is consistent ! :-)
                    $modname = $matches[1];
                    $alias = xarModAlias::resolve($modname);
                    if ($modname != $alias) {
                        $itemtype = 0;
                        // try to figure out which itemtype we're dealing with
                        $itemtypes = xarMod::apiFunc($alias,'user','getitemtypes',
                                                   array(),0);
                        if (!empty($itemtypes) && count($itemtypes) > 0) {
                            foreach ($itemtypes as $id => $info) {
                                if (!empty($info['name']) && $modname == $info['name']) {
                                    $itemtype = $id;
                                    break;
                                }
                            }
                        }
                        $modname = $alias;
                    } else {
                        $itemtype = 0;
                    }
                    if (xarMod::isAvailable($modname)) {
                        $modid = xarMod::getRegID($modname);
                        if (!empty($modid)) {
                            $args['modid'] = $modid;
                            if (preg_match('/^(\d+)/',$params[2],$matches)) {
                                $temp = $matches[1];
                                if (empty($params[3])) {
                                    $args['itemtype'] = $itemtype;
                                    $args['objectid'] = $temp;
                                    return array('display', $args);
                                } elseif (preg_match('/^(\d+)/',$params[3],$matches)) {
                                    $args['itemtype'] = $temp;
                                    $args['objectid'] = $matches[1];
                                    return array('display', $args);
                                }
                            }
                        }
                    }
            */
        } else {
            // we have no idea what this virtual path could be, so we'll just
            // forget about trying to decode this thing

            // you *could* return the main function here if you want to
            // return array('main', $args);
        }

        // default : return nothing -> no short URL decoded
    }
}
