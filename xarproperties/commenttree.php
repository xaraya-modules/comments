<?php
/**
 *
 * CommentTree Property
 *
 * @package Xaraya eXtensible Management System
 * @copyright (C) 2006 by to be added
 * @license GPL <http://www.gnu.org/licenses/gpl.html>
 * @link to be added
 * @subpackage Categories Module
 * @author Marc Lutolf <mfl@netspan.ch>
 *
 */

sys::import('modules.comments.class.comments');

class CommentTreeProperty extends DataProperty
{
    public $id         = 30058;
    public $name       = 'commenttree';
    public $desc       = 'CommentTree';
    public $reqmodules = ['comments'];

    public function __construct(ObjectDescriptor $descriptor)
    {
        parent::__construct($descriptor);

        $this->tplmodule = 'comments';
        $this->filepath   = 'modules/comments/xarproperties';
    }

    public function showInput(array $data = [])
    {
        if (isset($data['configuration'])) {
            $this->parseConfiguration($data['configuration']);
            unset($data['configuration']);
        }
        extract($data);
        if (!isset($module)) {
            $module = $this->mod()->getName();
        }
        if (!isset($itemtype)) {
            throw new BadParameterException('itemtype');
        }
        if (!isset($itemid)) {
            throw new BadParameterException('itemid');
        }

        $root = $this->mod()->apiMethod(
            'comments',
            'user',
            'get_node_root',
            ['modid' => $this->mod()->getID($module),
                              'objectid' => $itemid,
                              'itemtype' => $itemtype, ]
        );

        // If we don't have one, make one
        if (!count($root)) {
            $cid = $this->mod()->apiMethod(
                'comments',
                'user',
                'add_rootnode',
                ['modid'    => $this->mod()->getID($module),
                                        'objectid' => $itemid,
                                        'itemtype' => $itemtype, ]
            );
            if (empty($cid)) {
                throw new Exception('Unable to create root node');
            }
        }
        return $this->mod()->guiMethod(
            'comments',
            'user',
            'display',
            ['objectid' => $itemid,
                                       'module' => $module,
                                       'itemtype' => $itemtype,
                                       'returnurl' => $this->ctl()->getCurrentURL(), ]
        );
        /*if (isset($data['options'])) {
            $this->options = $data['options'];
        } else {
            $this->options = $this->mod()->apiFunc('categories','user','getchildren',array('id' => 0));
        }

        $trees = [];
        $totalcount = 0;
        foreach ($this->options as $entry) {
            $node = new CategoryTreeNode($entry['id']);
            $tree = new CategoryTree($node);
            $nodes = $node->depthfirstenumeration();
            $totalcount += $nodes->size();
            $trees[] = $nodes;
        }
        $data['trees'] = $trees;

        // Pager stuff, perhaps not good to have here
        $this->var()->check('pagerstart', $pagerstart);
        $this->var()->check('catsperpage', $catsperpage);
        if (empty($pagerstart)) {
            $data['pagerstart'] = 1;
        } else {
            $data['pagerstart'] = intval($pagerstart);
        }

        if (empty($catsperpage)) {
            $data['catsperpage'] = $this->mod('categories')->getVar('catsperpage');
        } else {
            $data['catsperpage'] = intval($catsperpage);
        }

        $data['pagertotal'] = $totalcount;
        return parent::showInput($data);
        */
    }
}
