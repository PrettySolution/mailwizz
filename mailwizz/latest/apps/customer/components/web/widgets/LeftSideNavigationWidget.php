<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * LeftSideNavigationWidget
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class LeftSideNavigationWidget extends CWidget
{
    /**
     * @return array
     */
    public function getMenuItems()
    {
        $controller = $this->controller;
        $route      = $controller->route;
        $customer   = Yii::app()->customer->getModel();
        
        $menuItems = array(
            'dashboard' => array(
                'name'      => Yii::t('app', 'Dashboard'),
                'icon'      => 'glyphicon-dashboard',
                'active'    => 'dashboard',
                'route'     => array('dashboard/index'),
            ),
            'price_plans' => array(
                'name'      => Yii::t('app', 'Price plans'),
                'icon'      => 'glyphicon-credit-card',
                'active'    => 'price_plans',
                'route'     => null,
                'items'     => array(
                    array('url' => array('price_plans/index'), 'label' => Yii::t('app', 'Price plans'), 'active' => strpos($route, 'price_plans/index') === 0 || strpos($route, 'price_plans/payment') === 0),
                    array('url' => array('price_plans/orders'), 'label' => Yii::t('app', 'Orders history'), 'active' => strpos($route, 'price_plans/order') === 0),
                ),
            ),
            'lists' => array(
                'name'      => Yii::t('app', 'Lists'),
                'icon'      => 'glyphicon-list-alt',
                'active'    => array('list', 'email_blacklist', 'suppression_lists'),
                'route'     => null,
                'items'     => array(
                    array('url' => array('lists/index'), 'label' => Yii::t('app', 'Lists'), 'active' => strpos($route, 'lists') === 0 && strpos($route, 'lists_tools') === false),
                    array('url' => array('lists_tools/index'), 'label' => Yii::t('app', 'Tools'), 'active' => strpos($route, 'lists_tools') === 0),
                    array('url' => array('email_blacklist/index'), 'label' => Yii::t('app', 'Email blacklist'), 'active' => strpos($route, 'email_blacklist') === 0),
                    array('url' => array('suppression_lists/index'), 'label' => Yii::t('app', 'Suppression lists'), 'active' => strpos($route, 'suppression_lists') === 0),
                ),
            ),
            'campaigns' => array(
                'name'      => Yii::t('app', 'Campaigns'),
                'icon'      => 'fa-envelope',
                'active'    => 'campaign',
                'route'     => null,
                'items'     => array(
                    array('url' => array('campaigns/index'), 'label' => Yii::t('app', 'All campaigns'), 'active' => $route == 'campaigns/index'),
                    array('url' => array('campaigns/regular'), 'label' => Yii::t('app', 'Regular campaigns'), 'active' => $route == 'campaigns/regular'),
                    array('url' => array('campaigns/autoresponder'), 'label' => Yii::t('app', 'Autoresponders'), 'active' => $route == 'campaigns/autoresponder'),
                    array('url' => array('campaign_groups/index'), 'label' => Yii::t('app', 'Groups'), 'active' => strpos($route, 'campaign_groups') === 0),
                    array('url' => array('campaigns_stats/index'), 'label' => Yii::t('app', 'Stats'), 'active' => strpos($route, 'campaigns_stats') === 0),
                    array('url' => array('campaigns_geo_opens/index'), 'label' => Yii::t('app', 'Geo Opens'), 'active' => strpos($route, 'campaigns_geo_opens') === 0),
                    array('url' => array('campaign_tags/index'), 'label' => Yii::t('app', 'Custom tags'), 'active' => strpos($route, 'campaign_tags') === 0),
                ),
            ),
            'templates' => array(
                'name'      => Yii::t('app', 'Email templates'),
                'icon'      => 'glyphicon-text-width',
                'active'    => 'templates',
                'route'     => null,
                'items'     => array(
                    array('url' => array('templates_categories/index'), 'label' => Yii::t('app', 'Categories'), 'active' => strpos($route, 'templates_categories') === 0),
                    array('url' => array('templates/index'), 'label' => Yii::t('app', 'Templates'), 'active' => in_array($route, array('templates/index', 'templates/create', 'templates/update'))),
                    array('url' => array('templates/gallery'), 'label' => Yii::t('app', 'Gallery'), 'active' => strpos($route, 'templates/gallery') === 0),
                ),
            ),
            'servers'       => array(
                'name'      => Yii::t('app', 'Servers'),
                'icon'      => 'glyphicon-transfer',
                'active'    => array('delivery_servers', 'bounce_servers', 'feedback_loop_servers', 'email_box_monitors'),
                'route'     => null,
                'items'     => array(
                    array('url' => array('delivery_servers/index'), 'label' => Yii::t('app', 'Delivery servers'), 'active' => strpos($route, 'delivery_servers') === 0),
                    array('url' => array('bounce_servers/index'), 'label' => Yii::t('app', 'Bounce servers'), 'active' => strpos($route, 'bounce_servers') === 0),
                    array('url' => array('feedback_loop_servers/index'), 'label' => Yii::t('app', 'Feedback loop servers'), 'active' => strpos($route, 'feedback_loop_servers') === 0),
                    array('url' => array('email_box_monitors/index'), 'label' => Yii::t('app', 'Email box monitors'), 'active' => strpos($route, 'email_box_monitors') === 0),
                ),
            ),
            'domains' => array(
                'name'      => Yii::t('app', 'Domains'),
                'icon'      => 'glyphicon-globe',
                'active'    => array('sending_domains', 'tracking_domains'),
                'route'     => null,
                'items'     => array(
                    array('url' => array('sending_domains/index'), 'label' => Yii::t('app', 'Sending domains'), 'active' => strpos($route, 'sending_domains') === 0),
                    array('url' => array('tracking_domains/index'), 'label' => Yii::t('app', 'Tracking domains'), 'active' => strpos($route, 'tracking_domains') === 0),
                ),
            ),
            'api-keys' => array(
                'name'      => Yii::t('app', 'Api keys'),
                'icon'      => 'glyphicon-star',
                'active'    => 'api_keys',
                'route'     => array('api_keys/index'),
            ),
            'surveys' => array(
                'name'      => Yii::t('app', 'Surveys'),
                'icon'      => 'glyphicon-list',
                'active'    => 'surveys',
                'route'     => array('surveys/index'),
            ),
            'articles' => array(
                'name'      => Yii::t('app', 'Articles'),
                'icon'      => 'glyphicon-book',
                'active'    => 'article',
                'route'     => Yii::app()->apps->getAppUrl('frontend', 'articles', true),
                'items'     => array(),
            ),
            'settings' => array(
                'name'      => Yii::t('app', 'Settings'),
                'icon'      => 'glyphicon-cog',
                'active'    => 'settings',
                'route'     => null,
                'items'     => array(),
            ),
        );

        if (!Yii::app()->options->get('system.customer.action_logging_enabled', true)) {
            unset($menuItems['dashboard']);
        }

        $maxDeliveryServers  = $customer->getGroupOption('servers.max_delivery_servers', 0);
        $maxBounceServers    = $customer->getGroupOption('servers.max_bounce_servers', 0);
        $maxFblServers       = $customer->getGroupOption('servers.max_fbl_servers', 0);
        $maxEmailBoxMonitors = $customer->getGroupOption('servers.max_email_box_monitors', 0);

        if (!$maxDeliveryServers && !$maxBounceServers && !$maxFblServers && !$maxEmailBoxMonitors) {
            unset($menuItems['servers']);
        } else {
            foreach (array($maxDeliveryServers, $maxBounceServers, $maxFblServers, $maxEmailBoxMonitors) as $index => $value) {
                if (!$value && isset($menuItems['servers']['items'][$index])) {
                    unset($menuItems['servers']['items'][$index]);
                }
            }
        }

        if (SendingDomain::model()->getRequirementsErrors() || $customer->getGroupOption('sending_domains.can_manage_sending_domains', 'no') != 'yes') {
            unset($menuItems['domains']['items'][0]);
        }

        if ($customer->getGroupOption('tracking_domains.can_manage_tracking_domains', 'no') != 'yes') {
            unset($menuItems['domains']['items'][1]);
        }

        if ($customer->getGroupOption('lists.can_use_own_blacklist', 'no') != 'yes') {
            unset($menuItems['lists']['items'][2], $menuItems['lists']['items'][3]);
        }

        if ($customer->getGroupOption('common.show_articles_menu', 'no') != 'yes') {
            unset($menuItems['articles']);
        }

	    if ($customer->getGroupOption('surveys.max_surveys', -1) == 0) {
		    unset($menuItems['surveys']);
	    }

        if (count($menuItems['domains']['items']) == 0) {
            unset($menuItems['domains']);
        }

        if (Yii::app()->options->get('system.monetization.monetization.enabled', 'no') == 'no') {
            unset($menuItems['price_plans']);
        }

        if (Yii::app()->options->get('system.common.api_status') != 'online') {
            unset($menuItems['api-keys']);
        } elseif ($customer->getGroupOption('api.enabled', 'yes') != 'yes') {
            unset($menuItems['api-keys']);
        }

        if ($customer->getGroupOption('campaigns.show_geo_opens', 'no') != 'yes') {
            unset($menuItems['campaigns']['items'][3]);
        }

        $menuItems = (array)Yii::app()->hooks->applyFilters('customer_left_navigation_menu_items', $menuItems);

        if (empty($menuItems['settings']['items'])) {
            unset($menuItems['settings']);
        }
        
        foreach ($menuItems as $name => $item) {
            if (!empty($item['route']) && is_array($item['route']) && !AccessHelper::hasRouteAccess($item['route'][0])) {
                unset($menuItems[$name]);
                continue;
            }
            if (!empty($item['items']) && is_array($item['items'])) {
                foreach ($item['items'] as $idx => $i) {
                    if (!is_array($i['url'])) {
                        continue;
                    }
                    if (!AccessHelper::hasRouteAccess($i['url'][0])) {
                        unset($menuItems[$name]['items'][$idx]);
                    }
                }
            }
        }
        
        return $menuItems;
    }

    /**
     * @throws CException
     */
    public function buildMenu()
    {
        $controller = $this->controller;
        $route      = $controller->route;

        Yii::import('zii.widgets.CMenu');
        
        $menu = new CMenu();
        $menu->htmlOptions          = array('class' => 'sidebar-menu');
        $menu->submenuHtmlOptions   = array('class' => 'treeview-menu');
        $menuItems                  = $this->getMenuItems();

        foreach ($menuItems as $key => $data) {
            $_route  = !empty($data['route']) ? $data['route'] : 'javascript:;';
            $active  = false;

            if (!empty($data['active']) && is_string($data['active']) && strpos($route, $data['active']) === 0) {
                $active = true;
            } elseif (!empty($data['active']) && is_array($data['active'])) {
                foreach ($data['active'] as $in) {
                    if (strpos($route, $in) === 0) {
                        $active = true;
                        break;
                    }
                }
            }

            $item = array(
                'url'         => $_route,
                'label'       => IconHelper::make($data['icon']) . ' <span>'.$data['name'].'</span>' . (!empty($data['items']) ? '<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i></span>' : ''),
                'active'      => $active,
                'linkOptions' => !empty($data['linkOptions']) && is_array($data['linkOptions']) ? $data['linkOptions'] : array(),
            );

            if (!empty($data['items'])) {
                foreach ($data['items'] as $index => $i) {
                    if (isset($i['label'])) {
                        $data['items'][$index]['label'] = '<i class="fa fa-circle-o text-primary"></i>' . $i['label'];
                    }
                }
                $item['items']       = $data['items'];
                $item['itemOptions'] = array('class' => 'treeview');
            }

            $menu->items[] = $item;
        }

        $menu->run();
    }

    /**
     * @return string
     */
    public function run()
    {
        return $this->buildMenu();
    }
}
