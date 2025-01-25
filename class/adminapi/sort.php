<?php

/**
 * @package modules\comments
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Comments\AdminApi;


use Xaraya\Modules\Comments\AdminApi;
use Xaraya\Modules\MethodClass;
use xarVar;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments adminapi sort function
 * @extends MethodClass<AdminApi>
 */
class SortMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Sorting
     * @author potion <ryan@webcommunicate.net>
     * @return string|void a string like 'itemid ASC';
     * @see AdminApi::sort()
     */
    public function __invoke(array $args = [])
    {
        // Default URL strings to look for
        $url_sortfield = 'sortfield';
        $url_ascdesc = 'ascdesc';

        extract($args);

        if (!$this->var()->check($url_sortfield, $sortfield)) {
            return;
        }
        if (!$this->var()->find($url_ascdesc, $ascdesc)) {
            return;
        }

        if (!isset($sort)) {
            if (!isset($sortfield)) {
                $sortfield = $sortfield_fallback;
            }

            if (!isset($ascdesc)) {
                $ascdesc = $ascdesc_fallback;
            }

            $sort = $sortfield . ' ' . $ascdesc;
        }

        return $sort;
    }
}
