/**
 * 
 * @param params
 * @returns {{open: open, close: close, toggle: toggle}}
 * @constructor
 */
window.TemplateBuilderHandler = function(params){

    /**
     * Defaults
     * @type {{builderId: string, options: {}, instance: {}, enabled: boolean, json: {}}}
     */
    var defaults = {
        builderId : '',
        options   : {},
        instance  : {},
        enabled   : false,
        json      : {}
    };

    var builder = $.extend({}, defaults, params);
    builder.instance = new TemplateBuilder(builder.options);
    
    var $builderWrapper = $('#builder_' + builder.builderId);
    
    var methods = {
        open: function(){
            
            $builderWrapper.trigger('templateBuilderHandler.beforeOpen');
            
            $builderWrapper
                .closest('.form-group')
                .css({
                    position: 'relative'
                });
            
            $builderWrapper.css({
                width       : '100%',
                height      : $('#cke_' + builder.builderId).height() + 5,
                position    : 'absolute',
                background  : '#fff',
                border      : '1px solid #c2c2c2',
                top         : '32px'
            });
            
            if (!$.isEmptyObject(builder.json)) {
                if (typeof(builder.json) === 'object') {
                    builder.instance.mountBuilder(builder.json);
                } else {
                    builder.instance.setJson(builder.json);
                }
            } else {
                builder.instance.mountBuilder();
            }
            
            Cookies.set('builder_status', 'open', { expires: 365, path: '/' });
            builder.enabled = true;

            $builderWrapper.trigger('templateBuilderHandler.afterOpen');
        },
        close: function(){
            $builderWrapper.trigger('templateBuilderHandler.beforeClose');
            
            builder.json = builder.instance.getJson();
            builder.instance.unmountBuilder();
            $builderWrapper.attr('style', '');
            Cookies.set('builder_status', 'closed', { expires: 365, path: '/' });
            builder.enabled = false;
            
            $builderWrapper.trigger('templateBuilderHandler.afterClose', [ {
                json: builder.json
            } ]);
        },
        toggle: function(){
            if (!builder.builderId) {
                return false;
            }
            if (builder.enabled) {
                methods.close();
            } else {
                methods.open();
            }
            return false;
        },
        shouldOpen: function(){
            return Cookies.get('builder_status') === 'open';
        },
        getInstance: function(){
            return builder.instance;
        },
        isEnabled: function(){
            return builder.enabled;
        }
    };
    
    return methods;
};