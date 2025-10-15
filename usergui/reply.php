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
use Xaraya\Modules\Comments\Defines;
use Xaraya\Modules\MethodClass;
use xarModHooks;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * comments user reply function
 * @extends MethodClass<UserGui>
 */
class ReplyMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * processes comment replies and then redirects back to the
     * appropriate module/object itemid (aka page)
     * @author Carl P. Corliss (aka rabbitt)
     * @access public
     * @return array|string|null returns whatever needs to be parsed by the BlockLayout engine
     * @see UserGui::reply()
     */
    public function __invoke(array $args = [])
    {
        /** @var UserApi $userapi */
        $userapi = $this->userapi();
        if (!$this->sec()->checkAccess('PostComments')) {
            return;
        }


        # --------------------------------------------------------
        # Get all the relevant info from the submitted comments form
        #


        # --------------------------------------------------------
        # Take appropriate action
        #
        $this->var()->find('comment_action', $data['comment_action'], 'str', 'reply');
        switch (strtolower($data['comment_action'])) {
            case 'submit':
                # --------------------------------------------------------
                # Get the values from the form
                #
                $data['reply'] = $this->data()->getObject(['name' => 'comments_comments']);
                $valid = $data['reply']->checkInput();

                // call transform input hooks
                // should we look at the title as well?
                $package['transform'] = ['text'];

                $package = xarModHooks::call(
                    'item',
                    'transform-input',
                    0,
                    $package,
                    'comments',
                    0
                );

                if ($this->mod()->getVar('AuthorizeComments') || $this->sec()->checkAccess('AddComments')) {
                    $status = Defines::STATUS_ON;
                } else {
                    $status = Defines::STATUS_OFF;
                }

                # --------------------------------------------------------
                # If something is wrong, represent the form
                #
                if (!$valid) {
                    return $this->mod()->template('reply', $data);
                }

                # --------------------------------------------------------
                # Everything is go: if there is a comment, create and go to the next page
                #
                if (!empty($data['reply']->properties['text']->value)) {
                    $data['comment_id'] = $data['reply']->createItem();
                } else {
                    $data['comment_id'] = 0;
                }
                $this->ctl()->redirect($data['reply']->properties['parent_url']->value . '#' . $data['comment_id']);
                return true;

            case 'reply':
                # --------------------------------------------------------
                # Bail if the proper args were not passed
                #
                $this->var()->find('comment_id', $data['comment_id'], 'int:1:', 0);
                if (empty($data['comment_id'])) {
                    return $this->ctl()->notFound();
                }

                # --------------------------------------------------------
                # Create the comment object
                #
                sys::import('modules.dynamicdata.class.objects.factory');
                $data['object'] = $this->data()->getObject(['name' => 'comments_comments']);
                $data['object']->getItem(['itemid' => $data['comment_id']]);

                // replace the deprecated eregi stuff below
                $title = & $data['object']->properties['title']->value;
                $text  = & $data['object']->properties['text']->value;
                $title = preg_replace('/^re:/i', '', $title);
                $new_title = 'Re: ' . $title;

                // get the title and link of the original object
                $modinfo = $this->mod()->getInfo($data['object']->properties['moduleid']->value);
                try {
                    $itemlinks = $this->mod()->apiFunc(
                        $modinfo['name'],
                        'user',
                        'getitemlinks',
                        ['itemtype' => $data['object']->properties['itemtype']->value,
                            'itemids' => [$data['object']->properties['itemid']->value], ]
                    );
                } catch (Exception $e) {
                }
                if (!empty($itemlinks) && !empty($itemlinks[$data['object']->properties['itemid']->value])) {
                    $url = $itemlinks[$header['itemid']]['url'];
                    $header['objectlink'] = $itemlinks[$data['object']->properties['itemid']->value]['url'];
                    $header['objecttitle'] = $itemlinks[$data['object']->properties['itemid']->value]['label'];
                } else {
                    $url = $this->ctl()->getModuleURL($modinfo['name'], 'user', 'main');
                }
                /*
                            list($text,
                                 $title) =
                                        xarModHooks::call('item',
                                                        'transform',
                                                         $data['object']->properties['parent_id']->value,
                                                         array($text,
                                                               $title));
                */
                $text         = $this->var()->prepHTML($text);
                $title        = $this->var()->prep($title);

                $package['new_title']            = $this->var()->prep($new_title);
                $data['package']               = $package;

                // Create an object item for the reply
                $data['reply'] = $this->data()->getObject(['name' => 'comments_comments']);
                $data['reply']->properties['title']->value = $new_title;
                $data['reply']->properties['position']->reference_id = $data['comment_id'];
                $data['reply']->properties['position']->position = 3;
                $data['reply']->properties['moduleid']->value = $data['object']->properties['moduleid']->value;
                $data['reply']->properties['itemtype']->value = $data['object']->properties['itemtype']->value;
                $data['reply']->properties['itemid']->value = $data['object']->properties['itemid']->value;
                $data['reply']->properties['parent_url']->value = $data['object']->properties['parent_url']->value;
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
                $package['transformed-title'] = $this->var()->prep($package['transformed-title']);
                $package['text']              = $this->var()->prepHTML($package['text']);
                $package['title']             = $this->var()->prep($package['title']);

                $comments[0]['text']      = $package['text'];
                $comments[0]['title']     = $package['title'];
                $comments[0]['moduleid']  = $header['moduleid'];
                $comments[0]['itemtype']  = $header['itemtype'];
                $comments[0]['itemid']    = $header['itemid'];
                $comments[0]['parent_id'] = $header['parent_id'];
                $comments[0]['author']    = (($this->user()->isLoggedIn() && !$package['postanon']) ? $this->user()->getName() : 'Anonymous');
                $comments[0]['id']       = 0;
                $comments[0]['postanon']  = $package['postanon'];
                // FIXME delete after time output testing
                // $comments[0]['date']      = $this->mls()->formatDate("%d %b %Y %H:%M:%S %Z",time());
                $comments[0]['date']      = time();
                $comments[0]['hostname']  = 'somewhere';

                $package['comments']          = $comments;
                $package['new_title']         = $package['title'];
                $receipt['action']            = 'reply';

                break;
        }

        $hooks = $userapi->formhooks();
        /*
            // Call new hooks for categories, dynamicdata etc.
            $args['module'] = 'comments';
            $args['itemtype'] = 0;
            $args['itemid'] = 0;
            // pass along the current module & itemtype for pubsub (urgh)
        // FIXME: handle 2nd-level hook calls in a cleaner way - cfr. categories navigation, comments add etc.
            $args['id'] = 0; // dummy category
            $modinfo = $this->mod()->getInfo($header['moduleid']);
            $args['current_module'] = $modinfo['name'];
            $args['current_itemtype'] = $header['itemtype'];
            $args['current_itemid'] = $header['itemid'];
            $hooks['iteminput'] = xarModHooks::call('item', 'new', 0, $args);
        */

        # --------------------------------------------------------
        # Pass args to the form template
        #
        $anonuid = $this->config()->getVar('Site.User.AnonymousUID');
        $data['hooks']              = $hooks;
        $data['package']            = $package;
        $data['package']['date']    = time();
        $data['package']['role_id']     = (($this->user()->isLoggedIn() && !$data['object']->properties['anonpost']->value) ? $this->user()->getId() : $anonuid);
        $data['package']['uname']   = (($this->user()->isLoggedIn() && !$data['object']->properties['anonpost']->value) ? $this->user()->getUser() : 'anonymous');
        $data['package']['name']    = (($this->user()->isLoggedIn() && !$data['object']->properties['anonpost']->value) ? $this->user()->getName() : 'Anonymous');

        return $data;
    }
}
