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
use Exception;

/**
 * comments user displayall function
 * @extends MethodClass<UserGui>
 */
class DisplayallMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Display comments from one or more modules and item types
     * @see UserGui::displayall()
     */
    public function __invoke(array $args = [])
    {
        /** @var UserApi $userapi */
        $userapi = $this->userapi();
        $this->var()->find('modid', $args['modid'], 'array', ['all']);
        ;
        $this->var()->find('itemtype', $args['itemtype'], 'int');
        ;
        $this->var()->get('order', $args['order'], 'str', 'DESC');
        ;
        $this->var()->get('howmany', $args['howmany'], 'id', 20);
        ;
        $this->var()->get('first', $args['first'], 'id', 1);
        ;

        if (empty($args['block_is_calling'])) {
            $args['block_is_calling'] = 0;
        }
        if (empty($args['truncate'])) {
            $args['truncate'] = '';
        }
        if (!isset($args['addmodule'])) {
            $args['addmodule'] = 'off';
        }
        if (!isset($args['addobject'])) {
            $args['addobject'] = 21;
        }
        if (!isset($args['addcomment'])) {
            $args['addcomment'] = 20;
        }
        if (!isset($args['adddate'])) {
            $args['adddate'] = 'on';
        }
        if (!isset($args['adddaysep'])) {
            $args['adddaysep'] = 'on';
        }
        if (!isset($args['addauthor'])) {
            $args['addauthor'] = 1;
        }
        if (!isset($args['addprevious'])) {
            $args['addprevious'] = 0;
        }

        /*$args['returnurl'] = '';*/
        $modarray = $args['modid'];
        // get the list of modules+itemtypes that comments is hooked to
        $hookedmodules = $this->mod()->apiFunc(
            'modules',
            'admin',
            'gethookedmodules',
            ['hookModName' => 'comments']
        );

        // initialize list of module and pubtype names
        $modlist = [];
        $modname = [];
        $modview = [];
        $modlist['all'] = $this->ml('All');
        // make sure we only retrieve comments from hooked modules
        $todolist = [];
        if (isset($hookedmodules) && is_array($hookedmodules)) {
            foreach ($hookedmodules as $module => $value) {
                $modid = $this->mod()->getRegID($module);
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
                    $mytypes = $this->mod()->apiFunc($module, 'user', 'getitemtypes');
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

        // replace 'all' with the list of hooked modules (+ xarbb if necessary ?)
        if (count($modarray) == 1 && $modarray[0] == 'all') {
            $args['modarray'] = $todolist;
        } else {
            $args['modarray'] = $modarray;
        }

        $comments = $userapi->get_multipleall($args);
        // Bug 6188: why is this needed? getoptions needs one hooked module to get the options for
        //$settings = $userapi->getoptions();

        if (!empty($args['order'])) {
            $settings['order'] = $args['order'];
        }

        // get title and link for all module items (where possible)
        $items = [];
        if (!empty($args['addobject'])) {
            // build a list of item ids per module and item type
            foreach (array_keys($comments) as $i) {
                $modid = $comments[$i]['modid'];
                $itemtype = $comments[$i]['itemtype'];
                if (!isset($items[$modid])) {
                    $items[$modid] = [];
                }
                if (!isset($items[$modid][$itemtype])) {
                    $items[$modid][$itemtype] = [];
                }
                $itemid = $comments[$i]['objectid'];
                $items[$modid][$itemtype][$itemid] = '';
            }
            // for each module and itemtype, retrieve the item links (if available)
            foreach ($items as $modid => $itemtypes) {
                $modinfo = $this->mod()->getInfo($modid);
                foreach ($itemtypes as $itemtype => $itemlist) {
                    $itemids = array_keys($itemlist);
                    try {
                        $itemlinks = $this->mod()->apiFunc(
                            $modinfo['name'],
                            'user',
                            'getitemlinks',
                            ['itemtype' => $itemtype,
                                'itemids' => $itemids]
                        );
                    } catch (Exception $e) {
                        $itemlinks = [];
                    }
                    if (!empty($itemlinks) && count($itemlinks) > 0) {
                        foreach ($itemlinks as $itemid => $itemlink) {
                            $items[$modid][$itemtype][$itemid] = $itemlink;
                        }
                    }
                }
            }
        }

        // generate comments array of arrays; each date has an array of comments
        // posted on that date
        $commentsarray = [];
        $timenow = time();
        $hoursnow = $this->mls()->formatDate("%H", $timenow);
        $dateprev = '';
        $numcomments = count($comments);
        for ($i = 0;$i < $numcomments;$i++) {
            if ($args['adddaysep'] == 'on') {
                // find out whether to change day separator
                $msgunixtime = $comments[$i]['datetime'];
                $msgdate = $this->mls()->formatDate("%b %d, %Y", $msgunixtime);
                $msgday = $this->mls()->formatDate("%A", $msgunixtime);

                $hoursdiff = ($timenow - $msgunixtime) / 3600;
                if ($hoursdiff < $hoursnow && $msgdate != $dateprev) {
                    $daylabel = $this->ml('Today');
                } elseif ($hoursdiff >= $hoursnow && $hoursdiff < $hoursnow + 24 && ($msgdate != $dateprev)) {
                    $daylabel = $this->ml('Yesterday');
                } elseif ($hoursdiff >= $hoursnow + 24 && $hoursdiff < $hoursnow + 48 && $msgdate != $dateprev) {
                    $daylabel = $this->ml('Two days ago');
                } elseif ($hoursdiff >= $hoursnow + 48 && $hoursdiff < $hoursnow + 144 && $msgdate != $dateprev) {
                    $daylabel = $msgday;
                } elseif ($hoursdiff >= $hoursnow + 144 && $msgdate != $dateprev) {
                    $daylabel = $msgdate;
                }
                $dateprev = $msgdate;
            } else {
                // no need to keep track of date
                $daylabel = 'none';
            }

            // add title, url and truncate comments if requested
            $modid = $comments[$i]['modid'];
            $itemtype = $comments[$i]['itemtype'];
            $itemid = $comments[$i]['objectid'];
            if (!empty($args['addobject']) && !empty($items[$modid][$itemtype][$itemid])) {
                $comments[$i]['title'] = $items[$modid][$itemtype][$itemid]['label'];
                $comments[$i]['objurl'] = $items[$modid][$itemtype][$itemid]['url'];
            }
            if (isset($modname[$modid][$itemtype])) {
                $comments[$i]['modname'] = $modname[$modid][$itemtype];
            }
            if (isset($modview[$modid][$itemtype])) {
                $comments[$i]['modview'] = $modview[$modid][$itemtype];
            }

            //$comments[$i]['returnurl'] = urlencode($modview[$modid][$itemtype]);
            //$comments[$i]['returnurl'] = null;
            if ($args['truncate']) {
                if (strlen($comments[$i]['subject']) > $args['truncate'] + 3) {
                    $comments[$i]['subject'] = substr($comments[$i]['subject'], 0, $args['truncate']) . '...';
                }
                if (!empty($comments[$i]['title']) && strlen($comments[$i]['title']) > $args['truncate'] - 3) {
                    $comments[$i]['title'] = substr($comments[$i]['title'], 0, $args['truncate']) . '...';
                }
            }
            $comments[$i]['subject'] = $this->prep()->text($comments[$i]['subject']);
            if (!empty($comments[$i]['text'])) {
                $comments[$i]['text'] = $this->prep()->html($comments[$i]['text']);
            }
            [$comments[$i]['text'],
                $comments[$i]['subject']] = $this->mod()->callHooks(
                    'item',
                    'transform',
                    $comments[$i]['id'],
                    [$comments[$i]['text'],
                        $comments[$i]['subject'], ],
                    'comments'
                );
            $commentsarray[$daylabel][] = $comments[$i];
        }

        // prepare for output
        $templateargs['order']          = $args['order'];
        $templateargs['howmany']        = $args['howmany'];
        $templateargs['first']          = $args['first'];
        $templateargs['adddate']        = $args['adddate'];
        $templateargs['adddaysep']      = $args['adddaysep'];
        $templateargs['addauthor']      = $args['addauthor'];
        $templateargs['addmodule']      = $args['addmodule'];
        $templateargs['addcomment']     = $args['addcomment'];
        $templateargs['addobject']      = $args['addobject'];
        $templateargs['addprevious']    = $args['addprevious'];
        $templateargs['modarray']       = $modarray;
        $templateargs['modid']          = $modarray;
        $templateargs['itemtype']       = $itemtype ?? 0;
        $templateargs['modlist']        = $modlist;
        /*$templateargs['decoded_returnurl'] = rawurldecode($this->mod()->getURL('user', 'displayall'));*/
        $templateargs['decoded_nexturl'] = $this->mod()->getURL(
            'user',
            'displayall',
            [
                'first' => $args['first'] + $args['howmany'],
                'howmany' => $args['howmany'],
                'modid' => $modarray, ]
        );
        $templateargs['commentlist']    = $commentsarray;
        $templateargs['order']          = $settings['order'];

        if ($args['block_is_calling'] == 0) {
            $data = $this->render('displayall', $templateargs);
        } else {
            $templateargs['olderurl'] = $this->mod()->getURL(
                'user',
                'displayall',
                [
                    'first' =>   $args['first'] + $args['howmany'],
                    'howmany' => $args['howmany'],
                    'modid' => $modarray,
                ]
            );
            $data = $this->tpl()->block('comments', 'latestcommentsblock', $templateargs);
        }

        return $data;
    }
}
