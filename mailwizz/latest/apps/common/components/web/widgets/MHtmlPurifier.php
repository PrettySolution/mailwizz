<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * MHtmlPurifier
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class MHtmlPurifier extends CHtmlPurifier
{
    // inherited
    protected function createNewHtmlPurifierInstance()
    {
        // custom classes for html purifier
        Yii::import('common.components.htmlpurifier.*');

        $purifier = parent::createNewHtmlPurifierInstance();
        $this->adjustConfiguration($purifier);
        return $purifier;
    }

    protected function adjustConfiguration(HTMLPurifier $purifier)
    {
        $options = require Yii::getPathOfAlias('common.config.htmlpurifier').'.php';

        if (is_file($_customOptionsFile = Yii::getPathOfAlias('common.config.htmlpurifier-custom').'.php')) {
            $options = CMap::mergeArray($options, require $_customOptionsFile);
        }

        $config = $purifier->config;

        foreach ($options as $directive => $value) {
            $config->set($directive, $value);
        }

        $this->setup($config);

        $this->onAdjustConfiguration(new CEvent($this, array(
            'purifier' => $purifier,
            'config'   => $config,
            'instance' => $this,
            'options'  => $options,
        )));

        Yii::app()->hooks->doAction('htmlpurifier_adjust_configuration', new CAttributeCollection(array(
            'purifier' => $purifier,
            'config'   => $config,
            'instance' => $this,
            'options'  => $options,
        )));
    }

    public function onAdjustConfiguration(CEvent $event)
    {
        $this->raiseEvent('onAdjustConfiguration', $event);
    }

    protected function setup(HTMLPurifier_Config $config)
    {
        // URI:
        // $uri = $config->getDefinition('URI');
        // $uri->addFilter(new HTMLPurifier_URIFilter_HostCustomFieldTag(), $config);
        // $uri->addFilter(new HTMLPurifier_URIFilter_HostCustomFieldTagPost(), $config);

        // CSS Definition
        $cssDefinition = $config->getCSSDefinition();
        $info = array();

        // New Set
        // all browsers
        $borderRadius =
        $info['border-top-left-radius'] =
        $info['border-top-right-radius'] =
        $info['border-bottom-left-radius'] =
        $info['border-bottom-right-radius'] =
        new HTMLPurifier_AttrDef_CSS_Composite(array(
            new HTMLPurifier_AttrDef_CSS_Length('0'),
            new HTMLPurifier_AttrDef_CSS_Percentage(true)
        ));
        $info['border-radius'] = new HTMLPurifier_AttrDef_CSS_Multiple($borderRadius);

        // webkit specific
        $borderRadius =
        $info['-webkit-border-top-left-radius'] =
        $info['-webkit-border-top-right-radius'] =
        $info['-webkit-border-bottom-left-radius'] =
        $info['-webkit-border-bottom-right-radius'] =
        new HTMLPurifier_AttrDef_CSS_Composite(array(
            new HTMLPurifier_AttrDef_CSS_Length('0'),
            new HTMLPurifier_AttrDef_CSS_Percentage(true)
        ));
        $info['-webkit-border-radius'] = new HTMLPurifier_AttrDef_CSS_Multiple($borderRadius);

        // mozilla specific
        $borderRadius =
        $info['-moz-border-top-left-radius'] =
        $info['-moz-border-top-right-radius'] =
        $info['-moz-border-bottom-left-radius'] =
        $info['-moz-border-bottom-right-radius'] =
        new HTMLPurifier_AttrDef_CSS_Composite(array(
            new HTMLPurifier_AttrDef_CSS_Length('0'),
            new HTMLPurifier_AttrDef_CSS_Percentage(true)
        ));
        $info['-moz-border-radius'] = new HTMLPurifier_AttrDef_CSS_Multiple($borderRadius);

        // New Set
        $trustedWh = new HTMLPurifier_AttrDef_CSS_Composite(
            array(
                new HTMLPurifier_AttrDef_CSS_Length('0'),
                new HTMLPurifier_AttrDef_CSS_Percentage(true),
                new HTMLPurifier_AttrDef_Enum(array('auto'))
            )
        );
        $max = $config->get('CSS.MaxImgLength');

        $info['min-width'] =
        $info['max-width'] =
        $info['min-height'] =
        $info['max-height'] =
        $max === null ? $trustedWh : new HTMLPurifier_AttrDef_Switch(
            'img',
            // For img tags:
            new HTMLPurifier_AttrDef_CSS_Composite(
                array(
                    new HTMLPurifier_AttrDef_CSS_Length('0', $max),
                    new HTMLPurifier_AttrDef_CSS_Percentage(true),
                    new HTMLPurifier_AttrDef_Enum(array('auto'))
                )
            ),
            // For everyone else:
            $trustedWh
        );

        $info['background-size'] = new HTMLPurifier_AttrDef_Enum(
            array('auto', 'cover', 'contain', 'initial', 'inherit')
        );

        $info['position'] = new HTMLPurifier_AttrDef_Enum(
            array('absolute', 'relative', 'static', 'fixed', 'none')
        );

        $info['top'] = $info['right'] = $info['bottom'] =  $info['left'] = $trustedWh;

        // wrap all new attr-defs with decorator that handles !important
        $allowImportant = $config->get('CSS.AllowImportant');
        foreach ($info as $k => $v) {
            $cssDefinition->info[$k] = new HTMLPurifier_AttrDef_CSS_ImportantDecorator($v, $allowImportant);
        }

        // Html Definition
        if (!($def = $config->maybeGetRawHTMLDefinition())) {
            return $this;
        }

        // http://developers.whatwg.org/sections.html
        $def->addElement('section', 'Block', 'Flow', 'Common');
        $def->addElement('nav',     'Block', 'Flow', 'Common');
        $def->addElement('article', 'Block', 'Flow', 'Common');
        $def->addElement('aside',   'Block', 'Flow', 'Common');
        $def->addElement('header',  'Block', 'Flow', 'Common');
        $def->addElement('footer',  'Block', 'Flow', 'Common');

        // Content model actually excludes several tags, not modelled here
        $def->addElement('address', 'Block', 'Flow', 'Common');
        $def->addElement('hgroup', 'Block', 'Required: h1 | h2 | h3 | h4 | h5 | h6', 'Common');

        // http://developers.whatwg.org/grouping-content.html
        $def->addElement('figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common');
        $def->addElement('figcaption', 'Inline', 'Flow', 'Common');

        // http://developers.whatwg.org/the-video-element.html#the-video-element
        $def->addElement('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', array(
            'src'       => 'URI',
            'type'      => 'Text',
            'width'     => 'Length',
            'height'    => 'Length',
            'poster'    => 'URI',
            'preload'   => 'Enum#auto,metadata,none',
            'controls'  => 'Text',
            'autoplay'  => 'Text',
        ));

        $def->addElement('source', 'Block', 'Flow', 'Common', array(
            'src'  => 'URI',
            'type' => 'Text',
        ));

        $def->addElement('track', 'Block', 'Flow', 'Common', array(
            'src'       => 'URI',
            'label'     => 'Text',
            'kind'      => 'Text',
            'srclang'   => 'Text',
            'default'   => 'Bool',
        ));

        // http://developers.whatwg.org/text-level-semantics.html
        $def->addElement('s',    'Inline', 'Inline', 'Common');
        $def->addElement('var',  'Inline', 'Inline', 'Common');
        $def->addElement('sub',  'Inline', 'Inline', 'Common');
        $def->addElement('sup',  'Inline', 'Inline', 'Common');
        $def->addElement('mark', 'Inline', 'Inline', 'Common');
        $def->addElement('wbr',  'Inline', 'Empty', 'Core');

        // http://developers.whatwg.org/edits.html
        $def->addElement('ins', 'Block', 'Flow', 'Common', array('cite' => 'URI', 'datetime' => 'CDATA'));
        $def->addElement('del', 'Block', 'Flow', 'Common', array('cite' => 'URI', 'datetime' => 'CDATA'));

        // map / area - start
        $def->addAttribute('img', 'usemap', 'CDATA');

        // Add map tag
        $map = $def->addElement('map', 'Block', 'Flow', 'Common', array('name' => 'CDATA', 'id' => 'ID', 'title' => 'CDATA'));
        $map->excludes = array('map' => true);

        // Add area tag
        $area = $def->addElement(
            'area', 'Block', 'Empty', 'Common', array(
                'name'      => 'CDATA',
                'id'        => 'ID',
                'alt'       => 'Text',
                'coords'    => 'CDATA',
                'accesskey' => 'Character',
                'nohref'    => new HTMLPurifier_AttrDef_Enum(array('nohref')),
                'href'      => 'URI',
                'shape'     => new HTMLPurifier_AttrDef_Enum(array('rect', 'circle', 'poly', 'default')),
                'tabindex'  => 'Number',
                'target'    => new HTMLPurifier_AttrDef_Enum(array('_blank', '_self', '_target', '_top'))
            )
        );
        $area->excludes = array('area' => true);
        // map / area - end

        // Others
        $def->addAttribute('iframe', 'allowfullscreen', 'Bool');
        $def->addAttribute('table', 'height', 'Text');
        $def->addAttribute('td', 'border', 'Text');
        $def->addAttribute('th', 'border', 'Text');
        $def->addAttribute('tr', 'width', 'Text');
        $def->addAttribute('tr', 'height', 'Text');
        $def->addAttribute('tr', 'border', 'Text');
        $def->addAttribute('hr', 'color', 'Text');
        
        // data attributes
        $dataAttributes = array(
            'width', 'height', 'type', 'info', 'dismiss', 'url', 'post', 'mce-src',
            'mce-json', 'json', 'default', 'id', 'uid', 'toggle', 'target',
            'original-title', 'color', 'clonable', 'time', 'date', 'time-start', 'time-end',
            'misc', 'common', 'details', 'description', 'status', 'border', 'image', 'original', 'clone',
            'ko-block', 'ko-container', 'ko-display', 'ko-wrap', 'ko-editable', 'ko-placeholder-height', 'ko-placeholder-width',
            'ko-link'
        );
        $dataElements   = array('div', 'table', 'td', 'p', 'span', 'a', 'img');
        foreach ($dataElements as $elem) {
            foreach ($dataAttributes as $attr) {
                $def->addAttribute($elem, 'data-' . $attr, 'Text');
            }
        }
        
        // since 1.3.6.3
        $ariaAttributes = array('labelledby', 'label', 'live');
        foreach ($dataElements as $elem) {
            foreach ($ariaAttributes as $attr) {
                $def->addAttribute($elem, 'aria-' . $attr, 'Text');
            }
        }
        $others = array('role');
        foreach ($dataElements as $elem) {
            foreach ($others as $other) {
                $def->addAttribute($elem, $other, 'Text');
            }
        }
        //
        
        return $this;
    }
}
