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
        $user       = Yii::app()->user->getModel();
        
        $supportUrl = Yii::app()->options->get('system.common.support_url');
        if ($supportUrl === null) {
            $supportUrl = MW_SUPPORT_KB_URL;
        }

        $menuItems = array(
            'support' => array(
                'name'        => Yii::t('app', 'Support'),
                'icon'        => 'glyphicon-question-sign',
                'active'      => '',
                'route'       => $supportUrl,
                'linkOptions' => array('target' => '_blank'),
            ),
            'dashboard' => array(
                'name'      => Yii::t('app', 'Dashboard'),
                'icon'      => 'glyphicon-dashboard',
                'active'    => 'dashboard',
                'route'     => array('dashboard/index'),
            ),
            'users' => array(
                'name'      => Yii::t('app', 'Users'),
                'icon'      => 'glyphicon-user',
                'active'    => array('users', 'user_groups'),
                'route'     => null,
                'items'     => array(
                    array('url' => array('users/index'), 'label' => Yii::t('app', 'Users'), 'active' => strpos($route, 'users') === 0),
                    array('url' => array('user_groups/index'), 'label' => Yii::t('app', 'Groups'), 'active' => strpos($route, 'user_groups') === 0),
                ),
            ),
            'customers' => array(
                'name'      => Yii::t('app', 'Customers'),
                'icon'      => 'fa-users',
                'active'    => array('customer', 'campaign', 'list', 'survey'),
                'route'     => null,
                'items'     => array(
                    array('url' => array('customers/index'), 'label' => Yii::t('app', 'Customers'), 'active' => strpos($route, 'customers') === 0 && strpos($route, 'customers_mass_emails') === false),
                    array('url' => array('customer_groups/index'), 'label' => Yii::t('app', 'Groups'), 'active' => strpos($route, 'customer_groups') === 0),
                    array('url' => array('lists/index'), 'label' => Yii::t('app', 'Lists'), 'active' => strpos($route, 'lists') === 0),
                    array('url' => array('campaigns/index'), 'label' => Yii::t('app', 'All campaigns'), 'active' => $route == 'campaigns/index'),
                    array('url' => array('campaigns/regular'), 'label' => Yii::t('app', 'Regular campaigns'), 'active' => $route == 'campaigns/regular'),
                    array('url' => array('campaigns/autoresponder'), 'label' => Yii::t('app', 'Autoresponders'), 'active' => $route == 'campaigns/autoresponder'),
                    array('url' => array('surveys/index'), 'label' => Yii::t('app', 'Surveys'), 'active' => strpos($route, 'surveys') === 0),
                    array('url' => array('customers_mass_emails/index'), 'label' => Yii::t('app', 'Mass emails'), 'active' => strpos($route, 'customers_mass_emails') === 0),
                    array('url' => array('customer_messages/index'), 'label' => Yii::t('app', 'Messages'), 'active' => strpos($route, 'customer_messages') === 0),
                    array('url' => array('customer_login_logs/index'), 'label' => Yii::t('app', 'Login logs'), 'active' => strpos($route, 'customer_login_logs') === 0),
                ),
            ),
            'monetization' => array(
                'name'      => Yii::t('app', 'Monetization'),
                'icon'      => 'glyphicon-credit-card',
                'active'    => array('payment_gateway', 'price_plans', 'orders', 'promo_codes', 'currencies', 'taxes'),
                'route'     => null,
                'items'     => array(
                    array('url' => array('payment_gateways/index'), 'label' => Yii::t('app', 'Payment gateways'), 'active' => strpos($route, 'payment_gateway') === 0),
                    array('url' => array('price_plans/index'), 'label' => Yii::t('app', 'Price plans'), 'active' => strpos($route, 'price_plans') === 0),
                    array('url' => array('orders/index'), 'label' => Yii::t('app', 'Orders'), 'active' => strpos($route, 'orders') === 0),
                    array('url' => array('promo_codes/index'), 'label' => Yii::t('app', 'Promo codes'), 'active' => strpos($route, 'promo_codes') === 0),
                    array('url' => array('currencies/index'), 'label' => Yii::t('app', 'Currencies'), 'active' => strpos($route, 'currencies') === 0),
                    array('url' => array('taxes/index'), 'label' => Yii::t('app', 'Taxes'), 'active' => strpos($route, 'taxes') === 0),
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
            'list-page-type' => array(
                'name'      => Yii::t('app', 'List page types'),
                'icon'      => 'glyphicon-list-alt',
                'active'    => 'list_page_type',
                'route'     => array('list_page_type/index'),
            ),
            'email-templates' => array(
                'name'      => Yii::t('app', 'Email templates'),
                'icon'      => 'glyphicon-text-width',
                'active'    => array('email_templates_categories', 'email_templates_gallery'),
                'route'     => null,
                'items'     => array(
                    array('url' => array('email_templates_categories/index'), 'label' => Yii::t('app', 'Categories'), 'active' => strpos($route, 'email_templates_categories') === 0),
                    array('url' => array('email_templates_gallery/index'), 'label' => Yii::t('app', 'Gallery'), 'active' => strpos($route, 'email_templates_gallery') === 0),
                ),
            ),
            'blacklist' => array(
                'name'      => Yii::t('app', 'Email blacklist'),
                'icon'      => 'glyphicon-ban-circle',
                'active'    => array('email_blacklist', 'block_email_request'),
                'route'     => null,
                'items'     => array(
                    array('url' => array('email_blacklist/index'), 'label' => Yii::t('app', 'Email blacklist'), 'active' => $route == 'email_blacklist' || strpos($route, 'email_blacklist/') === 0),
                    array('url' => array('email_blacklist_monitors/index'), 'label' => Yii::t('app', 'Blacklist monitors'), 'active' => strpos($route, 'email_blacklist_monitors') === 0),
                    array('url' => array('block_email_request/index'), 'label' => Yii::t('app', 'Block email requests'), 'active' => strpos($route, 'block_email_request') === 0),
                ),
            ),
            'extend' => array(
                'name'      => Yii::t('app', 'Extend'),
                'icon'      => 'glyphicon-plus-sign',
                'active'    => array('extensions', 'theme', 'languages', 'ext'),
                'route'     => null,
                'items'     => array(
                    array('url' => array('extensions/index'), 'label' => Yii::t('app', 'Extensions'), 'active' => strpos($route, 'ext') === 0),
                    array('url' => array('theme/index'), 'label' => Yii::t('app', 'Themes'), 'active' => strpos($route, 'theme') === 0),
                    array('url' => array('languages/index'), 'label' => Yii::t('app', 'Languages'), 'active' => strpos($route, 'languages') === 0),
                ),
            ),
            'locations' => array(
                'name'      => Yii::t('app', 'Locations'),
                'icon'      => 'glyphicon-globe',
                'active'    => array('ip_location_services', 'maxmind', 'countries', 'zones'),
                'route'     => null,
                'items'     => array(
                    array('url' => array('ip_location_services/index'), 'label' => Yii::t('app', 'Ip location services'), 'active' => strpos($route, 'ip_location_services') === 0),
                    array('url' => array('maxmind/index'), 'label' => Yii::t('app', 'Maxmind Database'), 'active' => strpos($route, 'maxmind') === 0),
                    array('url' => array('countries/index'), 'label' => Yii::t('app', 'Countries'), 'active' => strpos($route, 'countries') === 0),
                    array('url' => array('zones/index'), 'label' => Yii::t('app', 'Zones'), 'active' => strpos($route, 'zones') === 0),
                ),
            ),
            'articles' => array(
                'name'      => Yii::t('app', 'Articles'),
                'icon'      => 'glyphicon-book',
                'active'    => 'article',
                'route'     => null,
                'items'     => array(
                    array('url' => array('articles/index'), 'label' => Yii::t('app', 'View all articles'), 'active' => strpos($route, 'articles/index') === 0),
                    array('url' => array('article_categories/index'), 'label' => Yii::t('app', 'View all categories'), 'active' => strpos($route, 'article_categories') === 0),
                ),
            ),
            'pages' => array(
                'name'      => Yii::t('app', 'Pages'),
                'icon'      => 'glyphicon-file',
                'active'    => 'pages',
                'route'     => array('pages/index'),
            ),
            'settings' => array(
                'name'      => Yii::t('app', 'Settings'),
                'icon'      => 'glyphicon-cog',
                'active'    => array('settings', 'start_pages', 'common_email_templates'),
                'route'     => null,
                'items'     => array(
                    array('url' => array('settings/index'), 'label' => Yii::t('app', 'Common'), 'active' => strpos($route, 'settings/index') === 0),
                    array('url' => array('settings/system_urls'), 'label' => Yii::t('app', 'System urls'), 'active' => strpos($route, 'settings/system_urls') === 0),
                    array('url' => array('settings/import_export'), 'label' => Yii::t('app', 'Import/Export'), 'active' => strpos($route, 'settings/import_export') === 0),
                    array('url' => array('settings/email_templates'), 'label' => Yii::t('app', 'Email templates'), 'active' => strpos($route, 'settings/email_templates') === 0 || strpos($route, 'common_email_templates') === 0),
                    array('url' => array('settings/cron'), 'label' => Yii::t('app', 'Cron'), 'active' => strpos($route, 'settings/cron') === 0),
                    array('url' => array('settings/email_blacklist'), 'label' => Yii::t('app', 'Email blacklist'), 'active' => strpos($route, 'settings/email_blacklist') === 0),
                    array('url' => array('settings/campaign_attachments'), 'label' => Yii::t('app', 'Campaigns'), 'active' => strpos($route, 'settings/campaign_') === 0),
                    array('url' => array('settings/customer_common'), 'label' => Yii::t('app', 'Customers'), 'active' => strpos($route, 'settings/customer_') === 0),
	                array('url' => array('settings/2fa'), 'label' => Yii::t('app', '2FA'), 'active' => strpos($route, 'settings/2fa') === 0),
                    array('url' => array('settings/api'), 'label' => Yii::t('app', 'Api'), 'active' => strpos($route, 'settings/api') === 0),
                    array('url' => array('start_pages/index'), 'label' => Yii::t('app', 'Start pages'), 'active' => strpos($route, 'start_pages') === 0),
                    array('url' => array('settings/monetization'), 'label' => Yii::t('app', 'Monetization'), 'active' => strpos($route, 'settings/monetization') === 0),
                    array('url' => array('settings/customization'), 'label' => Yii::t('app', 'Customization'), 'active' => strpos($route, 'settings/customization') === 0),
                    array('url' => array('settings/cdn'), 'label' => Yii::t('app', 'CDN'), 'active' => strpos($route, 'settings/cdn') === 0),
                    array('url' => array('settings/spf_dkim'), 'label' => Yii::t('app', 'SPF/DKIM'), 'active' => strpos($route, 'settings/spf_dkim') === 0),
                    array('url' => array('settings/license'), 'label' => Yii::t('app', 'License'), 'active' => strpos($route, 'settings/license') === 0),
                    array('url' => array('settings/social_links'), 'label' => Yii::t('app', 'Social links'), 'active' => strpos($route, 'settings/social_links') === 0),
                ),
            ),
            'misc' => array(
                'name'      => Yii::t('app', 'Miscellaneous'),
                'icon'      => 'glyphicon-bookmark',
                'active'    => array('misc', 'transactional_emails', 'company_types', 'campaign_abuse_reports'),
                'route'     => null,
                'items'     => array(
                    array('url' => array('misc/campaigns_delivery_logs'), 'label' => Yii::t('app', 'Campaigns delivery logs'), 'active' => strpos($route, 'misc/campaigns_delivery_logs') === 0),
                    array('url' => array('misc/campaigns_bounce_logs'), 'label' => Yii::t('app', 'Campaigns bounce logs'), 'active' => strpos($route, 'misc/campaigns_bounce_logs') === 0),
                    array('url' => array('misc/campaigns_stats'), 'label' => Yii::t('app', 'Campaigns stats'), 'active' => strpos($route, 'misc/campaigns_stats') === 0),
                    array('url' => array('campaign_abuse_reports/index'), 'label' => Yii::t('app', 'Campaign abuse reports'), 'active' => strpos($route, 'campaign_abuse_reports/index') === 0),
                    array('url' => array('transactional_emails/index'), 'label' => Yii::t('app', 'Transactional emails'), 'active' => strpos($route, 'transactional_emails') === 0),
                    array('url' => array('misc/delivery_servers_usage_logs'), 'label' => Yii::t('app', 'Delivery servers usage logs'), 'active' => strpos($route, 'misc/delivery_servers_usage_logs') === 0),
                    array('url' => array('company_types/index'), 'label' => Yii::t('app', 'Company types'), 'active' => strpos($route, 'company_types') === 0),
                    array('url' => array('misc/application_log'), 'label' => Yii::t('app', 'Application log'), 'active' => strpos($route, 'misc/application_log') === 0),
                    array('url' => array('misc/emergency_actions'), 'label' => Yii::t('app', 'Emergency actions'), 'active' => strpos($route, 'misc/emergency_actions') === 0),
                    array('url' => array('misc/guest_fail_attempts'), 'label' => Yii::t('app', 'Guest fail attempts'), 'active' => strpos($route, 'misc/guest_fail_attempts') === 0),
                    array('url' => array('misc/cron_jobs_list'), 'label' => Yii::t('app', 'Cron jobs list'), 'active' => strpos($route, 'misc/cron_jobs_list') === 0),
                    array('url' => array('misc/cron_jobs_history'), 'label' => Yii::t('app', 'Cron jobs history'), 'active' => strpos($route, 'misc/cron_jobs_history') === 0),
                    array('url' => array('misc/phpinfo'), 'label' => Yii::t('app', 'PHP info'), 'active' => strpos($route, 'misc/phpinfo') === 0),
                    array('url' => array('misc/changelog'), 'label' => Yii::t('app', 'Changelog'), 'active' => strpos($route, 'misc/changelog') === 0),
                ),
            ),
            'store' => array(
                'name'        => Yii::t('app', 'Store'),
                'icon'        => 'glyphicon-shopping-cart',
                'active'      => 'store',
                'route'       => 'https://store.onetwist.com/index.php?product[]=mailwizz',
                'linkOptions' => array('target' => '_blank'),
            ),
        );

        if ($supportUrl == '') {
            unset($menuItems['support']);
        }
        
        if (!Yii::app()->params['store.enabled']) {
            unset($menuItems['store']);
        }

        $menuItems = (array)Yii::app()->hooks->applyFilters('backend_left_navigation_menu_items', $menuItems);

        // since 1.3.5
        foreach ($menuItems as $key => $data) {
            if (!empty($data['route']) && !$user->hasRouteAccess($data['route'])) {
                unset($menuItems[$key]);
                continue;
            }
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $index => $item) {
                    if (isset($item['url']) && !$user->hasRouteAccess($item['url'])) {
                        unset($menuItems[$key]['items'][$index], $data['items'][$index]);
                    }
                }
            }
            if (empty($data['route']) && empty($data['items'])) {
                unset($menuItems[$key]);
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
