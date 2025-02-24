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
use xarRoles;
use xarMod;
use xarModHooks;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments user search function
 * @extends MethodClass<UserGui>
 */
class SearchMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Searches all -active- comments based on a set criteria
     * @author Carl P. Corliss (aka rabbitt)
     * @access private
     * @return mixed description of return
     * @see UserGui::search()
     */
    public function __invoke(array $args = [])
    {
        /** @var UserApi $userapi */
        $userapi = $this->userapi();
        $this->var()->check('startnum', $startnum);
        $this->var()->check('header', $header);
        $this->var()->check('q', $q);
        $this->var()->check('bool', $bool);
        $this->var()->check('sort', $sort);
        $this->var()->check('author', $author);

        $postinfo   = ['q' => $q, 'author' => $author];
        $data       = [];
        $search     = [];

        // TODO:  check 'q' and 'author' for '%' value
        //        and sterilize if found
        if (!isset($q) || strlen(trim($q)) <= 0) {
            if (isset($author) && strlen(trim($author)) > 0) {
                $q = $author;
            } else {
                $data['header']['text']     = 1;
                $data['header']['title']    = 1;
                $data['header']['author']   = 1;
                return $data;
            }
        }

        $q = "%$q%";

        // Default parameters
        if (!isset($startnum)) {
            $startnum = 1;
        }
        if (!isset($numitems)) {
            $numitems = 20;
        }

        if (isset($header['title'])) {
            $search['title'] = $q;
            $postinfo['header[title]'] = 1;
            $header['title'] = 1;
        } else {
            $header['title'] = 0;
            $postinfo['header[title]'] = 0;
        }

        if (isset($header['text'])) {
            $search['text'] = $q;
            $postinfo['header[text]'] = 1;
            $header['text'] = 1;
        } else {
            $header['text'] = 0;
            $postinfo['header[text]'] = 0;
        }

        if (isset($header['author'])) {
            $postinfo['header[author]'] = 1;
            $header['author'] = 1;
            $user = xarRoles::ufindRole($author);

            $search['role_id'] = $user;
            $search['author'] = $author;
        } else {
            $postinfo['header[author]'] = 0;
            $header['author'] = 0;
        }

        $package['comments'] = $userapi->search($search);

        if (!empty($package['comments'])) {
            foreach ($package['comments'] as $key => $comment) {
                if ($header['text']) {
                    // say which pieces of text (array keys) you want to be transformed
                    $comment['transform'] = ['text'];
                    // call the item transform hooks
                    // Note : we need to tell Xaraya explicitly that we want to invoke the hooks for 'comments' here (last argument)
                    $comment = xarModHooks::call('item', 'transform', $comment['id'], $comment, 'comments');
                    // Index appears to be empty on the transform.  Is this line needed?
                    //$package['comments'][$key]['text'] = $this->var()->prepHTML($comment['text']);
                }
                if ($header['title']) {
                    $package['comments'][$key]['title'] = $this->var()->prep($comment['title']);
                }
            }

            $header['modid'] = $package['comments'][0]['modid'];
            $header['itemtype'] = $package['comments'][0]['itemtype'];
            $header['objectid'] = $package['comments'][0]['objectid'];

            $receipt['directurl'] = true;

            $data['package'] = $package;
            $data['receipt'] = $receipt;
        }

        if (!isset($data['package'])) {
            $data['receipt']['status'] = $this->ml('No Comments Found Matching Search');
        }

        $data['header'] = $header;
        return $data;
    }
}
