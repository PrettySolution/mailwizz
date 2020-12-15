<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CustomerGroupOptionSending
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.3
 */
 
class CustomerGroupOptionSending extends OptionCustomerSending
{
    public function behaviors()
    {
        $behaviors = array(
            'handler' => array(
                'class'         => 'backend.components.behaviors.CustomerGroupModelHandlerBehavior',
                'categoryName'  => $this->_categoryName,
            ),
        );
        return CMap::mergeArray($behaviors, parent::behaviors());
    }

    public function save()
    {
        return $this->asa('handler')->save();
    }
    
    public function getGroupsList()
    {
        $groups = parent::getGroupsList();
        if ($group = $this->asa('handler')->getGroup()) {
            foreach ($groups as $groupId => $name) {
                if ($groupId == $group->group_id) {
                    unset($groups[$groupId]);
                    break;
                }
            }    
        }
        return $groups;
    }
}
