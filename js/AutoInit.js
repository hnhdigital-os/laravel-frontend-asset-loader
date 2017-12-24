
// Auto trigger intialization.
$(function() {
    $('[class*="init-"]:visible').each(function(key, element) {
        result = $.grep($(element).attr('class').split(' '), function(s) { return s.match(new RegExp('init-')) });
        result.forEach(function(class_name) {
            $(element).trigger('extension::'+class_name.replace('init-', '')+'::init');
        });
    });

    $('body').on('extensions::init', findAndInit);
    $('ul.nav-tabs a').on('shown.bs.tab', findAndInit);
});

function findAndInit(event, restrict_search) {
    $('[class*="init-"]:visible').each(function(key, element) {
        result = $.grep($(element).attr('class').split(' '), function(s) { return s.match(new RegExp('init-')) });
        result.forEach(function(class_name) {

            if (typeof restrict_search == 'object') {
                result = $(restrict_search).find('.' + class_name);
            } else if (typeof restrict_search == 'string') {
                result = $(restrict_search + ' .' + class_name)
            } else {
                result = $(' .' + class_name);
            }

            result.each(function(key, element) {
                $(element).trigger('extension::'+class_name.replace('init-', '')+'::init');
            });
        });
    });
}

var findAndApplyScriptExtensions = findAndInit;
