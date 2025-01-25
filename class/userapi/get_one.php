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


use Xaraya\Modules\Comments\UserApi;
use Xaraya\Modules\Comments\Defines;
use Xaraya\Modules\Comments\Renderer;
use Xaraya\Modules\MethodClass;
use xarDB;
use xarUser;
use xarMod;
use xarLocale;
use DataObjectFactory;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * comments userapi get_one function
 * @extends MethodClass<UserApi>
 */
class GetOneMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Get a single comment.
     * @author Carl P. Corliss (aka rabbitt)
     * @access public
     * @param array<mixed> $args
     * @var int $id       the id of a comment
     * @return array an array containing the sole comment that was requested
     * or an empty array if no comment found
     * @see UserApi::getOne()
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (!isset($id) || empty($id)) {
            $msg = $this->ml(
                'Missing or Invalid argument [#(1)] for #(2) function #(3) in module #(4)',
                'id',
                'userapi',
                'get_one',
                'comments'
            );
            throw new Exception($msg);
        }

        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();

        // initialize the commentlist array
        $commentlist = [];

        sys::import('modules.dynamicdata.class.objects.factory');
        $object = $this->data()->getObject(['name' => 'comments_comments']);
        $object->getItem(['itemid' => $id]);
        $values = $object->getFieldValues();
        $values['position_atomic'] = $object->properties['position']->atomic_value;

        if ($values['status'] != Defines::STATUS_ON) {
            return [];
        }

        $values['postanon'] = $values['anonpost'];
        $values['datetime'] = $values['date'];
        $values['role_id'] = $values['author'];
        //Renderer::wrap_words($values['text'],80);
        //    $values['author'] = xarUser::getVar('name',$values['author']);

        $arr[0] = $values;
        $values = $arr;

        // if the depth is zero then we
        // only want one comment
        /* $sql = "SELECT  title AS title,
                         date AS datetime,
                         hostname AS hostname,
                         text AS text,
                         author AS author,
                         author AS role_id,
                         id AS id,
                         parent_id AS parent_id,
                         status AS status,
                         left_id AS left_id,
                         right_id AS right_id,
                         anonpost AS postanon,
                         moduleid AS moduleid,
                         itemtype AS itemtype,
                         objectid AS objectid
                   FROM  $xartable[comments]
                  WHERE  id=$id
                    AND  status=".Defines::STATUS_ON;

         $result =& $dbconn->Execute($sql);
         if(!$result) return;

         // if we have nothing to return
         // we return nothing ;) duh? lol
         if ($result->EOF) {
             return array();
         }*/

        if (!xarMod::load('comments', 'renderer')) {
            $msg = $this->ml('Unable to load #(1) #(2) - unable to trim excess depth', 'comments', 'renderer');
            throw new Exception($msg);
        }

        // zip through the list of results and
        // add it to the array we will return
        /*while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            // FIXME delete after date output testing
            // $row['date'] = xarLocale::formatDate("%B %d, %Y %I:%M %p",$row['datetime']);
            $row['date'] = $row['datetime'];
            $row['author'] = xarUser::getVar('name',$row['author']);
            Renderer::wrap_words($row['text'],80);
            $commentlist[] = $row;
            $result->MoveNext();
        }

        $result->Close();
        */

        if (!Renderer::array_markdepths_bypid($values)) {
            $msg = $this->ml('Unable to add depth field to comments!');
            throw new Exception($msg);
            // FIXME: <rabbitt> this stuff should really be moved out of the comments
            //        module into a "rendering" module of some sort anyway -- or (god forbid) a widget.
        }

        return $values;
    }
}
