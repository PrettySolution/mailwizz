jQuery(document).ready(function($){
    
    (function(){
        var map, 
            $container = $('#activity-map-container'),
            $map = $('#map'), 
            $spinner = $('.btn-spinner-xs-right'),
            $mapMessages = $('.map-messages'),
            $mapMessageLoading = $('.loading-message', $mapMessages),
            $mapMessageDoneLoading = $('.done-loading', $mapMessages),
            translate = $map.data('translate'),
            pagesCount = -1, 
            currentPage = 1,
            requestObjects = [],
            requestCounter = 0,
            fullscreenAnimation = false;
        
        function initMap() {
            $map.show();
            map = new GMaps({
                div: $map[0],
                lat: 0,
                lng: 0,
                zoom: $map.data('zoom') ? $map.data('zoom') : 1,
                markerClusterer: function(map) {
                    return new MarkerClusterer(map, [], $map.data('markerclusterer'));
                }
            });
            if (requestObjects.length) {
                for (var i in requestObjects) {
                    if (requestObjects[i]) {
                        requestObjects[i].abort();
                    }
                }
                requestObjects = [];
                requestCounter = 0;
            }
        }
        
        function getResults(url, pageNumber) {
            pageNumber = pageNumber || 1;
            
            $mapMessageDoneLoading.hide();
            $mapMessageLoading.show().find('span').text(pageNumber);
            $spinner.toggleClass('active');
            
            requestObjects[requestCounter] = $.get(url, {page: pageNumber}, function(json) {
                $spinner.toggleClass('active');
                if (!json.results || typeof json.results != 'object' || !json.results.length) {
                    $mapMessageLoading.hide();
                    $mapMessageDoneLoading.show();
                    return;
                }
                for (var i in json.results) {
                    if (!requestObjects[requestCounter]) {
                        $mapMessageLoading.hide();
                        $mapMessageDoneLoading.show();
                        break;
                    }
                    
                    var content = [];
                    content.push(translate.email + ': ' + json.results[i].email);
                    content.push(translate.ip + ': ' + json.results[i].ip_address + ' ('+ json.results[i].location +')');
                    content.push(translate.device + ': ' + json.results[i].device);
                    content.push(translate.date + ': ' + json.results[i].date_added);
                    
                    map.addMarker({
                        lat: json.results[i].latitude,
                        lng: json.results[i].longitude,
                        title: json.results[i].email,
                        infoWindow: {
                            content: content.join("<br />")
                        } 
                    });
                }
                if (json.pages_count > 0 && json.current_page < json.pages_count) {
                    setTimeout(function(){
                        getResults(url, json.current_page * 1 + 1);
                    }, 100);
                } else {
                    $mapMessageLoading.hide();
                    $mapMessageDoneLoading.show();
                }
                ++requestCounter;
            }, 'json');
        }

        $('#map-opens').on('click', function(){
            initMap();
            getResults($(this).attr('href'), 1);
            $('#map-opens').addClass('btn-primary').removeClass('btn-default');
            $('#map-clicks, #map-unsubscribes').removeClass('btn-primary').addClass('btn-default');
            return false;
        });
        
        $('#map-clicks').on('click', function(){
            initMap();
            getResults($(this).attr('href'), 1);
            $('#map-clicks').addClass('btn-primary').removeClass('btn-default');
            $('#map-opens, #map-unsubscribes').removeClass('btn-primary').addClass('btn-default');
            return false;
        });
        
        $('#map-unsubscribes').on('click', function(){
            initMap();
            getResults($(this).attr('href'), 1);
            $('#map-unsubscribes').addClass('btn-primary').removeClass('btn-default');
            $('#map-opens, #map-clicks').removeClass('btn-primary').addClass('btn-default');
            return false;
        });
        
        $('#enter-exit-fullscreen').on('click', function(){
            if (fullscreenAnimation) {
                return false;
            }
            fullscreenAnimation = true;
            var $this = $(this);
            
            // not in full screen, entering.
            if (!$container.data('fullscreen')) {
                var top = $container.offset().top - $(window).scrollTop();
                var left= $container.offset().left;
                var width = $(window).width();
                var height = $(window).height();
                
                var animateProps = {
                    top: 0, 
                    left: 0, 
                    padding: 0, 
                    margin: 0,
                    width: width,
                    height: height
                };

                $container.data({
                    top: top, 
                    left: left
                }).css({
                    top: top,
                    left: left,
                    zIndex: 9999,
                    position: 'fixed'
                }).animate(animateProps, 550, function(){
                    $container.data('fullscreen', true);
                    fullscreenAnimation = false;
                    $this.text($this.data('exit'));
                });
                
                $map.data('height', $map.height()).animate({
                    height: height - 120
                }, 500, function(){
                    $map = $container.find('#map').data('zoom', 2);
                    var $selected = $('#map-links a.btn-primary');
                    if ($selected.length > 0) {
                        $selected.trigger('click');
                    }
                });
                
            } else {
                
                var animateProps = {
                    top: $container.data('top'), 
                    left: $container.data('left'), 
                    width: $container.data('width'),
                    height: $container.data('height')
                };

                $container.data({
                    top: 0, 
                    left: 0
                }).animate(animateProps, 550, function(){
                    $container.data('fullscreen', false).removeAttr('style');
                    fullscreenAnimation = false;
                    $this.text($this.data('enter'));
                });
                
                $map.animate({
                    height: $map.data('height')
                }, 500, function(){
                    $map = $container.find('#map').data('zoom', 1);
                    var $selected = $('#map-links a.btn-primary');
                    if ($selected.length > 0) {
                        $selected.trigger('click');
                    }
                });
            }
            
            return false;
        });
        
        $(window).on('resize', function(){
            if ($container.data('fullscreen')) {
                $('#enter-exit-fullscreen').trigger('click');
            }
        });
        
        
    })();
});