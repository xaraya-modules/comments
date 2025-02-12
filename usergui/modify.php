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
use xarVar;
use xarController;
use xarUser;
use xarSecurity;
use xarMod;
use xarModHooks;
use xarTpl;
use xarLocale;
use xarServer;
use DataObjectFactory;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * comments user modify function
 * @extends MethodClass<UserGui>
 */
class ModifyMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Modify a comment
     * This is dependant on the following criteria:
     * 1. user is the owner of the comment, or
     * 2. user has a minimum of moderator permissions for the
     *    specified comment
     * 3. we haven't reached the edit time limit if it is set
     * @author Carl P. Corliss (aka rabbitt)
     * @access private
     * @return mixed description of return
     * @see UserGui::modify()
     */
    public function __invoke(array $args = [])
    {
        /** @var UserApi $userapi */
        $userapi = $this->userapi();
        if (!$this->var()->find('parent_url', $parent_url, 'str', 0)) {
            return;
        }
        if (!$this->var()->find('adminreturn', $data['adminreturn'], 'str')) {
            return;
        }

        # --------------------------------------------------------
        # Bail if the proper args were not passed
        #
        if (!$this->var()->find('comment_id', $data['comment_id'], 'int:1:', 0)) {
            return;
        }
        if (empty($data['comment_id'])) {
            return $this->ctl()->notFound();
        }

        # --------------------------------------------------------
        # Create the comment object and get the item to modify
        #
        sys::import('modules.dynamicdata.class.objects.factory');
        $data['object'] = $this->data()->getObject(['name' => 'comments_comments']);
        $data['object']->getItem(['itemid' => $data['comment_id']]);

        # --------------------------------------------------------
        # Check that this user can modify this comment
        #
        if ($data['object']->properties['author']->value != xarUser::getVar('id')) {
            if (!$this->sec()->checkAccess('EditComments')) {
                return;
            }
        }

        $header['moduleid'] = $data['object']->properties['moduleid']->value;
        $header['itemtype'] = $data['object']->properties['itemtype']->value;
        $header['itemid']   = $data['object']->properties['itemid']->value;

        // get the title and link of the original object
        $modinfo = $this->mod()->getInfo($data['object']->properties['moduleid']->value);
        try {
            $itemlinks = $this->mod()->apiFunc(
                $modinfo['name'],
                'user',
                'getitemlinks',
                ['itemtype' => $header['itemtype'],
                    'itemids' => [$header['itemid']], ]
            );
        } catch (Exception $e) {
        }
        if (!empty($itemlinks) && !empty($itemlinks[$header['itemid']])) {
            $url = $itemlinks[$header['itemid']]['url'];
            $header['objectlink'] = $itemlinks[$header['itemid']]['url'];
            $header['objecttitle'] = $itemlinks[$header['itemid']]['label'];
        } else {
            $url = $this->ctl()->getModuleURL($modinfo['name'], 'user', 'main');
        }
        /*if (empty($receipt['returnurl'])) {
            $receipt['returnurl'] = array('encoded' => rawurlencode($url),
                                          'decoded' => $url);
        }*/

        $package['settings'] = $userapi->getoptions($header);

        # --------------------------------------------------------
        # Take appropriate action
        #
        if (!$this->var()->find('comment_action', $data['comment_action'], 'str', 'modify')) {
            return;
        }
        switch ($data['comment_action']) {
            case 'submit':
                # --------------------------------------------------------
                # Get the values from the form
                #
                $valid = $data['object']->checkInput();

                // call transform input hooks
                // should we look at the title as well?
                $package['transform'] = ['text'];

                if (empty($package['settings']['edittimelimit'])
                   or (time() <= ($package['comments'][0]['xar_date'] + ($package['settings']['edittimelimit'] * 60)))
                   or $this->sec()->checkAccess('AdminComments')) {
                    $package = xarModHooks::call(
                        'item',
                        'transform-input',
                        0,
                        $package,
                        'comments',
                        0
                    );
                    # --------------------------------------------------------
                    # If something is wrong, redisplay the form
                    #
                    if (!$valid) {
                        return $this->mod()->template('modify', $data);
                    }

                    # --------------------------------------------------------
                    # Everything is go: update and go to the next page
                    #
                    $data['comment_id'] = $data['object']->updateItem();
                }

                if (isset($data['adminreturn']) && $data['adminreturn'] == 'yes') { // if we got here via the admin side
                    $this->ctl()->redirect($this->mod()->getURL('admin', 'view'));
                } else {
                    $this->ctl()->redirect($data['object']->properties['parent_url']->value . '#' . $data['comment_id']);
                }
                return true;
            case 'modify':
                $title = & $data['object']->properties['title']->value;
                $text  = & $data['object']->properties['text']->value;
                [$transformed_text,
                    $transformed_title] =
                           xarModHooks::call(
                               'item',
                               'transform',
                               $data['comment_id'],
                               [$text,
                                   $title, ]
                           );

                $data['transformed_text']    = $this->var()->prepHTML($transformed_text);
                $data['transformed_title']   = $this->var()->prep($transformed_title);
                $data['text']                = $this->var()->prepHTML($text);
                $data['title']               = $this->var()->prep($title);
                $data['comment_action']      = 'submit';

                break;
            case 'preview':
            default:
                [$package['transformed-text'],
                    $package['transformed-title']] = xarModHooks::call(
                        'item',
                        'transform',
                        $header['parent_id'],
                        [$package['text'],
                            $package['title'], ]
                    );

                $package['transformed-text']  = $this->var()->prepHTML($package['transformed-text']);
                $package['transformed-title'] = $this->var()->prepHTML($package['transformed-title']);
                $package['text']              = $this->var()->prep($package['text']);
                $package['title']             = $this->var()->prep($package['title']);

                $comments[0]['text']     = $package['text'];
                $comments[0]['title']    = $package['title'];
                $comments[0]['moduleid']    = $header['moduleid'];
                $comments[0]['itemtype'] = $header['itemtype'];
                $comments[0]['itemid'] = $header['itemid'];
                $comments[0]['parent_id']      = $header['parent_id'];
                $comments[0]['author']   = ((xarUser::isLoggedIn() && !$package['postanon']) ? xarUser::getVar('name') : 'Anonymous');
                $comments[0]['id']      = 0;
                $comments[0]['postanon'] = $package['postanon'];
                // FIXME Delete after time putput testing
                // $comments[0]['date']     = xarLocale::formatDate("%d %b %Y %H:%M:%S %Z",time());
                $comments[0]['date']     = time();

                $forwarded = xarServer::getVar('HTTP_X_FORWARDED_FOR');
                if (!empty($forwarded)) {
                    $hostname = preg_replace('/,.*/', '', $forwarded);
                } else {
                    $hostname = xarServer::getVar('REMOTE_ADDR');
                }

                $comments[0]['hostname'] = $hostname;
                $package['comments']         = $comments;
                $data['comment_action']      = 'modify';

                break;
        }

        $hooks = $userapi->formhooks();
        /*
            // Call modify hooks for categories, dynamicdata etc.
            $args['module'] = 'comments';
            $args['itemtype'] = 0;
            $args['itemid'] = $header['id'];
            // pass along the current module & itemtype for pubsub (urgh)
        // FIXME: handle 2nd-level hook calls in a cleaner way - cfr. categories navigation, comments add etc.
            $args['id'] = 0; // dummy category
            $modinfo = $this->mod()->getInfo($header['moduleid']);
            $args['current_module'] = $modinfo['name'];
            $args['current_itemtype'] = $header['itemtype'];
            $args['current_itemid'] = $header['itemid'];
            $hooks['iteminput'] = xarModHooks::call('item', 'modify', $header['id'], $args);
        */

        $data['hooks']              = $hooks;
        return $data;
    }
}
