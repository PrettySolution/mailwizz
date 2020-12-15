<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignJsonFeedParser
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3
 */

class CampaignJsonFeedParser
{
    /**
     * @var int 
     */
    public static $maxItemsCount = 100;

    /**
     * @var int 
     */
    public static $itemsCount = 10;

    /**
     * @var int
     */
    public static $itemsOffset = 0;

    /**
     * @var int 
     */
    public static $daysBack = -1;

    /**
     * @var string 
     */
    public static $noItemAction = '';

    /**
     * @param $content
     * @param Campaign $campaign
     * @param ListSubscriber|null $subscriber
     * @param bool $cache
     * @param null $cacheKeySuffix
     * @param DeliveryServer|null $server
     * @return string
     * @throws Exception
     */
    public static function parseContent($content, Campaign $campaign, ListSubscriber $subscriber = null, $cache = false, $cacheKeySuffix = null, DeliveryServer $server = null)
    {
        if (!$cacheKeySuffix) {
            $cacheKeySuffix = $content;
        }
        $cacheKey = sha1(__METHOD__ . $campaign->campaign_uid . sha1($cacheKeySuffix));
        if ($cache && ($cachedContent = Yii::app()->cache->get($cacheKey))) {
            return $cachedContent;
        }

        $content = StringHelper::decodeSurroundingTags($content);
        if (!CampaignHelper::contentHasJsonFeed($content)) {
            return $content;
        }
        
        // $pattern = '/\[JSON_FEED_BEGIN(.*?)\]((?!\[JSON_FEED_END).)*\[JSON_FEED_END\]/sx';
        $pattern = '/\[JSON_FEED_BEGIN(.*?)\](.*?)\[JSON_FEED_END\]/sx';
        if (!preg_match_all($pattern, $content, $multiMatches)) {
            return $content;
        }

        if (!isset($multiMatches[0], $multiMatches[0][0])) {
            return $content;
        }

        $doCache = false;
        
        foreach ($multiMatches[0] as $fullFeedHtml) {
            $_fullFeedHtml = CHtml::decode($fullFeedHtml);
            $matchKeyBefore= sha1($_fullFeedHtml);
            $searchReplace = CampaignHelper::getCommonTagsSearchReplace($_fullFeedHtml, $campaign, $subscriber, $server);
            $_fullFeedHtml = str_replace(array_keys($searchReplace), array_values($searchReplace), $_fullFeedHtml);
            $_fullFeedHtml = CampaignHelper::getTagFilter()->apply($_fullFeedHtml, $searchReplace);
            $matchKeyAfter = sha1($_fullFeedHtml);

            if (!preg_match('/\[JSON_FEED_BEGIN(.*?)\](.*?)\[JSON_FEED_END\]/sx', $_fullFeedHtml, $matches)) {
                continue;
            }

            if (!isset($matches[0], $matches[2])) {
                continue;
            }

            $feedItemTemplate = $matches[2];

            preg_match('/\[JSON_FEED_BEGIN(.*?)\]/', $_fullFeedHtml, $matches);
            if (empty($matches[1])) {
                continue;
            }

            $attributesPattern  = '/([a-z0-9\-\_]+) *= *(?:([\'"])(.*?)\\2|([^ "\'>]+))/';
            preg_match_all($attributesPattern, $matches[1], $matches, PREG_SET_ORDER);
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

            $count = self::$itemsCount;
            if (isset($attributes['count']) && (int)$attributes['count'] > 0 && (int)$attributes['count'] <= self::$maxItemsCount) {
                $count = (int)$attributes['count'];
            }

            // 1.5.1
            $offset = self::$itemsOffset;
            if (isset($attributes['offset']) && (int)$attributes['offset'] > 0) {
                $offset = (int)$attributes['offset'];
            }

            // 1.5.1
            $daysBack = self::$daysBack;
            if (isset($attributes['days-back']) && (int)$attributes['days-back'] >= 0) {
                $daysBack = (int)$attributes['days-back'];
            }

            // 1.5.1
            $noItemAction    = !isset($attributes['no-item-action']) ? self::$noItemAction : $attributes['no-item-action'];
            
            // 1.7.4
	        $sendOnlyUniqueItems = !empty($attributes['send-only-unique-items']) && strtolower($attributes['send-only-unique-items']) == 'yes';

            $doCache   = $matchKeyBefore == $matchKeyAfter && !$campaign->isDraft && $cache;
            $feedItems = self::getRemoteFeedItems($attributes['url'], $count, $campaign, $doCache, $offset, $daysBack, $noItemAction, $sendOnlyUniqueItems);
            
            $feedItemsMap = array(
                '[JSON_FEED_ITEM_TITLE]'         => 'title',
                '[JSON_FEED_ITEM_DESCRIPTION]'   => 'description',
                '[JSON_FEED_ITEM_CONTENT]'       => 'content',
                '[JSON_FEED_ITEM_IMAGE]'         => 'image',
                '[JSON_FEED_ITEM_LINK]'          => 'link',
                '[JSON_FEED_ITEM_PUBDATE]'       => 'pubDate',
                '[JSON_FEED_ITEM_GUID]'          => 'guid',
            );

            $html = '';
            foreach ($feedItems as $index => $feedItem) {
                $itemHtml = $feedItemTemplate;
                foreach ($feedItemsMap as $tag => $mapValue) {
                    if (!isset($feedItem[$mapValue]) || !is_string($feedItem[$mapValue])) {
                        continue;
                    }
                    $itemHtml = str_replace($tag, $feedItem[$mapValue], $itemHtml);
                }
                
                if (sha1($itemHtml) != sha1($feedItemTemplate)) {
                    $html .= $itemHtml;
                }
            }

	        // since 1.9.2
	        foreach ($feedItems as $index => $feedItem) {
		        $itemHtml = $feedItemTemplate;
		        foreach ($feedItemsMap as $tag => $mapValue) {
			        if (!isset($feedItem[$mapValue]) || !is_string($feedItem[$mapValue])) {
				        continue;
			        }
			        $tag = str_replace('_FEED_ITEM_', '_FEED_ITEM_' . ($index + 1) . '_', $tag);
			        $itemHtml = str_replace($tag, $feedItem[$mapValue], $itemHtml);
		        }

		        if (sha1($itemHtml) != sha1($feedItemTemplate)) {
			        $html .= $itemHtml;
		        }
	        }
	        //

            // since 1.5.1
            foreach ($feedItems as $index => $feedItem) {
                foreach ($feedItemsMap as $tag => $mapValue) {
                    if (!isset($feedItem[$mapValue]) || !is_string($feedItem[$mapValue])) {
                        continue;
                    }
                    $tagNum = str_replace('_FEED_ITEM_', '_FEED_ITEM_' . ($index + 1) . '_', $tag);
                    $html   = str_replace($tagNum, $feedItem[$mapValue], $html);
                }
            }
            //

            $content = str_replace($fullFeedHtml, $html, $content);
        }

        if ($doCache) {
            $cacheTTL = !$campaign->getIsAutoresponder() ? MW_CACHE_TTL : 3600 * 12;
            Yii::app()->cache->set($cacheKey, $content, $cacheTTL);
        }

        return $content;
    }

	/**
	 * @param $url
	 * @param int $count
	 * @param Campaign $campaign
	 * @param bool $cache
	 * @param int $offset
	 * @param int $daysBack
	 * @param string $noItemAction
	 * @param bool $sendOnlyUniqueItems
	 *
	 * @return array|mixed
	 * @throws Exception
	 */
    public static function getRemoteFeedItems($url, $count = 10, Campaign $campaign, $cache = false, $offset = 0, $daysBack = -1, $noItemAction = '', $sendOnlyUniqueItems = false)
    {
        $accessKey = sha1(sprintf('m:%s.c:%s.u:%s.c:%s.o:%s.d:%s.s:%s', __METHOD__, $campaign->campaign_uid, $url, $count, $offset, $daysBack, $campaign->send_at));

	    // 1.8.1 - mutex addition
	    if (!Yii::app()->mutex->acquire($accessKey, 30)) {
		    return array();
	    }
        
        if ($cache && ($items = Yii::app()->cache->get($accessKey)) !== false) {
	        Yii::app()->mutex->release($accessKey);
            return $items;
        }

	    $items = array();
	    if ($cache) {
		    $cacheTTL = !$campaign->getIsAutoresponder() ? MW_CACHE_TTL : 3600;
		    Yii::app()->cache->set($accessKey, $items, $cacheTTL);
	    }
	    
        $response = AppInitHelper::simpleCurlGet($url);
        if ($response['status'] == 'error' || empty($response['message'])) {
	        Yii::app()->mutex->release($accessKey);
            return $items;
        }

        $json = @CJSON::decode($response['message'], false);

        if (empty($json)) {
	        Yii::app()->mutex->release($accessKey);
            return $items;
        }

        $offset = (int)$offset;
        $index  = 0;
        foreach($json as $item) {
            
            // 1.5.1
            if ($daysBack >= 0 && strtotime((string)$item->pubDate) < strtotime('-' . $daysBack . ' days')) {
                continue;
            }
          
            $index++;
            
            // 1.5.1
            if ($offset > 0 && $offset >= $index) {
                continue;
            }
            
            if (count($items) >= $count) {
                break;
            }

            $itemMap = array(
                'title'         => null,
                'description'   => null,
                'content'       => null,
                'image'         => null,
                'link'          => null,
                'pubDate'       => null,
                'guid'          => null,
            );

            if (!empty($item->title)) {
                $itemMap['title'] = (string)$item->title;
            }

            if (!empty($item->description)) {
                $itemMap['description'] = (string)$item->description;
            }

            if (!empty($item->content)) {
                $itemMap['content'] = (string)$item->content;
            }

            if (!empty($item->image)) {
                $itemMap['image'] = (string)$item->image;
            }

            if (!empty($item->link)) {
                $itemMap['link'] = (string)$item->link;
            }

            if (!empty($item->pubDate)) {
                $itemMap['pubDate'] = (string)$item->pubDate;
            }

            if (!empty($item->guid)) {
                $itemMap['guid'] = (string)$item->guid;
            }

            $itemMap = array_map(array('CHtml', 'decode'), $itemMap);
            // $itemMap = array_map(array('CHtml', 'encode'), $itemMap);

	        // 1.7.4
	        if ($sendOnlyUniqueItems) {
		        // make sure the item hasn't been served. If it has, skip it
		        $itemCacheKey = (int)$campaign->list_id . ':' . (int)$campaign->segment_id . ':' . sha1(serialize($itemMap));
		        if (!$campaign->getIsRecurring()) {
			        $itemCacheKey .= ':' . $campaign->campaign_id;
		        }
		        if (Yii::app()->cache->get($itemCacheKey)) {
			        continue;
		        }
		        Yii::app()->cache->set($itemCacheKey, true);
	        }
	        
            $items[] = $itemMap;
        }
        
        // 1.5.1
        if (empty($items) && $noItemAction == 'postpone-campaign' && $campaign->getIsProcessing()) {
            $campaign->saveSendAt(new CDbExpression('DATE_ADD(NOW(), INTERVAL 1 DAY)'));
	        Yii::app()->mutex->release($accessKey);
            throw new Exception('Rescheduling the campaign because no feed item was found!', 95);
        }
        //
        
        if ($cache) {
            $cacheTTL = !$campaign->getIsAutoresponder() ? MW_CACHE_TTL : 3600;
            Yii::app()->cache->set($accessKey, $items, $cacheTTL);
        }

	    Yii::app()->mutex->release($accessKey);
        
        return $items;
    }
}
