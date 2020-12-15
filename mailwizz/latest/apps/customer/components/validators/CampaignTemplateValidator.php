<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignTemplateValidator
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class CampaignTemplateValidator extends CValidator
{
    protected function validateAttribute($object, $attribute)
    {
        // extract the attribute value from it's model object
        $value = $object->$attribute;
        if ($object->scenario == 'copy') {
            return;
        }
        
        if ($object->hasErrors($attribute)) {
            return;
        }
        
        $tags = $object->getAvailableTags();
        $missingTags = array();
        
        foreach ($tags as $tag) {
            
            if (!isset($tag['tag']) || !isset($tag['required']) || !$tag['required']) {
                continue;
            }
    
            if (!isset($tag['pattern']) && strpos($value, $tag['tag']) === false) {
                // since 1.3.6.3
                $isMissing = true;
                if (!empty($tag['alt_tags_if_tag_required_and_missing']) && is_array($tag['alt_tags_if_tag_required_and_missing'])) {
                    foreach ($tag['alt_tags_if_tag_required_and_missing'] as $altTag) {
                        if (strpos($value, $altTag) !== false) {
                            $isMissing = false;
                            break;
                        }
                    }
                }
                //
                if ($isMissing) {
                    $missingTags[] = $tag['tag'];
                }
            } elseif (isset($tag['pattern']) && !preg_match($tag['pattern'], $value)) {
                $missingTags[] = $tag['tag'];
            }
        }
        
        if (!empty($missingTags)) {
            $missingTags = array_unique($missingTags);
            $this->addError($object, $attribute, Yii::t('campaigns', 'The following tags are required but were not found in your content: {tags}', array(
                '{tags}' => implode(', ', $missingTags),
            )));
        }
        
        if ($object->hasErrors($attribute)) {
            return;
        }
    }
}