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
use Xaraya\Modules\Comments\Renderer;
use Xaraya\Modules\MethodClass;
use xarModHooks;
use BadParameterException;

/**
 * comments user display function
 * @extends MethodClass<UserGui>
 */
class DisplayMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Displays a comment or set of comments
     * @author Carl P. Corliss (aka rabbitt)
     * @access public
     * @param array<mixed> $args
     * @var integer $args['modid']              the module id
     * @var integer $args['itemtype']           the item type
     * @var string $args['objectid']           the item id
     * @var integer $args['depth']              depth of comment thread to display
     * @var integer $args['selected_id']      optional: the cid of the comment to view (only for displaying single comments)
     * @var integer $args['thread']           optional: display the entire thread following cid
     * @var integer $args['preview']          optional: an array containing a single (preview) comment used with adding/editing comments
     * @var bool $args['noposting']        optional: a boolean to define whether posting is enabled
     * @return array|string|null returns whatever needs to be parsed by the BlockLayout engine
     * @see UserGui::display()
     */
    public function __invoke(array $args = [])
    {
        /** @var UserApi $userapi */
        $userapi = $this->userapi();
        if (!$this->sec()->checkAccess('ReadComments', 0)) {
            return;
        }

        // Check if an object was passed
        if (isset($args['object'])) {
            $fields['moduleid'] = $args['object']->moduleid;
            $fields['itemtype'] = $args['object']->itemtype;
            $fields['itemid'] = $args['object']->properties['id']->value;
            $fields['parent_url'] = $this->ctl()->getCurrentURL();
        } else {
            // Check for required args
            $ishooked = 0;
            // then check for a 'id' parameter
            if (!empty($args['id'])) {
                $comment_id = $args['id'];
            } else {
                $this->var()->find('comment_id', $data['comment_id'], 'int:1:', 0);
            }
            // and set the selected id to this one
            if (!empty($data['comment_id']) && !isset($data['selected_id'])) {
                $data['selected_id'] = $data['comment_id'];
            }
        }

        # --------------------------------------------------------
        # Bail if the proper args were not passed
        #
        if (empty($fields)) {
            return $this->mod()->template('errors', ['layout' => 'no_direct_access']);
        }

        # --------------------------------------------------------
        # Try and get a selectee ID if we don't have one yet
        #
        if (empty($data['selected_id'])) {
            $this->var()->find('selected_id', $data['selected_id'], 'int', 0);
        }

        # --------------------------------------------------------
        # Get the current comment
        #
        $data['object'] = $this->data()->getObject(['name' => 'comments_comments']);
        if (!empty($data['selected_id'])) {
            $data['object']->getItem(['itemid' => $data['selected_id']]);
        }
        $data['selected_id'] = $data['object']->properties['id']->value;

        # --------------------------------------------------------
        # Add any attributes passed
        #
        if (isset($args['tplmodule'])) {
            $data['object']->tplmodule = $args['tplmodule'];
        }

        # --------------------------------------------------------
        # Load the comment object with what we know about the environment
        #
        $data['object']->setFieldValues($fields, 1);
        $fields = $data['object']->getFieldValues([], 1);

        # --------------------------------------------------------
        # Create an empty object for display and add any attributes passed
        #
        $data['emptyobject'] = $this->data()->getObject(['name' => 'comments_comments']);
        if (isset($args['tplmodule'])) {
            $data['object']->tplmodule = $args['tplmodule'];
        }

        # --------------------------------------------------------
        # Get the viewing options: depth, render style, order, and sortby
        #
        $package['settings'] = $userapi->getoptions();

        if (!isset($args['thread'])) {
            $this->var()->find('thread', $thread);
        }

        if (!$this->mod()->load('comments', 'renderer')) {
            $msg = $this->ml('Unable to load #(1) #(2)', 'comments', 'renderer');
            throw new BadParameterException($msg);
        }

        if (empty($data['selected_id']) || isset($thread)) {
            $data['comments'] = $userapi->get_multiple($fields);
            if (count($data['comments']) > 1) {
                $data['comments'] = Renderer::array_sort(
                    $data['comments'],
                    $package['settings']['sortby'],
                    $package['settings']['order']
                );
            }
        } else {
            $package['settings']['render'] = Defines::VIEW_FLAT;
            $data['comments'] = $userapi->get_one($fields);
        }

        $data['comments'] = Renderer::array_prune_excessdepth(
            [
                'array_list'    => $data['comments'],
                'cutoff'        => $package['settings']['depth'],
                'moduleid'      => $fields['moduleid'],
                'itemtype'      => $fields['itemtype'],
                'itemid'        => $fields['itemid'],
            ]
        );

        if ($package['settings']['render'] == Defines::VIEW_THREADED) {
            $data['comments'] = Renderer::array_maptree($data['comments']);
        }

        // run text and title through transform hooks
        if (!empty($data['comments'])) {
            foreach ($data['comments'] as $key => $comment) {
                $comment['text'] = $this->prep()->html($comment['text']);
                $comment['title'] = $this->prep()->text($comment['title']);
                // say which pieces of text (array keys) you want to be transformed
                $comment['transform'] = ['text'];
                // call the item transform hooks
                // Note : we need to tell Xaraya explicitly that we want to invoke the hooks for 'comments' here (last argument)
                $data['comments'][$key] = xarModHooks::call('item', 'transform', $comment['id'], $comment, 'comments');
            }
        }

        $package['settings']['max_depth'] = Defines::MAX_DEPTH;
        // Bug 6175: removed $this->prep()->text() from the title, as articles already
        // does this *but* maybe needs fixing in articles instead?
        $package['new_title']             = $this->mem()->get('Comments.title', 'title');

        $this->var()->find('comment_action', $data['comment_action'], 'str', 'submit');

        $hooks = $userapi->formhooks();

        if (!empty($data['comments'])) {
            $baseurl = $this->ctl()->getCurrentURL();
            foreach ($data['comments'] as $key => $val) {
                $data['comments'][$key]['parent_url'] = str_replace($baseurl, '', $data['comments'][$key]['parent_url']);
            }
        }

        $data['hooks']   = $hooks;
        $data['package'] = $package;

        $data['comment_id'] = $data['selected_id'];

        // Pass posting parameter to the template
        if (isset($args['noposting'])) {
            $data['noposting'] = $args['noposting'];
        } else {
            $data['noposting'] = false;
        }

        return $data;
    }
}
