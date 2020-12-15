<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignHelper
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class CampaignHelper
{
	/**
	 * CampaignHelper::parseContent()
	 *
	 * This should be always connected with the CampaignTemplate model class::getAvailableTags().
	 * Will parse the content tags and transform them
	 *
	 * It is used in:
	 * console/components/behaviors/CampaignSenderBehavior.php
	 * frontend/controllers/CampaignsController.php
	 * 
	 * @param $content
	 * @param Campaign $campaign
	 * @param ListSubscriber $subscriber
	 * @param bool $appendBeacon
	 * @param DeliveryServer|null $server
	 *
	 * @return array
	 * @throws CException
	 */
    public static function parseContent($content, Campaign $campaign, ListSubscriber $subscriber, $appendBeacon = false, DeliveryServer $server = null)
    {
        $content = StringHelper::decodeSurroundingTags($content);
        $options = Yii::app()->options;

        $searchReplace = self::getCommonTagsSearchReplace($content, $campaign, $subscriber, $server);
        $content       = str_replace(array_keys($searchReplace), array_values($searchReplace), $content);
        $content       = self::getTagFilter()->apply($content, $searchReplace);

        $to      = isset($searchReplace['[CAMPAIGN_TO_NAME]']) ? $searchReplace['[CAMPAIGN_TO_NAME]'] : '';
        $subject = isset($searchReplace['[CAMPAIGN_SUBJECT]']) ? $searchReplace['[CAMPAIGN_SUBJECT]'] : '';

        // tags with params, if any...
        $searchReplace  = array();
        if (preg_match_all('/\[([a-z_]+)([^\]]+)?\]/i', $content, $matches)) {
            $matches = array_unique($matches[0]);
            foreach ($matches as $tag) {
                if (strpos($tag, '[DATETIME') === 0) {
                    $searchReplace[$tag] = self::parseDateTimeTag($tag);
                } elseif (strpos($tag, '[DATE') === 0) {
                    $searchReplace[$tag] = self::parseDateTag($tag);
                }
            }
        }

        /**
         * This is where we replace the markers from CampaignHelper::transformLinksForTracking() 
         * This is the only place to replace the markers that won't affect the performance 
         * 
         * @since 1.4.3
         * @see CampaignHelper::transformLinksForTracking()
         */
        if (!empty($server)) {
            if ($server->type == 'elasticemail-web-api' || preg_match('/smtp(\d+)?\.elasticemail\.com/i', $server->hostname)) {
                $unsubscribeTags = array('_UNSUBSCRIBE_URL_', '_DIRECT_UNSUBSCRIBE_URL_', '_UNSUBSCRIBE_FROM_CUSTOMER_URL_');
                foreach ($unsubscribeTags as $unsubscribeTag) {
                    $pattern = sprintf('/data-unsubtag="%s" href(\s+)?=(\s+)?(\042|\047)((\s+)?(.*?)(\s+)?)(\042|\047)/i', $unsubscribeTag);
                    if (!preg_match_all($pattern, $content, $matches)) {
                        continue;
                    }
                    $pattern = '/href(\s+)?=(\s+)?(\042|\047)((\s+)?(.*?)(\s+)?)(\042|\047)/i';
                    $markup  = array_unique($matches[0]);
                    foreach ($markup as $mkp) {
                        $_mkp = str_replace(sprintf('data-unsubtag="%s"', $unsubscribeTag), '', $mkp);
                        $_mkp = trim($_mkp);
                        $_mkp = preg_replace($pattern, 'href="{unsubscribe:$6}"', $_mkp);
                        $searchReplace[$mkp] = $_mkp;
                    }
                }
            }
        }
        //
        
        if (!empty($searchReplace)) {
            $content = str_replace(array_keys($searchReplace), array_values($searchReplace), $content);
        }
        
        // 1.4.4
        if (!empty($subject)) {
            $searchReplace = self::getCommonTagsSearchReplace($subject, $campaign, $subscriber, $server);
            if (!empty($searchReplace)) {
                $subject = str_replace(array_keys($searchReplace), array_values($searchReplace), $subject);
            }
        }
        //
        
        unset($searchReplace);

        if ($appendBeacon && !empty($subscriber->subscriber_id)) {
            $beaconUrl = $options->get('system.urls.frontend_absolute_url');
            $beaconUrl .= 'campaigns/' . $campaign->campaign_uid . '/track-opening/' . $subscriber->subscriber_uid;
            $beaconImage = CHtml::image($beaconUrl, '', array('width' => 1, 'height' => 1));
            $content = str_ireplace('</body>', $beaconImage . "\n" . '</body>', $content);
        }
        
        return array($to, $subject, $content);
    }

	/**
	 * @param $content
	 * @param array $templateVariables
	 *
	 * @return false|string|null
	 * @throws Throwable
	 */
    public static function parseByTemplateEngine($content, array $templateVariables = array())
    {
        // twig requires php >= 5.2.7
        if (version_compare(PHP_VERSION, '5.2.7', '<')) {
            return $content;
        }
 
        try {
        	
            $data = array();
            foreach ($templateVariables as $key => $value) {
                $data[ str_replace(array('[', ']'), '', $key) ] = $value;
            }
            $twig = TwigHelper::getInstance();

	        // 1.6.9 - hidden chars cleanup
	        $specialCharsMap = array(
		        chr(194) . chr(160) => ' ', // hidden \u00a0 
	        );
	        $twigContent = str_replace(array_keys($specialCharsMap), array_values($specialCharsMap), $content);
	        //
	        
            $template = $twig->createTemplate($twigContent);
            $_content = $template->render($data);
        } catch (Exception $e) {
        	Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            $_content = null;
        }
        
        return $_content ? $_content : $content;
    }

    /**
     * CampaignHelper::isTemplateEngineEnabled()
     * 
     * @return bool
     */
    public static function isTemplateEngineEnabled()
    {
        static $enabled;
        if ($enabled !== null) {
            return $enabled;
        }
        if (version_compare(PHP_VERSION, '5.2.7', '<')) {
            return $enabled = false;
        }
        return $enabled = Yii::app()->options->get('system.campaign.template_engine.enabled', 'no') == 'yes';
    }

    /**
     * CampaignHelper::transformLinksForTracking()
     * The $canSave param is not used anymore, but kept for compatibility for now.
     * 
     * @param $content
     * @param Campaign $campaign
     * @param ListSubscriber $subscriber
     * @param bool $canSave
     * @param bool $isPlainText
     * @return mixed|string
     * @throws Exception
     */
    public static function transformLinksForTracking($content, Campaign $campaign, ListSubscriber $subscriber, $canSave = false, $isPlainText = false)
    {
        static $trackingUrls = array();
        static $trackingUrlsSaved = array();

        $content  = StringHelper::decodeSurroundingTags($content);
        $content  = StringHelper::normalizeUrlsInContent($content);
        $list     = $campaign->list;
        $cacheKey = sha1('tracking_urls_for_' . $campaign->campaign_uid . '_' . $content);
        
        // first try
        if (($_content = Yii::app()->cache->get($cacheKey)) !== false) {
            return $_content;
        }

        // this can take a while
        if (!Yii::app()->mutex->acquire($cacheKey, 120)) {
            
            // in case it has been written in these 120 seconds interval by a parallel process
            if (($_content = Yii::app()->cache->get($cacheKey)) !== false) {
                return $_content;
            }
            
            return $content;
        }

        // meanwhile might have been set by a parallel process
        if (($_content = Yii::app()->cache->get($cacheKey)) !== false) {

            // release mutex
            Yii::app()->mutex->release($cacheKey);
            
            return $_content;
        }
        
        // since 1.3.5.9
        Yii::app()->hooks->doAction('campaign_content_before_transform_links_for_tracking', $collection = new CAttributeCollection(array(
            'content'       => $content,
            'campaign'      => $campaign,
            'subscriber'    => $subscriber,
            'list'          => $list,
            'trackingUrls'  => $trackingUrls,
            'cacheKey'      => $cacheKey,
        )));
        $content      = $collection->content;
        $trackingUrls = $collection->trackingUrls;
        
        if (!isset($trackingUrls[$cacheKey])) {
            
            $trackingUrls[$cacheKey] = array();
            $baseUrl                 = Yii::app()->options->get('system.urls.frontend_absolute_url');
            $trackingUrl             = $baseUrl . 'campaigns/[CAMPAIGN_UID]/track-url/[SUBSCRIBER_UID]';

            /**
             * Since the template is cached, we need to add some markers that we will recognize later
             * as the unubscribe urls to replace them with proper tags.
             * These markers will be later replaced in CampaignHelper::parseContent()
             * 
             * We're doing this mainly for ElasticEmail because otherwise it inserts it's own {unsubscribe} tag
             * where it wants.
             * 
             * @since 1.4.3
             * @see CampaignHelper::parseContent()
             */
            if (!$isPlainText) {
                $unsubscribeTags = array('UNSUBSCRIBE_URL', 'DIRECT_UNSUBSCRIBE_URL', 'UNSUBSCRIBE_FROM_CUSTOMER_URL');
                foreach ($unsubscribeTags as $unsubscribeTag) {
                    $unsubSearchReplace = array(
                        sprintf('href="[%s]"', $unsubscribeTag) => sprintf('data-unsubtag="_%1$s_" href="[%1$s]"', $unsubscribeTag), 
                        sprintf("href='[%s]'", $unsubscribeTag) => sprintf('data-unsubtag="_%1$s_" href="[%1$s]"', $unsubscribeTag)
                    );
                    $content = str_replace(array_keys($unsubSearchReplace), array_values($unsubSearchReplace), $content);
                }
            }
            //
            
            if (!$isPlainText) {
                // (\042|\047) are octal quotes.
                $pattern = '/href(\s+)?=(\s+)?(\042|\047)(\s+)?(.*?)(\s+)?(\042|\047)/i';
            } else {
                $pattern = '/https?:\/\/([^\s]+)/im';
            }
            
            if (!preg_match_all($pattern, $content, $matches)) {

                // cache content
                Yii::app()->cache->set($cacheKey, $content);
                
                // release mutex
                Yii::app()->mutex->release($cacheKey);
                
                return $content;
            }
            
            if (!$isPlainText) {
                $urls = $matches[5];
            } else {
                $urls = $matches[0];
            }
            $urls = array_map('trim', $urls);
            
            // combine url with markup
            $urls = array_combine($urls, $matches[0]);
            $foundUrls = array();

            foreach ($urls as $url => $markup) {
                
                // since 1.3.6.3
                $url = StringHelper::normalizeUrl($url);

                // external url which may contain one or more tags(sharing maybe?)
	            if (preg_match('/https?.*/i', $url, $matches) && FilterVarHelper::url($url)) {
		            $_url = trim($matches[0]);
		            $foundUrls[$_url] = $markup;
		            continue;
	            }

	            // since 1.7.8
	            if (preg_match('/tel:(.*)/i', $url, $matches) && FilterVarHelper::phoneUrl($url)) {
		            $_url = trim($matches[0]);
		            $foundUrls[$_url] = $markup;
		            continue;
	            }

	            // since 1.7.8
	            if (preg_match('/mailto:(.*)/i', $url, $matches) && FilterVarHelper::mailtoUrl($url)) {
		            $_url = trim($matches[0]);
		            $foundUrls[$_url] = $markup;
		            continue;
	            }

                // local tag to be transformed
                if (preg_match('/^\[([A-Z0-9:_]+)_URL\]$/i', $url, $matches)) {
                    $_url = trim($matches[0]);
                    $foundUrls[$_url] = $markup;
                    continue;
                }
            }

            if (empty($foundUrls)) {
                
                // since 1.3.5.9
                Yii::app()->hooks->doAction('campaign_content_after_transform_links_for_tracking', $collection = new CAttributeCollection(array(
                    'content'      => $content,
                    'campaign'     => $campaign,
                    'subscriber'   => $subscriber,
                    'list'         => $list,
                    'trackingUrls' => $trackingUrls,
                    'cacheKey'     => $cacheKey,
                )));
                $content      = $collection->content;
                $trackingUrls = $collection->trackingUrls;

                // cache content
                Yii::app()->cache->set($cacheKey, $content);

                // release mutex
                Yii::app()->mutex->release($cacheKey);
                
                return $content;
            }

            $prefix = $campaign->campaign_uid;
            $sort   = array();

            foreach ($foundUrls as $url => $markup) {

                $urlHash = sha1($prefix . $url);
                $track   = $trackingUrl . '/' . $urlHash;
                $length  = strlen($url);

                $trackingUrls[$cacheKey][] = array(
                    'url'       => $url,
                    'hash'      => $urlHash,
                    'track'     => $track,
                    'length'    => $length,
                    'markup'    => $markup,
                );

                $sort[] = $length;
            }

            unset($foundUrls);
            
            // make sure we order by the longest url to the shortest
            array_multisort($sort, SORT_DESC, SORT_NUMERIC, $trackingUrls[$cacheKey]);
        }
        
        if (!empty($trackingUrls[$cacheKey])) {

            $searchReplace = array();
            foreach ($trackingUrls[$cacheKey] as $urlData) {
                if (!$isPlainText) {
                    $searchReplace[$urlData['markup']] = 'href="'.$urlData['track'].'"';
                } else {
                    $searchReplace[$urlData['markup']] = $urlData['track'];
                }
            }
            
            $content = str_replace(array_keys($searchReplace), array_values($searchReplace), $content);
            
            // put back link hrefs
            $searchReplace = array();
            foreach ($trackingUrls[$cacheKey] as $urlData) {
                $searchReplace['link href="' . $urlData['track'] . '"'] = 'link href="'.$urlData['url'].'"';
            }
            $content = str_replace(array_keys($searchReplace), array_values($searchReplace), $content);
            
            unset($searchReplace);

            // save the url tags.
            $insertModels = array();
            foreach ($trackingUrls[$cacheKey] as $urlData) {

                $hash = $urlData['hash'];
                $key  = sha1($cacheKey . $hash);
                if (isset($trackingUrlsSaved[$key])) {
                    continue;
                }
                $trackingUrlsSaved[$key] = true;

                if (isset($insertModels[$hash])) {
                    continue;
                }

                $urlModel = CampaignUrl::model()->countByAttributes(array(
                    'campaign_id' => (int)$campaign->campaign_id,
                    'hash'        => $hash,
                ));

                if (!empty($urlModel)) {
                    continue;
                }

                $insertModels[$hash] = array(
                    'campaign_id' => $campaign->campaign_id,
                    'destination' => $urlData['url'],
                    'hash'        => $hash,
                    'date_added'  => new CDbExpression('NOW()'),
                );
            }

            if (!empty($insertModels)) {

                try {

                    // drop the keys
                    $insertModels = array_values($insertModels);

                    $schema    = Yii::app()->getDb()->getSchema();
                    $tableName = CampaignUrl::model()->tableName();
                    $schema->getCommandBuilder()->createMultipleInsertCommand($tableName, $insertModels)->execute();

                } catch (Exception $e) {

                    // delete the cache, if any
                    Yii::app()->cache->delete($cacheKey);

                    // release mutex
                    Yii::app()->mutex->release($cacheKey);

                    throw new Exception('Unable to save the tracking urls!');
                }
            }
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('campaign_content_after_transform_links_for_tracking', $collection = new CAttributeCollection(array(
            'content'      => $content,
            'campaign'     => $campaign,
            'subscriber'   => $subscriber,
            'list'         => $list,
            'trackingUrls' => $trackingUrls,
            'cacheKey'     => $cacheKey,
        )));
        $content      = $collection->content;
        $trackingUrls = $collection->trackingUrls;

        // cache content
        Yii::app()->cache->set($cacheKey, $content);
        
        // release mutex
        Yii::app()->mutex->release($cacheKey);
        
        // return transformed
        return $content;
    }

    /**
     * CampaignHelper::embedContentImages()
     *
     * @param string $content
     * @param Campaign $campaign
     * @return mixed
     */
    public static function embedContentImages($content, Campaign $campaign)
    {
        if (empty($content)) {
            return array($content, array());
        }

        static $parsed = array();
        $key = sha1($campaign->campaign_uid . $content);

        if (isset($parsed[$key]) || array_key_exists($key, $parsed)) {
            return $parsed[$key];
        }
        
        if (!CommonHelper::functionExists('qp')) {
            require_once(Yii::getPathOfAlias('common.vendors.QueryPath.src.QueryPath') . '/QueryPath.php');
        }

        $embedImages = array();
        $storagePath = Yii::getPathOfAlias('root.frontend.assets');
        $extensions  = array('jpg', 'jpeg', 'png', 'gif');

        libxml_use_internal_errors(true);

        try {

            $query = qp($content, 'body', array(
                'ignore_parser_warnings'    => true,
                'convert_to_encoding'       => Yii::app()->charset,
                'convert_from_encoding'     => Yii::app()->charset,
                'use_parser'                => 'html',
            ));

            // to do: what action should we take here?
            if (count(libxml_get_errors()) > 0) {}

            $images = $query->top()->find('img');

            if (empty($images) || !is_object($images) || $images->length == 0) {
                throw new Exception('No images found!');
            }

            foreach ($images as $image) {
                $src = urldecode($image->attr('src'));
                $src = str_replace(array('../', './', '..\\', '.\\', '..'), '', trim($src));

                if (empty($src)) {
                    continue;
                }

                $ext = pathinfo($src, PATHINFO_EXTENSION);
                if (empty($ext) || !in_array(strtolower($ext), $extensions)) {
                    continue;
                }
                unset($ext);

                if (preg_match('/\/frontend\/assets(\/gallery\/([a-zA-Z0-9]{13,})\/.*)/', $src, $matches)) {
                    $src = $matches[1];
                } elseif (preg_match('/\/frontend\/assets(\/files\/(customer|user)\/([a-zA-Z0-9]{13,})\/.*)/', $src, $matches)) {
                    $src = $matches[1];
                }

                if (preg_match('/^https?/i', $src)) {
                    continue;
                }

                $fullFilePath = $storagePath . '/' . $src;
                if (!is_file($fullFilePath)) {
                    continue;
                }

                $imageInfo = @getimagesize($fullFilePath);
                if (empty($imageInfo[0]) || empty($imageInfo[1]) || empty($imageInfo['mime'])) {
                    continue;
                }

                $cid = sha1($fullFilePath);
                $embedImages[] = array(
                    'name'  => basename($fullFilePath),
                    'path'  => $fullFilePath,
                    'cid'   => $cid,
                    'mime'  => $imageInfo['mime'],
                );

                $image->attr('src', 'cid:' . $cid);
                unset($fullFilePath, $cid, $imageInfo);
            }

            $content = $query->top()->html();
            unset($query, $images);

        } catch (Exception $e) {}

        libxml_use_internal_errors(false);
        return $parsed[$key] = array($content, $embedImages);
    }

    /**
     * CampaignHelper::htmlToText()
     *
     * @param string $content
     * @return string
     */
    public static function htmlToText($content)
    {
        static $html2text;

        if ($html2text === null) {
            Yii::import('common.vendors.Html2Text.*');
            $html2text = new Html2Text();

            if (!MW_IS_CLI) {
                $appName = Yii::app()->apps->getCurrentAppName();
                $options = Yii::app()->options;
                $html2text->set_base_url($options->get('system.urls.'.$appName.'_absolute_url'));
            }
        }

        $html2text->set_html($content);

        $text = $html2text->get_text();

        $lines = explode("\n", $text);
        $lines = array_filter(array_map('trim', $lines));
        $text  = implode("\n", $lines);
        
        return $text;
    }

    /**
     * @param $content
     * @return array|mixed
     */
    public static function extractTemplateUrls($content)
    {
        if (empty($content)) {
            return array();
        }

        static $urls = array();
        $hash = sha1($content);

        if (array_key_exists($hash, $urls)) {
            return $urls[$hash];
        }

        $urls[$hash] = array();

        // (\042|\047) are octal quotes.
        $pattern = '/href(\s+)?=(\s+)?(\042|\047)(\s+)?(.*?)(\s+)?(\042|\047)/i';
        if (!preg_match_all($pattern, $content, $matches)) {
            return $urls[$hash];
        }

        if (empty($matches[5])) {
            return $urls[$hash];
        }

        $urls[$hash] = array_unique(array_map(array('CHtml', 'decode'), array_map('trim', $matches[5])));

        // remove tag urls
        foreach ($urls[$hash] as $index => $url) {
            if (empty($url) || (strpos($url, '[') !== 0 && !FilterVarHelper::url($url))) {
                unset($urls[$hash][$index]);
            }
        }

        sort($urls[$hash]);

        return $urls[$hash];
    }

    /**
     * @param $listId
     * @return mixed
     */
    public static function getListFields($listId)
    {
        return ListField::getAllByListId($listId);
    }

    /**
     * @param $tag
     * @param Campaign $campaign
     * @param string $content
     * @return bool
     */
    public static function getIsTagUsedInCampaign($tag, Campaign $campaign, $content = '')
    {
        if (!is_array($tag)) {
            $tag = array($tag);
        }

        $tag = array_filter(array_unique($tag));
        foreach ($tag as $t) {
            
            $t = str_replace(array('[', ']'), '', $t);
            
            if (empty($t)) {
                continue;
            }
            
            if (
                (!empty($content) && strpos($content, $t) !== false) ||
                (!empty($campaign->subject) && strpos($campaign->subject, $t) !== false) ||
                (!empty($campaign->to_name) && strpos($campaign->to_name, $t) !== false) ||
                (!empty($campaign->from_name) && strpos($campaign->from_name, $t) !== false) ||
                (!empty($campaign->from_email) && strpos($campaign->from_email, $t) !== false)
            ) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * @param $content
     * @param Campaign $campaign
     * @param ListSubscriber $subscriber
     * @return array|mixed|string
     * @throws CException
     */
    public static function getSubscriberFieldsSearchReplace($content, Campaign $campaign, ListSubscriber $subscriber)
    {
        // since 1.3.6.2
        if (MW_PERF_LVL && MW_PERF_LVL & MW_PERF_LVL_ENABLE_SUBSCRIBER_FIELD_CACHE) {
            return $subscriber->getAllCustomFieldsWithValues();
        }
        
        $searchReplace = array();
        $list = $campaign->list;
        foreach (self::getListFields($list->list_id) as $field) {
            $tag = $field['tag'];
            if (empty($tag) || !self::getIsTagUsedInCampaign($tag, $campaign, $content)) {
                continue;
            }
            $tag = '[' .  $tag . ']';

            $values = Yii::app()->getDb()->createCommand()
                ->select('value')
                ->from('{{list_field_value}}')
                ->where('subscriber_id = :sid AND field_id = :fid', array(
                    ':sid' => (int)$subscriber->subscriber_id, 
                    ':fid' => (int)$field['field_id']
                ))
                ->queryAll();

            $value = array();
            foreach ($values as $val) {
                $value[] = $val['value'];
            }
            $searchReplace[$tag] = implode(', ', $value);
        }
        
        return $searchReplace;
        
    }

    /**
     * @param $content
     * @param Campaign $campaign
     * @param ListSubscriber|null $subscriber
     * @param DeliveryServer|null $server
     * @return array|mixed|string
     * @throws CException
     * @throws Exception
     */
    public static function getCommonTagsSearchReplace($content, Campaign $campaign, ListSubscriber $subscriber = null, DeliveryServer $server = null)
    {
        $list            = $campaign->list;
        $options         = Yii::app()->options;
        $searchReplace   = array();
        $ccSearchReplace = array();
        $ceSearchReplace = array();
        
        // since 1.3.5.9
        static $customerCampaignTags = array();
        if (!empty($campaign->customer_id) && strpos($content, CustomerCampaignTag::getTagPrefix()) !== false) {
            if (!isset($customerCampaignTags[$campaign->customer_id])) {
                $customerCampaignTags[$campaign->customer_id] = array();
                $criteria = new CDbCriteria();
                $criteria->select = 'tag, content, random';
                $criteria->compare('customer_id', (int)$campaign->customer_id);
                $criteria->limit = 100;
                $models = CustomerCampaignTag::model()->findAll($criteria);
                foreach ($models as $model) {
                    $customerCampaignTags[$campaign->customer_id][] = $model->getAttributes(array('tag', 'content', 'random'));
                }
                unset($models);
            }

            foreach ($customerCampaignTags[$campaign->customer_id] as $ccTag) {
                $ccTagName  = '[' . CustomerCampaignTag::getTagPrefix() . $ccTag['tag'] . ']';
                $tagContent = StringHelper::decodeSurroundingTags($ccTag['content']);
                if ($ccTag['random'] == CustomerCampaignTag::TEXT_YES) {
                    $contentRandom = explode("\n", $tagContent);
                    $contentRandom = array_filter(array_unique(array_map('trim', $contentRandom)));
                    $tagContent    = $contentRandom[array_rand($contentRandom)];
                    unset($contentRandom);
                }

                // this still might contain unparsed campaign tags
                $ccSearchReplace[$ccTagName] = $tagContent;

                if (strpos($tagContent, '[') !== false && strpos($tagContent, ']') !== false) {
                    // cheap trick to add to the content so that the tags are found later...
                    $content .= $tagContent;
                }
            }
        }
        //

        // since 1.5.3
        static $extraCampaignTags = array();
        if (strpos($content, CampaignExtraTag::getTagPrefix()) !== false) {
            if (!isset($extraCampaignTags[$campaign->campaign_id])) {
                $extraCampaignTags[$campaign->campaign_id] = array();
                $criteria = new CDbCriteria();
                $criteria->select = 'tag, content';
                $criteria->compare('campaign_id', (int)$campaign->campaign_id);
                $criteria->limit = 100;
                $models = CampaignExtraTag::model()->findAll($criteria);
                foreach ($models as $model) {
                    $extraCampaignTags[$campaign->campaign_id][] = $model->getAttributes(array('tag', 'content'));
                }
                unset($models);
            }

            foreach ($extraCampaignTags[$campaign->campaign_id] as $ceTag) {
                $ceTagName  = '[' . CampaignExtraTag::getTagPrefix() . $ceTag['tag'] . ']';
                $tagContent = StringHelper::decodeSurroundingTags($ceTag['content']);
                
                // this still might contain unparsed campaign tags
                $ceSearchReplace[$ceTagName] = $tagContent;

                if (strpos($tagContent, '[') !== false && strpos($tagContent, ']') !== false) {
                    // cheap trick to add to the content so that the tags are found later...
                    $content .= $tagContent;
                }
            }
        }
        //

        // 1.3.9.5
        $randomContentBlock = array();
        if (strpos($content, '[RANDOM_CONTENT') !== false && preg_match_all('/\[RANDOM_CONTENT:([^\]]+)\]/', $content, $matches)) {
            foreach ($matches[0] as $index => $tag) {
                if (!isset($matches[1]) || !isset($matches[1][$index])) {
                    continue;
                }
                $tagValue = explode('|', $matches[1][$index]);
                $randKey  = array_rand($tagValue);
                $tagValue = trim($tagValue[$randKey]);
                
                if (stripos($tagValue, 'BLOCK') !== false && strpos($tagValue, ':') !== false) {
                    $blockName = explode(':', $tagValue);
                    $blockName = end($blockName);
                    $blockName = trim($blockName);

                    $rndModel = CampaignRandomContent::model()->findByAttributes(array(
                        'campaign_id' => $campaign->campaign_id,
                        'name'        => $blockName
                    ));

                    if (!empty($rndModel)) {
                        $tagValue = $rndModel->content;
                        $pattern  = '/href(\s+)?=(\s+)?(\042|\047)(\s+)?(.*?)(\s+)?(\042|\047)/i';
                        if (preg_match_all($pattern, $tagValue, $__matches)) {
                            $tagValue = self::transformLinksForTracking($tagValue, $campaign, $subscriber, true);
                        }
                    }
                }
                //

                $randomContentBlock[$tag] = $tagValue;
                if (strpos($tagValue, '[') !== false && strpos($tagValue, ']') !== false) {
                    // cheap trick to add to the content so that the tags are found later...
                    $content .= $tagValue;
                }
            }
        }
        
        // subscriber
        if (!empty($subscriber) && !empty($subscriber->subscriber_id)) {
            $searchReplace = self::getSubscriberFieldsSearchReplace($content, $campaign, $subscriber);
        }
        
        // list
        if (self::getIsTagUsedInCampaign('LIST_', $campaign, $content)) {
	        $searchReplace['[LIST_ID]']          = $list->list_id;
            $searchReplace['[LIST_UID]']         = $list->list_uid;
            $searchReplace['[LIST_NAME]']        = $list->display_name;
            $searchReplace['[LIST_DESCRIPTION]'] = $list->description;
            $searchReplace['[LIST_FROM_NAME]']   = $list->default->from_name;
            $searchReplace['[LIST_FROM_EMAIL]']  = $list->default->from_email;
            $searchReplace['[LIST_SUBJECT]']     = $list->default->subject;
        }

        // date 
        if (self::getIsTagUsedInCampaign('CURRENT_', $campaign, $content)) {
            $searchReplace['[CURRENT_YEAR]']            = date('Y');
            $searchReplace['[CURRENT_MONTH]']           = date('m');
            $searchReplace['[CURRENT_DAY]']             = date('d');
            $searchReplace['[CURRENT_DATE]']            = date('m/d/Y');
            $searchReplace['[CURRENT_MONTH_FULL_NAME]'] = Yii::t('campaigns', date('F'));
        }

	    // signs 
	    if (self::getIsTagUsedInCampaign('SIGN_', $campaign, $content)) {
		    $searchReplace['[SIGN_LT]']   = '<';
		    $searchReplace['[SIGN_LTE]']  = '<=';
		    $searchReplace['[SIGN_GT]']   = '>';
		    $searchReplace['[SIGN_GTE]']  = '>=';
	    }
        
        // company
        if (self::getIsTagUsedInCampaign('COMPANY_', $campaign, $content)) {
            
            $company = !empty($list->company) ? $list->company : null;
            $searchReplace['[COMPANY_FULL_ADDRESS]'] = $company ? nl2br($company->getFormattedAddress()) : '';
            $searchReplace['[COMPANY_NAME]']         = $company ? $company->name : '';
            $searchReplace['[COMPANY_WEBSITE]']      = $company ? $company->website : '';
            $searchReplace['[COMPANY_ADDRESS_1]']    = $company ? $company->address_1 : '';
            $searchReplace['[COMPANY_ADDRESS_2]']    = $company ? $company->address_2 : '';
            $searchReplace['[COMPANY_CITY]']         = $company ? $company->city: '';
            $searchReplace['[COMPANY_ZIP]']          = $company ? $company->zip_code : '';
            $searchReplace['[COMPANY_PHONE]']        = $company ? $company->phone : '';
            
            if (self::getIsTagUsedInCampaign('COMPANY_ZONE', $campaign, $content)) {
                $searchReplace['[COMPANY_ZONE]']        = $company && !empty($company->zone) ? $company->zone->name : '';
	            $searchReplace['[COMPANY_ZONE_CODE]']   = $company && !empty($company->zone) ? $company->zone->code : '';
	            
	            // 1.9.2
	            if (empty($searchReplace['[COMPANY_ZONE]']) && !empty($company) && !empty($company->zone_name)) {
		            $searchReplace['[COMPANY_ZONE]'] = $company->zone_name;
	            }
            }
            
            if (self::getIsTagUsedInCampaign('COMPANY_COUNTRY', $campaign, $content)) {
                $searchReplace['[COMPANY_COUNTRY]']         = $company && !empty($company->country) ? $company->country->name : '';
	            $searchReplace['[COMPANY_COUNTRY_CODE]']    = $company && !empty($company->country) ? $company->country->code : '';
            }
        }
        
        // campaign
        if (self::getIsTagUsedInCampaign('CAMPAIGN_', $campaign, $content)) {
            $searchReplace['[CAMPAIGN_NAME]']             = $campaign->name;
            $searchReplace['[CAMPAIGN_FROM_NAME]']        = $campaign->from_name;
            $searchReplace['[CAMPAIGN_FROM_EMAIL]']       = $campaign->from_email;
            $searchReplace['[CAMPAIGN_REPLY_TO]']         = $campaign->reply_to;
	        $searchReplace['[CAMPAIGN_ID]']               = $campaign->campaign_id;
            $searchReplace['[CAMPAIGN_UID]']              = $campaign->campaign_uid;
            $searchReplace['[CAMPAIGN_REPORT_ABUSE_URL]'] = '';
            $searchReplace['[CAMPAIGN_SEND_AT]']          = $campaign->send_at;
            $searchReplace['[CAMPAIGN_STARTED_AT]']       = !is_string($campaign->started_at) ? date('Y-m-d H:i:s') : $campaign->started_at;
            $searchReplace['[CAMPAIGN_DATETIME_ADDED]']   = $campaign->date_added;
            $searchReplace['[CAMPAIGN_DATE_ADDED]']       = date('Y-m-d', strtotime($campaign->date_added));
            $searchReplace['[CAMPAIGN_SEGMENT_NAME]']     = !empty($campaign->segment_id) ? $campaign->segment->name : '';
        }
        
        $campaignUrl                = $options->get('system.urls.frontend_absolute_url') . 'campaigns/' . $campaign->campaign_uid;
        $unsubscribeUrl             = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $list->list_uid . '/unsubscribe';
	    $unsubscribeFromCustomerUrl = $options->get('system.urls.frontend_absolute_url') . 'lists/unsubscribe-from-customer/' . $campaign->customer->customer_uid;
        $forwardFriendUrl           = $options->get('system.urls.frontend_absolute_url') . 'campaigns/' . $campaign->campaign_uid . '/forward-friend';
        $updateProfileUrl           = null;
        $webVersionUrl              = null;

        if (!empty($subscriber) && !empty($subscriber->subscriber_id)) {
            $unsubscribeUrl             .= '/' . $subscriber->subscriber_uid . '/' . $campaign->campaign_uid;
	        $unsubscribeFromCustomerUrl .= '/' . $subscriber->subscriber_uid . '/' . $campaign->campaign_uid;;
            $forwardFriendUrl           .= '/' . $subscriber->subscriber_uid;
            $updateProfileUrl = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $list->list_uid . '/update-profile/' . $subscriber->subscriber_uid;
            $webVersionUrl    = $options->get('system.urls.frontend_absolute_url') . 'campaigns/' . $campaign->campaign_uid . '/web-version/' . $subscriber->subscriber_uid;

            if (self::getIsTagUsedInCampaign('SUBSCRIBER_', $campaign, $content)) {
	            $searchReplace['[SUBSCRIBER_ID]']                   = $subscriber->subscriber_id;
                $searchReplace['[SUBSCRIBER_UID]']                  = $subscriber->subscriber_uid;
                $searchReplace['[SUBSCRIBER_IP]']                   = $subscriber->ip_address;
                $searchReplace['[SUBSCRIBER_DATE_ADDED]']           = $subscriber->date_added;
                $searchReplace['[SUBSCRIBER_DATE_ADDED_LOCALIZED]'] = $subscriber->dateAdded;
                
                // 1.5.0
	            $searchReplace['[SUBSCRIBER_EMAIL]']        = '';
                $searchReplace['[SUBSCRIBER_EMAIL_NAME]']   = '';
                $searchReplace['[SUBSCRIBER_EMAIL_DOMAIN]'] = '';
                $searchReplace['[EMAIL_NAME]']              = '';
                $searchReplace['[EMAIL_DOMAIN]']            = '';
                if (!empty($subscriber->email)) {
                    list($_emailName, $_emailDomain) = explode('@', $subscriber->email);
	                $searchReplace['[SUBSCRIBER_EMAIL]']        = $subscriber->email;
                    $searchReplace['[SUBSCRIBER_EMAIL_NAME]']   = $_emailName;
                    $searchReplace['[SUBSCRIBER_EMAIL_DOMAIN]'] = $_emailDomain;
                    $searchReplace['[EMAIL_NAME]']              = $_emailName;
                    $searchReplace['[EMAIL_DOMAIN]']            = $_emailDomain;
                }
                //
            }

            if (self::getIsTagUsedInCampaign('CAMPAIGN_REPORT_ABUSE_URL', $campaign, $content)) {
                $searchReplace['[CAMPAIGN_REPORT_ABUSE_URL]'] = $campaignUrl . '/report-abuse/' . $list->list_uid . '/' . $subscriber->subscriber_uid;
            }
            
            // 1.3.8.8
            if (self::getIsTagUsedInCampaign('SUBSCRIBER_OPTIN_', $campaign, $content)) {
                $searchReplace['[SUBSCRIBER_OPTIN_IP]']   = !empty($subscriber->optinHistory) ? $subscriber->optinHistory->optin_ip : '';
                $searchReplace['[SUBSCRIBER_OPTIN_DATE]'] = !empty($subscriber->optinHistory) ? $subscriber->optinHistory->optin_date : '';
            }

            // 1.3.8.8
            if (self::getIsTagUsedInCampaign('SUBSCRIBER_CONFIRM_', $campaign, $content)) {
                $searchReplace['[SUBSCRIBER_CONFIRM_IP]']   = !empty($subscriber->optinHistory) ? $subscriber->optinHistory->confirm_ip : '';
                $searchReplace['[SUBSCRIBER_CONFIRM_DATE]'] = !empty($subscriber->optinHistory) ? $subscriber->optinHistory->confirm_date : '';
            }
            
            // 1.3.9.3
            if (self::getIsTagUsedInCampaign('SUBSCRIBER_LAST_SENT_DATE', $campaign, $content)) {
                $criteria = new CDbCriteria();
                $criteria->select = 'date_added';
                $criteria->compare('subscriber_id', (int)$subscriber->subscriber_id);
                $criteria->order = 'date_added DESC';
                $criteria->limit = 1;
                $model = CampaignDeliveryLog::model()->find($criteria);
                $searchReplace['[SUBSCRIBER_LAST_SENT_DATE]']           = $model ? $model->date_added : '';
                $searchReplace['[SUBSCRIBER_LAST_SENT_DATE_LOCALIZED]'] = $model ? $model->dateAdded : '';
            }
        }

        if (self::getIsTagUsedInCampaign('CURRENT_DOMAIN', $campaign, $content)) {
            $searchReplace['[CURRENT_DOMAIN]']     = parse_url($options->get('system.urls.frontend_absolute_url'), PHP_URL_HOST);
            $searchReplace['[CURRENT_DOMAIN_URL]'] = $options->get('system.urls.frontend_absolute_url');
        }
        
        // server - since 1.3.6.6
        if (self::getIsTagUsedInCampaign('DS_', $campaign, $content)) {
            $searchReplace['[DS_NAME]']          = !empty($server) && !empty($server->name) ? $server->name : '';
            $searchReplace['[DS_HOST]']          = !empty($server) && !empty($server->hostname) ? $server->hostname : '';
            $searchReplace['[DS_ID]']            = !empty($server) && !empty($server->server_id) ? $server->server_id : '';
            $searchReplace['[DS_TYPE]']          = !empty($server) && !empty($server->type) ? $server->type : '';
            $searchReplace['[DS_FROM_NAME]']     = !empty($server) && !empty($server->from_name) ? $server->from_name : '';
            $searchReplace['[DS_FROM_EMAIL]']    = !empty($server) && !empty($server->from_email) ? $server->from_email : '';
            $searchReplace['[DS_REPLYTO_EMAIL]'] = !empty($server) && !empty($server->reply_to_email) ? $server->reply_to_email : '';
        }

        // other urls
        if (self::getIsTagUsedInCampaign('UNSUBSCRIBE_', $campaign, $content)) {
            $searchReplace['[UNSUBSCRIBE_URL]']                 = $unsubscribeUrl;
            $searchReplace['[UNSUBSCRIBE_LINK]']                = CHtml::link(Yii::t('campaigns', 'Unsubscribe'), $unsubscribeUrl);
            $searchReplace['[DIRECT_UNSUBSCRIBE_URL]']          = $unsubscribeUrl . (!empty($subscriber) ? '/unsubscribe-direct' : '');
            $searchReplace['[DIRECT_UNSUBSCRIBE_LINK]']         = CHtml::link(Yii::t('campaigns', 'Unsubscribe'), $unsubscribeUrl . (!empty($subscriber) ? '/unsubscribe-direct' : ''));
            $searchReplace['[UNSUBSCRIBE_FROM_CUSTOMER_URL]']   = $unsubscribeFromCustomerUrl;
	        $searchReplace['[UNSUBSCRIBE_FROM_CUSTOMER_LINK]']  = CHtml::link(Yii::t('campaigns', 'Unsubscribe from this customer'), $unsubscribeFromCustomerUrl);
        }
        
        // vcards - 1.7.6
	    if (self::getIsTagUsedInCampaign('_VCARD_URL', $campaign, $content)) {
	    	$searchReplace['[CAMPAIGN_VCARD_URL]'] = $options->get('system.urls.frontend_absolute_url') . 'campaigns/' . $campaign->campaign_uid . '/vcard';
		    $searchReplace['[LIST_VCARD_URL]']     = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $list->list_uid . '/vcard';
	    }
        
        // 1.3.8.0
        if (!empty($server)) {
            if ($server->type == 'elasticemail-web-api' || preg_match('/smtp(\d+)?\.elasticemail\.com/i', $server->hostname)) {
                
                if (self::getIsTagUsedInCampaign('UNSUBSCRIBE_', $campaign, $content)) {
                    $searchReplace['[UNSUBSCRIBE_URL]']                 = sprintf('{unsubscribe:%s}', $unsubscribeUrl);
                    $searchReplace['[UNSUBSCRIBE_LINK]']                = CHtml::link(Yii::t('campaigns', 'Unsubscribe'), sprintf('{unsubscribe:%s}', $unsubscribeUrl));
                    $searchReplace['[DIRECT_UNSUBSCRIBE_URL]']          = sprintf('{unsubscribe:%s}', $unsubscribeUrl . (!empty($subscriber) ? '/unsubscribe-direct' : ''));
                    $searchReplace['[DIRECT_UNSUBSCRIBE_LINK]']         = CHtml::link(Yii::t('campaigns', 'Unsubscribe'), sprintf('{unsubscribe:%s}', $unsubscribeUrl . (!empty($subscriber) ? '/unsubscribe-direct' : '')));
	                $searchReplace['[UNSUBSCRIBE_FROM_CUSTOMER_URL]']   = sprintf('{unsubscribe:%s}', $unsubscribeFromCustomerUrl);
	                $searchReplace['[UNSUBSCRIBE_FROM_CUSTOMER_LINK]']  = CHtml::link(Yii::t('campaigns', 'Unsubscribe from this customer'), sprintf('{unsubscribe:%s}', $unsubscribeFromCustomerUrl));
                }

                if (self::getIsTagUsedInCampaign('COMPANY_', $campaign, $content)) {
                    $searchReplace['[COMPANY_FULL_ADDRESS]'] = '{accountaddress}';
                }
            }
        }
        
        if (self::getIsTagUsedInCampaign('UPDATE_PROFILE_URL', $campaign, $content)) {
            $searchReplace['[UPDATE_PROFILE_URL]'] = $updateProfileUrl;
        }

        if (self::getIsTagUsedInCampaign('WEB_VERSION_URL', $campaign, $content)) {
            $searchReplace['[WEB_VERSION_URL]'] = $webVersionUrl;
        }

        if (self::getIsTagUsedInCampaign('CAMPAIGN_URL', $campaign, $content)) {
            $searchReplace['[CAMPAIGN_URL]'] = $campaignUrl;
        }

        if (self::getIsTagUsedInCampaign('FORWARD_FRIEND_URL', $campaign, $content)) {
            $searchReplace['[FORWARD_FRIEND_URL]'] = $forwardFriendUrl;
        }

	    // 1.8.1
	    if (!empty($subscriber) && self::getIsTagUsedInCampaign('SURVEY', $campaign, $content)) {
		    if (preg_match_all('/\[SURVEY:([a-z0-9]{13}):VIEW_URL\]/i', $content, $surveyMatches)) {
			    if (isset($surveyMatches[0], $surveyMatches[1])) {
				    foreach ($surveyMatches[0] as $index => $surveyMatch) {
					    $surveyUid = $surveyMatches[1][$index];
					    $url = sprintf('%ssurveys/%s/%s/%s',
						    $options->get('system.urls.frontend_absolute_url'),
						    $surveyUid,
						    $subscriber->subscriber_uid,
						    $campaign->campaign_uid
					    );
					    $searchReplace[$surveyMatch] = $url;
				    }
			    }
		    }
	    }
	    //
        
        $to  = str_replace(array_keys($searchReplace), array_values($searchReplace), $campaign->to_name);
        $to  = self::getTagFilter()->apply($to, $searchReplace);
        if (empty($to) && !empty($subscriber) && !empty($subscriber->subscriber_id)) {
            $to = $subscriber->email;
        }
        if (empty($to)) {
            $to = 'unknown';
        }
        $searchReplace['[CAMPAIGN_TO_NAME]'] = $to;

        $subject = str_replace(array_keys($searchReplace), array_values($searchReplace), $campaign->subject);
        $subject = self::getTagFilter()->apply($subject, $searchReplace);
        if (empty($subject)) {
            $subject = 'unknown';
        }

        // since 1.3.5, rotate content randomly
        $subject = self::applyRandomContentTag($subject);
        //
        
        $searchReplace['[CAMPAIGN_SUBJECT]'] = $subject;
        
        // 1.3.9.3
        foreach ($ccSearchReplace as $tag => $tagContent) {
            if (strpos($tagContent, '[') !== false && strpos($tagContent, ']') !== false) {
                $tagContent = str_replace(array_keys($searchReplace), array_values($searchReplace), $tagContent);
            }
            $searchReplace[$tag] = $tagContent;
        }
        unset($ccSearchReplace);
        //

        // 1.5.3
        foreach ($ceSearchReplace as $tag => $tagContent) {
            if (strpos($tagContent, '[') !== false && strpos($tagContent, ']') !== false) {
                $tagContent = str_replace(array_keys($searchReplace), array_values($searchReplace), $tagContent);
            }
            $searchReplace[$tag] = $tagContent;
        }
        unset($ceSearchReplace);
        //
        
        // 1.3.9.5
        foreach ($randomContentBlock as $tag => $tagContent) {
        	
            if (strpos($tagContent, '[') !== false && strpos($tagContent, ']') !== false) {
                $tagContent = str_replace(array_keys($searchReplace), array_values($searchReplace), $tagContent);
            }
            
            $searchReplace[$tag] = $tagContent;

            // 1.8.4
	        if (strpos($tag, '[') !== false && strpos($tag, ']') !== false) {
		        $tag = str_replace(array_keys($searchReplace), array_values($searchReplace), $tag);
	        }

	        $searchReplace[$tag] = $tagContent;
	        //
	        
        }
        unset($randomContentBlock);
        //
        
        $searchReplace = (array)Yii::app()->hooks->applyFilters('campaigns_get_common_tags_search_replace', $searchReplace, $campaign, $subscriber, $server);
        
        return $searchReplace;
    }

    /**
     * @return EmailTemplateTagFilter
     */
    public static function getTagFilter()
    {
        static $tagFilter;
        if ($tagFilter === null) {
            $tagFilter = new EmailTemplateTagFilter();
        }
        return $tagFilter;
    }

    /**
     * @param $emailContent
     * @param $emailHeader
     * @param Campaign $campaign
     * @return mixed
     */
    public static function injectEmailHeader($emailContent, $emailHeader, Campaign $campaign)
    {
        return preg_replace('/<body([^>]+)?>/i', '$0' . $emailHeader, $emailContent);
    }
    
    /**
     * @param $emailContent
     * @param $emailFooter
     * @param Campaign $campaign
     * @return mixed
     */
    public static function injectEmailFooter($emailContent, $emailFooter, Campaign $campaign)
    {
        return str_ireplace('</body>', $emailFooter . "\n" . '</body>', $emailContent);
    }

    /**
     * @param $emailContent
     * @param $preheader
     * @param Campaign $campaign
     * @return mixed
     */
    public static function injectPreheader($emailContent, $preheader, Campaign $campaign)
    {
        $hideCss      = 'display:none!important;mso-hide:all;';
        $style        = sprintf('<style type="text/css">span.preheader{%s}</style>', $hideCss);
        $emailContent = str_ireplace('</head>', $style . '</head>', $emailContent);
        $preheader    = sprintf('<span class="preheader" style="%s">%s</span>', $hideCss, $preheader);
        $preheader    = str_replace('$', '\$', $preheader);
        return preg_replace('/<body([^>]+)?>/six', '$0' . $preheader, $emailContent);
    }

    /**
     * @param $tag
     * @return false|string
     */
    public static function parseDateTag($tag)
    {
        $params = array_merge(array(
            'FORMAT' => 'Y-m-d',
        ), StringHelper::getTagParams($tag));
        return @date($params['FORMAT']);
    }

    /**
     * @param $tag
     * @return false|string
     */
    public static function parseDateTimeTag($tag)
    {
        $params = array_merge(array(
            'FORMAT' => 'Y-m-d H:i:s',
        ), StringHelper::getTagParams($tag));
        return @date($params['FORMAT']);
    }

    /**
     * @param $content
     * @param $pattern
     * @return mixed
     */
    public static function injectGoogleUtmTagsIntoTemplate($content, $pattern)
    {
        $pattern = trim($pattern, '?&/');
        $pattern = str_replace(array('&utm;', '&amp;', ';'), array('&utm', '&', ''), $pattern);

        $patternArray = array();
        parse_str($pattern, $patternArray);
        if (empty($patternArray)) {
            return $content;
        }

        if (!CommonHelper::functionExists('qp')) {
            require_once(Yii::getPathOfAlias('common.vendors.QueryPath.src.QueryPath') . '/QueryPath.php');
        }

        libxml_use_internal_errors(true);
        
        $urlSearchReplace = array();
        
        try {
            
            $ioFilter = Yii::app()->ioFilter;
            $content  = StringHelper::normalizeUrlsInContent($content);
            
            $query = qp($ioFilter->purify(CHtml::decode(urldecode($content))), 'body', array(
                'ignore_parser_warnings'    => true,
                'convert_to_encoding'       => Yii::app()->charset,
                'convert_from_encoding'     => Yii::app()->charset,
                'use_parser'                => 'html',
            ));

            // to do: what action should we take here?
            if (count(libxml_get_errors()) > 0) {}

            $anchors = $query->top()->find('a');

            if (empty($anchors) || !is_object($anchors) || $anchors->length == 0) {
                throw new Exception('No anchor found!');
            }

            foreach ($anchors as $anchor) {
                if (!($href = $anchor->attr('href'))) {
                    continue;
                }
                $ohref = $href;
                $href  = rtrim(rtrim(trim(urldecode($href), '?&'), '/'), '/');
                $title = trim($anchor->attr('title'));

                //skip url tags
                if (preg_match('/^\[([A-Z0-9\:_]+)_URL\]$/i', $href)) {
                    continue;
                }

                if (!($parsedQueryString = parse_url($href, PHP_URL_QUERY))) {
                    $queryString = urldecode(http_build_query($patternArray, '', '&'));
                    if (!empty($title)) {
                        $queryString = str_replace('[TITLE_ATTR]', $title, $queryString);
                    }
	                $urlSearchReplace[$ohref] = rtrim($href, '/') . '/?' . $queryString;
                    continue;
                }

                $parsedUrlQueryArray = array();
                parse_str($parsedQueryString, $parsedUrlQueryArray);
                if (empty($parsedUrlQueryArray)) {
                    continue;
                }

                $href = str_replace($parsedQueryString, '[QS]', $href);
                $_patternArray = CMap::mergeArray($parsedUrlQueryArray, $patternArray);
                $queryString   = urldecode(http_build_query($_patternArray, '', '&'));
                if (!empty($title)) {
                    $queryString = str_replace('[TITLE_ATTR]', $title, $queryString);
                }
                $urlSearchReplace[$ohref] = str_replace('[QS]', $queryString, $href);
            }
            
            $sort = array();
            foreach ($urlSearchReplace as $k => $v) {
                $sort[] = strlen($k);
            }
            array_multisort($urlSearchReplace, $sort, SORT_NUMERIC, SORT_DESC);
            
            foreach ($urlSearchReplace as $url => $replacement) {
                $decodedUrl = urldecode($url);
                $searchFor  = array($url);
                if ($decodedUrl != $url) {
                    $searchFor[] = $decodedUrl;
                }
                foreach ($searchFor as $item) {
                    $pattern = sprintf('#href=(\042|\047)(%s)(\042|\047)#i', preg_quote($item, '#'));
                    $content = preg_replace($pattern, sprintf('href="%s"', $replacement), $content);
                }
            }
            
            unset($anchors, $query);

        } catch (Exception $e) {}

        libxml_use_internal_errors(false);

        return $content;
    }

    /**
     * @return array
     */
    public static function getParsedFieldValueByListFieldValueTagInfo()
    {
        return Yii::app()->hooks->applyFilters('common_helper_parsed_field_value_by_list_field_value_tag_info', array(
            '[INCREMENT_BY_X]'  => Yii::t('campaigns', 'Increment the value by X where X is an integer'),
            '[DECREMENT_BY_X]'  => Yii::t('campaigns', 'Decrement the value by X where X is an integer'),
            '[MULTIPLY_BY_X]'   => Yii::t('campaigns', 'Multiply the value by X where X is an integer'),
            '[DATETIME]'        => Yii::t('campaigns', 'Set current date and time, in Y-m-d H:i:s format'),
            '[DATE]'            => Yii::t('campaigns', 'Set current date, in Y-m-d format'),
            '[IP_ADDRESS]'      => Yii::t('campaigns', 'Set the current IP address'),
            '[GEO_COUNTRY]'     => Yii::t('campaigns', 'Set the current user country based on IP address'),
            '[GEO_STATE]'       => Yii::t('campaigns', 'Set the current user state/zone based on IP address'),
            '[GEO_CITY]'        => Yii::t('campaigns', 'Set the current user city based on IP address'),
            '[USER_AGENT]'      => Yii::t('campaigns', 'Set the current User Agent string'),
            '[UA_BROWSER]'      => Yii::t('campaigns', 'Set the current user browser based on the User Agent string'),
            '[UA_OS]'           => Yii::t('campaigns', 'Set the current user operating system based on the User Agent string'),
            '[UA_ENGINE]'       => Yii::t('campaigns', 'Set the current user browser engine based on the User Agent string'),
	        '[UA_DEVICE]'       => Yii::t('campaigns', 'Set the current user browser device based on the User Agent string'),
        ));
    }

    /**
     * @param CAttributeCollection $collection
     * @return string
     */
    public static function getParsedFieldValueByListFieldValue(CAttributeCollection $collection)
    {
        $fieldValue = $collection->fieldValue;
        $valueModel = $collection->valueModel;
        
        if (preg_match('/\[INCREMENT_BY_(\d+)\]/', $fieldValue, $matches)) {
            $fieldValue = (int)$valueModel->value + (int)$matches[1];
        } elseif (preg_match('/\[DECREMENT_BY_(\d+)\]/', $fieldValue, $matches)) {
            $fieldValue = (int)$valueModel->value - (int)$matches[1];
        } elseif (preg_match('/\[MULTIPLY_BY_(\d+)\]/', $fieldValue, $matches)) {
            $fieldValue = (int)$valueModel->value * (int)$matches[1];
        }
        
        $ipAddress = '';
        $userAgent = '';
        if (!MW_IS_CLI) {
        	$ipAddress = Yii::app()->request->getUserHostAddress();
        	$userAgent = Yii::app()->request->getUserAgent();
        }

        // 1.8.5
	    $date     = date('Y-m-d');
	    $dateTime = date('Y-m-d H:i:s');
	    if (!empty($collection->campaign) && !empty($collection->campaign->customer)) {
		    $date     = $collection->campaign->customer->dateTimeFormatter->formatDateTime(null, null, 'yyyy-MM-dd');
		    $dateTime = $collection->campaign->customer->dateTimeFormatter->formatDateTime();
	    }
	    
        $searchReplace = array(
            '[DATETIME]'        => $dateTime,
            '[DATE]'            => $date,
            '[IP_ADDRESS]'      => $ipAddress,
            '[GEO_COUNTRY]'     => '',
            '[GEO_STATE]'       => '',
            '[GEO_CITY]'        => '',
            '[USER_AGENT]'      => StringHelper::truncateLength($userAgent, 250),
	        '[UA_BROWSER]'      => '',
	        '[UA_OS]'           => '',
	        '[UA_ENGINE]'       => '',
	        '[UA_DEVICE]'       => '',
        );
        
        if ($ipLocation = IpLocation::findByIp($ipAddress)) {
	        $searchReplace = CMap::mergeArray($searchReplace, array(
		        '[GEO_COUNTRY]' => $ipLocation->country_name,
		        '[GEO_STATE]'   => $ipLocation->zone_name,
		        '[GEO_CITY]'    => $ipLocation->city_name,
	        ));
        }
        
        if ($userAgent && version_compare(PHP_VERSION, '5.4', '>=')) {
        	$class  = '\WhichBrowser\Parser';
        	$parser = new $class($userAgent, array('detectBots' => false));
        	$searchReplace = CMap::mergeArray($searchReplace, array(
		        '[UA_BROWSER]'  => !empty($parser->browser->name) ? $parser->browser->name  : '',
		        '[UA_OS]'       => !empty($parser->os->name)      ? $parser->os->name       : '',
		        '[UA_ENGINE]'   => !empty($parser->engine->name)  ? $parser->engine->name   : '',
		        '[UA_DEVICE]'   => !empty($parser->device->type)  ? ucfirst($parser->device->type)   : '',
	        ));
        }

        $searchReplace = (array)Yii::app()->hooks->applyFilters('common_helper_parsed_field_value_by_list_field_value_search_replace', $searchReplace);

        return (string)str_replace(array_keys($searchReplace), array_values($searchReplace), $fieldValue);
    }

    /**
     * @param $content
     * @return bool
     */
    public static function contentHasXmlFeed($content)
    {
        $content = StringHelper::decodeSurroundingTags($content);
        return strpos($content, '[XML_FEED_BEGIN ') !== false && strpos($content, '[XML_FEED_END]') !== false;
    }

    /**
     * @param $content
     * @return bool
     */
    public static function contentHasJsonFeed($content)
    {
        $content = StringHelper::decodeSurroundingTags($content);
        return strpos($content, '[JSON_FEED_BEGIN ') !== false && strpos($content, '[JSON_FEED_END]') !== false;
    }

    /**
     * @param $content
     * @return bool
     */
    public static function hasRemoteContentTag($content)
    {
        $content = StringHelper::decodeSurroundingTags($content);
        return strpos($content, '[REMOTE_CONTENT ') !== false;
    }

	/**
	 * @param $content
	 * @param Campaign $campaign
	 * @param ListSubscriber $subscriber
	 *
	 * @return mixed
	 * @throws CException
	 */
    public static function fetchContentForRemoteContentTag($content, Campaign $campaign, ListSubscriber $subscriber = null)
    {
        if (!self::hasRemoteContentTag($content)) {
            return $content;
        }
        
        // 1.6.4 - replace regular tags to avoid issues in next regex match
	    $content = preg_replace('/\[([A-Z0-9\_]+)\]/', '##(##$1##)##', $content);

	    $pattern = '/\[REMOTE_CONTENT(.*?)\]/sx';
        $matched = preg_match_all($pattern, $content, $multiMatches);

	    // 1.6.4 - restore regular tags
	    $content = str_replace(array('##(##', '##)##'), array('[', ']'), $content);
	    
        if (!$matched) {
            return $content;
        }
        
        if (!isset($multiMatches[0], $multiMatches[0][0])) {
	        return $content;
        }

        foreach ($multiMatches[0] as $fullHtml) {

        	// 1.6.4 - put back the tags
	        $fullHtml = str_replace(array('##(##', '##)##'), array('[', ']'), $fullHtml);
	        
            // when it has been replaced already in the loop
            if (strpos($content, $fullHtml) === false) {
                continue;
            }
            
            // 1.6.4
	        $searchReplace  = self::getCommonTagsSearchReplace($fullHtml, $campaign, $subscriber);
            $fullHtmlParsed = str_replace(array_keys($searchReplace), array_values($searchReplace), $fullHtml);
            //
	        
            $remoteContent = false;
            
            if ($campaign) {
                $cacheKey      = sha1(__METHOD__ . $fullHtmlParsed . $campaign->campaign_uid);
                $remoteContent = Yii::app()->cache->get($cacheKey);
            }
            
            if ($remoteContent === false) {
                
                $attributesPattern  = '/([a-z0-9\-\_]+) *= *(?:([\'"])(.*?)\\2|([^ "\'>]+))/';
                preg_match_all($attributesPattern, $fullHtmlParsed, $matches, PREG_SET_ORDER);
                if (empty($matches)) {
                    continue;
                }

                $attributes = array();
                foreach ($matches as $match) {
                    if (!isset($match[1], $match[3])) {
                        continue;
                    }
                    $attributes[strtolower($match[1])] = $match[3];
                }
				
                $attributes['url'] = isset($attributes['url']) ? str_replace('&amp;', '&', $attributes['url']) : null;
                if (!$attributes['url'] || !FilterVarHelper::url($attributes['url'])) {
                    continue;
                }
				
                $remoteContent = AppInitHelper::simpleCurlGet($attributes['url']);
                $remoteContent = !empty($remoteContent['message']) ? $remoteContent['message'] : '';
            }
            
            if ($campaign) {
                Yii::app()->cache->set($cacheKey, $remoteContent);
            }

            $content = str_replace($fullHtml, $remoteContent, $content);
        }
        
        return $content;
    }

    /**
     * @param $content
     * @return mixed
     */
    public static function applyRandomContentTag($content)
    {
        if (strpos($content, '[RANDOM_CONTENT') !== false && preg_match_all('/\[RANDOM_CONTENT:([^\]]+)\]/', $content, $matches)) {
            foreach ($matches[0] as $index => $tag) {
                if (!isset($matches[1]) || !isset($matches[1][$index])) {
                    continue;
                }
                $tagValue = explode('|', $matches[1][$index]);
                $randKey  = array_rand($tagValue);
                $content  = str_replace($tag, $tagValue[$randKey], $content);
            }
        }
        return $content;
    }

    /**
     * @param Campaign $campaign
     * @return array
     */
    public static function getTimewarpOffsets(Campaign $campaign)
    {
        $carbonClass = '\Carbon\Carbon';
        $offsets = array();
        
        $timewarpHour = (int)$campaign->option->timewarp_hour;
        $timewarpMin  = (int)$campaign->option->timewarp_minute;
        
        foreach (DateTimeHelper::getTimeZones() as $timezone => $name) {

            $remote = call_user_func(array($carbonClass, 'now'), $timezone);
            if (isset($offsets[$remote->offset])) {
                continue;
            }
            
            $remoteSet = call_user_func(array($carbonClass, 'now'), $timezone);
            $remoteSet->hour($timewarpHour)->minute($timewarpMin);
            
            if ($remote->lte($remoteSet)) {
                continue;
            }
            
            $offsets[$remote->offset] = true;
        }

        if (empty($offsets)) {
            return array();
        }
        
        $negative = $positive = array();

        $offsets  = array_keys($offsets);
        foreach ($offsets as $offset) {
            if ($offset >= 0) {
                $positive[] = $offset;
            } else {
                $negative[] = $offset;
            }
        }
        unset($offsets);
        
        return array($negative, $positive);
    }

    /**
     * @param Campaign $campaign
     * @return CDbCriteria|null
     */
    public static function getTimewarpCriteria(Campaign $campaign)
    {
        $criteria = new CDbCriteria();
        
        if (!($offsets = self::getTimewarpOffsets($campaign))) {
            return null;
        }
        
        $criteria->join = ' LEFT JOIN {{ip_location}} ipLocation ON ipLocation.ip_address = t.ip_address ';

        $offsetCondition = array();
        if (!empty($offsets[0])) {
            $offsetCondition[] = '(ipLocation.timezone_offset >= :nmin AND ipLocation.timezone_offset <= :nmax)';
            $criteria->params[':nmin'] = min($offsets[0]);
            $criteria->params[':nmax'] = max($offsets[0]);
        }
        if (!empty($offsets[1])) {
            $offsetCondition[] = '(ipLocation.timezone_offset >= :pmin AND ipLocation.timezone_offset <= :pmax)';
            $criteria->params[':pmin'] = min($offsets[1]);
            $criteria->params[':pmax'] = max($offsets[1]);
        }

        $condition = implode(' OR ', $offsetCondition);
        $condition = sprintf('(ipLocation.timezone_offset IS NULL OR (%s))', $condition);
        $criteria->addCondition($condition);
        
        return $criteria;
    }
}
