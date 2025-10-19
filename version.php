<?php

/**
 * Comments Module
 *
 * @package modules
 * @subpackage comments
 * @category Third Party Xaraya Module
 * @version 2.4.0
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://xaraya.com/index.php/release/14.html
 * @author Carl P. Corliss <rabbitt@xaraya.com>
 */

/**
 * Module Information
 */

namespace Xaraya\Modules\Comments;

class Version
{
    /**
     * Get module version information
     *
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        return [
            'name' => 'comments',
            'id' => '14',
            'version' => '2.4.0',
            'displayname' => 'Comments',
            'description' => 'Allows users to post comments on items',
            'credits' => 'xardocs/credits.txt',
            'help' => '',
            'changelog' => '',
            'license' => '',
            'official' => 1,
            'author' => 'Carl P. Corliss (aka Rabbitt)',
            'contact' => 'rabbitt@xaraya.com',
            'admin' => 1,
            'user' => 0,
            'class' => 'Utility',
            'category' => 'Content',
            'namespace' => 'Xaraya\\Modules\\Comments',
            'twigtemplates' => true,
            'dependencyinfo'
             => [
                 0
                  => [
                      'name' => 'Xaraya Core',
                      'version_ge' => '2.4.1',
                  ],
             ],
        ];
    }
}
