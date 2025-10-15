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

namespace Xaraya\Modules\Comments\AdminGui;

use Xaraya\Modules\Comments\AdminGui;
use Xaraya\Modules\MethodClass;
use sys;

sys::import('xaraya.modules.method');
sys::import('modules.comments.defines');

/**
 * comments admin modifyconfig function
 * @extends MethodClass<AdminGui>
 */
class ModifyconfigMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * This is a standard function to modify and update the configuration parameters of the
     * module
     * @see AdminGui::modifyconfig()
     */
    public function __invoke(array $args = [])
    {
        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!$this->sec()->checkAccess('Admincomments')) {
            return;
        }

        // Check if this template has been submitted, or if we just got here
        $this->var()->find('phase', $phase, 'str:1:100', 'modify');

        // Load the DD master object class. This line will likely disappear in future versions
        sys::import('modules.dynamicdata.class.objects.factory');
        // Get the object we'll be working with for comments-specific configuration
        $object = $this->data()->getObject(['name' => 'comments_module_settings']);
        // Get the appropriate item of the dataobject. Using itemid 0 (not passing an itemid parameter) is standard convention
        $object->getItem(['itemid' => 0]);
        $data['object'] = $object;

        // Get the object we'll be working with for common configuration settings
        $data['module_settings'] = $this->mod()->apiFunc('base', 'admin', 'getmodulesettings', ['module' => 'comments']);
        // Decide which fields are configurable in this module
        $data['module_settings']->setFieldList('items_per_page, enable_user_menu, user_menu_link');
        // Get the appropriate item of the dataobject. Using itemid 0 (not passing an itemid parameter) is standard convention
        $data['module_settings']->getItem();

        // Run the appropriate code depending on whether the template was submitted or not
        switch (strtolower($phase)) {
            case 'modify':
            default:
                break;

            case 'update':
                # --------------------------------------------------------
                #
                # Confirm the authorisation code
                #
                # This is done to make sure this was submitted by a human to this module and not called programatically
                # However in this case we need to disable it because although the common configuration below is
                # handled in this module, the module-specific part at the end of the page is sent to be done by
                # the dynamicdata module, where the same check is done. Since both checks cannot simultaneously
                # be passed, (the act of checking resets the check) the one below is disabled in this example.
                #
                //if (!$this->sec()->confirmAuthKey()) return;

                # --------------------------------------------------------
                #
                # Updating the common configuration
                #
                # We do this before anything else, because the checkInput error checking might
                # decide something is not right and redisplay the form. In such a case we don't want to
                # already have saved data. The module-specific code active at the bottom of the page does no
                # such error checking. This is not really a problem because you can't really get input errors
                # when you're dealing with checkboxes (they're either checked or they aren't)
                #

                $isvalid = $data['module_settings']->checkInput();
                if (!$isvalid) {
                    return $this->mod()->template('modifyconfig', $data);
                } else {
                    $itemid = $data['module_settings']->updateItem();
                }

                # --------------------------------------------------------
                #
                # Updating the comments configuration without using DD
                #
                # In this case we get each value from the template and set the appropriate modvar
                # Note that in this case we are setting modvars, whereas in the other two ways below we are actually
                # setting moditemvars with the itemid = 1
                # This can work because in the absence of a moditemvar the corresponding modvar is returned
                # What we cannot do however is mix these methods, because once we have a moditemvar defined, we can
                # no longer default back to the modvar (unless called specifically as below).
                #
                /*
                    // Get parameters from whatever input we need.  All arguments to this
                    // function should be obtained from $this->var()->fetch(), getting them
                    // from other places such as the environment is not allowed, as that makes
                    // assumptions that will not hold in future versions of Xaraya
                    $this->var()->find('bold', $bold, 'checkbox', false);

                    // Confirm authorisation code.  This checks that the form had a valid
                    // authorisation code attached to it.  If it did not then the function will
                    // proceed no further as it is possible that this is an attempt at sending
                    // in false data to the system
                    if (!$this->sec()->confirmAuthKey()) return;

                    $this->mod()->setVar('bold', $bold);
                */

                # --------------------------------------------------------
                #
                # Updating the comments configuration with DD class calls
                #
                # This is the same as the examples of creating, modifying or deleting an item
                # Note that, as in those examples, this code could be placed in the modifyconfig.php file
                # and this file dispensed with.
                #

                // Load the DD master object class. This line will likely disappear in future versions
                sys::import('modules.dynamicdata.class.objects.factory');
                // Get the object we'll be working with
                $object = $this->data()->getObject(['name' => 'comments_module_settings']);
                // Get the data from the form
                $isvalid = $object->checkInput();
                // Update the item with itemid = 0


                $item = $object->updateItem(['itemid' => 0]);

                $this->ctl()->redirect($this->mod()->getURL('admin', 'modifyconfig'));

                # --------------------------------------------------------
                #
                # Updating the comments configuration with a DD API call
                #
                # This is a special case using the dynamicdata_admin_update function.
                # It depends on finding all the relevant information on the template we are submitting, i.e.
                # - objectid of the comments_module_settings object
                # - itemid (1 in this case)
                # - returnurl, telling us where to jump to after update
                #
                # This needs to be the last thing happening on this page because it redirects. Code below
                # this point will not execute

                //if (!$admingui->update()) {
                //    return;
                //}

                break;
        }

        // Return the template variables defined in this function
        return $data;
    }
}
