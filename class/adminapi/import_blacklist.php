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
use xarDB;
use xarMod;
use xarTableDDL;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * comments adminapi import_blacklist function
 * @extends MethodClass<AdminApi>
 */
class ImportBlacklistMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Imports Current Blacklist
     *  left/right values
     *  @author John Cox
     * @access public
     * @return bool|null true on success
     */
    public function __invoke(array $args = [])
    {
        extract($args);
        sys::import('xaraya.tableddl');
        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();
        $btable = $xartable['blacklist'];
        $bbtable = &$xartable['blacklist_column'];
        $feedfile = 'http://www.jayallen.org/comment_spam/blacklist.txt';

        // Get the feed file (from cache or from the remote site)
        $filegrab = xarMod::apiFunc(
            'base',
            'user',
            'getfile',
            ['url' => $feedfile,
                'cached' => true,
                'cachedir' => 'cache/rss',
                'refresh' => 604800,
                'extension' => '.txt', ]
        );
        if (!$filegrab) {
            $msg = $this->ml('Could not get new blacklist file.');
            throw new BadParameterException($msg);
        }

        // Kinda hackish here.  No empty table command that I can find.

        $query = xarTableDDL::dropTable($xartable['blacklist']);
        $result = & $dbconn->Execute($query);

        if (!$result) {
            return;
        }


        // Create blacklist tables
        $fields = [
            'id' => ['type' => 'integer', 'unsigned' => true, 'null' => false, 'increment' => true, 'primary_key' => true],
            //        'id'       => array('type'=>'integer',  'null'=>FALSE,  'increment'=> TRUE, 'primary_key'=>TRUE),
            'domain'   => ['type' => 'varchar',  'null' => false,  'size' => 255],
        ];

        $query = xarTableDDL::createTable($xartable['blacklist'], $fields);
        $file = file('var/cache/rss/' . md5($feedfile) . '.txt');
        $result = & $dbconn->Execute($query);
        if (!$result) {
            return;
        }
        for ($i = 0; $i < count($file); $i++) {
            $data = $file[$i];
            $domain = "";
            for ($j = 0; $j < strlen($data); $j++) {
                if ($data[$j] == " " || $data[$j] == "#") {
                    break;
                } else {
                    $domain .= $data[$j];
                    continue;
                }
            }
            if (strpos($domain, '[\w\-_.]')) {
                $domaim = str_replace('[\w\-_.]', '[-\w\_.]', $domain);
            }
            $ps = strpos($domain, '/');
            while ($ps !== false) {
                if ($ps == 0) {
                    $domain = '\\' + $domain;
                } elseif (substr($domain, $ps - 1, 1) != '\\') {
                    $domain = substr_replace($domain, '\/', $ps, 1);
                }
                $ps = strpos($domain, '/', $ps + 2);
            }
            $domain = trim($domain);
            if ($domain != "") {
                $nextId = $dbconn->GenId($btable);
                $query = "INSERT INTO $btable(id,
                                              domain)
                          VALUES (?,?)";
                $bindvars = [$nextId, $domain];
                $result = & $dbconn->Execute($query, $bindvars);
                if (!$result) {
                    return;
                }
            }
        }
        return true;
    }
}
