<?php
/**
 * Comments module - Allows users to post comments on items
 *
 * @package modules
 * @copyright (C) 2002-2007 The copyright-placeholder
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://www.xaraya.com
 *
 * @subpackage comments
 * @link http://xaraya.com/index.php/release/14.html
 * @author Carl P. Corliss <rabbitt@xaraya.com>
 */
/**
 *
 * @package comments
 *
 */
sys::import('modules.dynamicdata.class.properties.base');
use Xaraya\Modules\Comments\Defines;
use Xaraya\Modules\Comments\Renderer;

class CommentsProperty extends DataProperty
{
    public $id         = 103;
    public $name       = 'comments';
    public $desc       = 'Comments';
    public $reqmodules = ['comments'];

    public function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);

        $this->tplmodule = 'comments';
        $this->filepath   = 'modules/comments/xarproperties';
    }

    public function showInput(array $data = [])
    {
        if (!$this->sec()->checkAccess('ReadComments', 0)) {
            return;
        }

        // Check for a 'id' parameter
        if (!empty($data['id'])) {
            $id = $data['id'];
        } else {
            $this->var()->find('id', $id, 'int:1:', 0);
        }

        // and set the selected id to this one
        if (!empty($id) && !isset($data['selected_id'])) {
            $data['selected_id'] = $id;
        }

        // TODO: now clean up the rest :-)

        $header   = xarController::getVar('header');
        $package  = xarController::getVar('package');
        $receipt  = xarController::getVar('receipt');

        // Fetch the module ID
        if (isset($data['modid'])) {
            $header['modid'] = $data['modid'];
        } elseif (isset($header['modid'])) {
            $data['modid'] = $header['modid'];
        } else {
            $this->var()->find('modid', $modid);
            if (empty($modid)) {
                $modid = $this->mod()->getRegID($this->mod()->getName());
            }
            $data['modid'] = $modid;
            $header['modid'] = $modid;
        }
        $header['modname'] = $this->mod()->getName($header['modid']);

        // Fetch the itemtype
        if (isset($data['itemtype'])) {
            $header['itemtype'] = $data['itemtype'];
        } elseif (isset($header['itemtype'])) {
            $data['itemtype'] = $header['itemtype'];
        } else {
            $this->var()->find('itemtype', $itemtype);
            $data['itemtype'] = $itemtype;
            $header['itemtype'] = $itemtype;
        }


        $package['settings'] = $this->mod()->apiMethod('comments', 'user', 'getoptions', $header);

        // FIXME: clean up return url handling

        $settings_uri = "&#38;depth={$package['settings']['depth']}"
            . "&#38;order={$package['settings']['order']}"
            . "&#38;sortby={$package['settings']['sortby']}"
            . "&#38;render={$package['settings']['render']}";

        // Fetch the object ID
        if (isset($data['object'])) {
            $header['objectid'] = $this->mod()->getID($data['object']);
        } elseif (isset($header['objectid'])) {
            $data['objectid'] = $header['objectid'];
        } else {
            $this->var()->find('objectid', $objectid);
            $data['objectid'] = $objectid;
            $header['objectid'] = $objectid;
        }

        if (isset($data['selected_id'])) {
            $header['selected_id'] = $data['selected_id'];
        } elseif (isset($header['selected_id'])) {
            $data['selected_id'] = $header['selected_id'];
        } else {
            $this->var()->find('selected_id', $selected_id);
            $data['selected_id'] = $selected_id;
            $header['selected_id'] = $selected_id;
        }
        if (!isset($data['thread'])) {
            $this->var()->find('thread', $thread);
        }
        if (isset($thread) && $thread == 1) {
            $header['cid'] = $cid;
        }

        if (!$this->mod()->load('comments', 'renderer')) {
            $msg = $this->ml('Unable to load #(1) #(2)', 'comments', 'renderer');
            throw new BadParameterException($msg);
        }


        if (!isset($header['selected_id']) || isset($thread)) {
            $package['comments'] = $this->mod()->apiMethod('comments', 'user', 'get_multiple', $header);
            if (count($package['comments']) > 1) {
                $package['comments'] = Renderer::array_sort(
                    $package['comments'],
                    $package['settings']['sortby'],
                    $package['settings']['order']
                );
            }
        } else {
            $header['id'] = $header['selected_id'];
            $package['settings']['render'] = Defines::VIEW_FLAT;
            $package['comments'] = $this->mod()->apiMethod('comments', 'user', 'get_one', $header);
            if (!empty($package['comments'][0])) {
                $header['modid'] = $package['comments'][0]['modid'];
                $header['itemtype'] = $package['comments'][0]['itemtype'];
                $header['objectid'] = $package['comments'][0]['objectid'];
            }
        }

        $package['comments'] = Renderer::array_prune_excessdepth(
            [
                'array_list'    => $package['comments'],
                'cutoff'        => $package['settings']['depth'],
                'modid'         => $header['modid'],
                'itemtype'      => $header['itemtype'],
                'objectid'      => $header['objectid'],
            ]
        );

        if ($package['settings']['render'] == Defines::VIEW_THREADED) {
            $package['comments'] = Renderer::array_maptree($package['comments']);
        }

        // run text and title through transform hooks
        if (!empty($package['comments'])) {
            foreach ($package['comments'] as $key => $comment) {
                $comment['text'] = $this->prep()->html($comment['text']);
                $comment['title'] = $this->prep()->text($comment['title']);
                // say which pieces of text (array keys) you want to be transformed
                $comment['transform'] = ['text'];
                // call the item transform hooks
                // Note : we need to tell Xaraya explicitly that we want to invoke the hooks for 'comments' here (last argument)
                $package['comments'][$key] = xarModHooks::call('item', 'transform', $comment['id'], $comment, 'comments');
            }
        }

        $header['input-title']            = $this->ml('Post a new comment');

        $package['settings']['max_depth'] = Defines::MAX_DEPTH;
        $package['role_id']               = $this->user()->getId();
        $package['uname']                 = $this->user()->getUser();
        $package['name']                  = $this->user()->getName();
        // Bug 6175: removed $this->prep()->text() from the title, as articles already
        // does this *but* maybe needs fixing in articles instead?
        $package['new_title']             = $this->mem()->get('Comments.title', 'title');

        // Let's honour the phpdoc entry at the top :-)
        /*if(isset($data['returnurl'])) {
            $receipt['returnurl']['raw'] = $data['returnurl'];
        }*/

        // get the title and link of the original object
        $modinfo = $this->mod()->getInfo($header['modid']);
        try {
            $itemlinks = $this->mod()->apiFunc(
                $modinfo['name'],
                'user',
                'getitemlinks',
                ['itemtype' => $header['itemtype'],
                'itemids' => [$header['objectid']]]
            );
        } catch (Exception $e) {
            $itemlinks = [];
        }

        if (!empty($itemlinks) && !empty($itemlinks[$header['objectid']])) {
            $url = $itemlinks[$header['objectid']]['url'];
            if (!strstr($url, '?')) {
                $url .= '?';
            }
            $header['objectlink'] = $itemlinks[$header['objectid']]['url'];
            $header['objecttitle'] = $itemlinks[$header['objectid']]['label'];
        } else {
            $url = $this->ctl()->getModuleURL($modinfo['name'], 'user', 'main');
        }

        /*$receipt['returnurl'] = array('encoded' => rawurlencode($url), 'decoded' => $url);*/

        $receipt['post_url']              = $this->mod()->getURL('user', 'reply');
        $receipt['action']                = 'display';

        $hooks = $this->mod()->apiMethod('comments', 'user', 'formhooks');

        //if (time() - ($package['comments']['xar_date'] - ($package['settings']['edittimelimit'] * 60))) {
        //}
        $data['hooks']   = $hooks;
        $data['header']  = $header;
        $data['package'] = $package;
        $data['receipt'] = $receipt;

        return parent::showInput($data);
    }
}
