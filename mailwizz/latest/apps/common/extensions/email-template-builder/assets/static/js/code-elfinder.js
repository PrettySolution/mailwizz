jQuery(document).ready(function($){
    $('#elfinder').on('ext.ckeditor.elfinder.js_options.get_file_callback.start', function(e, options) {
        
        if (!window.opener) {
            return false;
        }
        
        if (!window.imageSrc && !window.imageBgc && !window.fileSrc) {
            return false;
        }

        if (window.imageSrc && window.opener.document.getElementById(window.imageSrc)) {
            window.opener.document.getElementById(window.imageSrc).src = options.file.url;
        }

        if (window.imageBgc && window.opener.document.querySelector('.' + window.imageBgc)) {
            window.opener.document.querySelector('.' + window.imageBgc).style.backgroundImage = "url('" + options.file.url + "')";
        }

        if (window.fileSrc) {
            if (window.opener.document.getElementById(window.fileSrc)) {
                window.opener.document.getElementById(window.fileSrc).value = options.file.url;
            }
            if (window.opener.document.getElementById(window.currentTemplate)) {
                window.opener.document.getElementById(window.currentTemplate).getElementsByTagName('A')[0].href = options.file.url;
            }
        }

        window.close();

    });
});