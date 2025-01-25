<?php

/**
 * @package modules\comments
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Comments\UserGui;


use Xaraya\Modules\Comments\UserGui;
use Xaraya\Modules\Comments\UserApi;
use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarMod;
use xarController;
use xarModVars;
use xarVar;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * comments user rss function
 * @extends MethodClass<UserGui>
 */
class RssMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Configures a comments RSS output
     * @author John Cox
     * @access public
     * @return array|void
     * @see UserGui::rss()
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        /** @var UserApi $userapi */
        $userapi = $this->userapi();
        if (!$this->sec()->checkAccess('ReadComments', 0)) {
            return;
        }

        // get the list of modules+itemtypes that comments is hooked to
        $hookedmodules = xarMod::apiFunc(
            'modules',
            'admin',
            'gethookedmodules',
            ['hookModName' => 'comments']
        );

        // initialize list of module and pubtype names
        $items   = [];
        $modlist = [];
        $modname = [];
        $modview = [];
        $modlist['all'] = $this->ml('All');
        // make sure we only retrieve comments from hooked modules
        $todolist = [];
        if (isset($hookedmodules) && is_array($hookedmodules)) {
            foreach ($hookedmodules as $module => $value) {
                $modid = xarMod::getRegID($module);
                if (!isset($modname[$modid])) {
                    $modname[$modid] = [];
                }
                if (!isset($modview[$modid])) {
                    $modview[$modid] = [];
                }
                $modname[$modid][0] = ucwords($module);
                $modview[$modid][0] = $this->ctl()->getModuleURL($module, 'user', 'view');
                // Get the list of all item types for this module (if any)
                try {
                    $mytypes = xarMod::apiFunc($module, 'user', 'getitemtypes');
                } catch (Exception $e) {
                    $mytypes = [];
                }
                if (!empty($mytypes) && count($mytypes) > 0) {
                    foreach (array_keys($mytypes) as $itemtype) {
                        $modname[$modid][$itemtype] = $mytypes[$itemtype]['label'];
                        $modview[$modid][$itemtype] = $mytypes[$itemtype]['url'];
                    }
                }
                // we have hooks for individual item types here
                if (!isset($value[0])) {
                    foreach ($value as $itemtype => $val) {
                        $todolist[] = "$module.$itemtype";
                        if (isset($mytypes[$itemtype])) {
                            $type = $mytypes[$itemtype]['label'];
                        } else {
                            $type = $this->ml('type #(1)', $itemtype);
                        }
                        $modlist["$module.$itemtype"] = ucwords($module) . ' - ' . $type;
                    }
                } else {
                    $todolist[] = $module;
                    $modlist[$module] = ucwords($module);
                    // allow selecting individual item types here too (if available)
                    if (!empty($mytypes) && count($mytypes) > 0) {
                        foreach ($mytypes as $itemtype => $mytype) {
                            if (!isset($mytype['label'])) {
                                continue;
                            }
                            $modlist["$module.$itemtype"] = ucwords($module) . ' - ' . $mytype['label'];
                        }
                    }
                }
            }
        }
        $args['modarray']   = $todolist;
        $args['howmany']    = $this->mod()->getVar('rssnumitems');
        $items = $userapi->get_multipleall($args);

        for ($i = 0; $i < count($items); $i++) {
            $item = $items[$i];
            $modinfo = xarMod::getInfo($item['modid']);
            $items[$i]['rsstitle']      = htmlspecialchars($item['subject']);
            try {
                $linkarray                  = xarMod::apiFunc(
                    $modinfo['name'],
                    'user',
                    'getitemlinks',
                    ['itemtype' => $item['itemtype'],
                        'itemids'  => [$item['objectid']], ]
                );
            } catch (Exception $e) {
            }
            if (!empty($linkarray)) {
                foreach ($linkarray as $url) {
                    $items[$i]['link'] = $url['url'];
                }
            } else {
                // We'll use the comment link instead
                $items[$i]['link'] = $this->mod()->getURL( 'user', 'display', ['id' => $item['id']]);
            }

            $items[$i]['rsssummary'] = preg_replace('<br />', "\n", $item['text']);
            $items[$i]['rsssummary'] = $this->var()->prep(strip_tags($item['text']));
        }

        //$output = var_export($items, 1); return "<pre>$output</pre>";
        $data['items'] = $items;
        return $data;
    }
}
