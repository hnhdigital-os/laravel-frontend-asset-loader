/**
 * Frontend Assets.
 *
 * @type {Object}
 */
$.frontendAssets = {
  /**
   * Stores the init functions.
   *
   * @type {Object}
   */
  scripts: {},

  /**
   * Startup.
   *
   * @type {Object}
   */
  startup: {},

  /**
   * Storage.
   *
   * @type {Object}
   */
  storage: {},

  /**
   * Auto init any extensions.
   *
   * @return void
   */
  autoInit: function() {
    $('[class*="init-"]:visible').each(function(key, element) {
      result = $.grep(
        $(element)
          .attr('class')
          .split(' '),
        function(s) {
          return s.match(new RegExp('init-'));
        }
      );
      result.forEach(function(class_name) {
        var extension = class_name.replace('init-', '');
        $(element).on(
            'extension::' + extension + '::init',
            $.frontendAssets.scripts[extension]
          );

        $(element).trigger(
          'extension::' + extension + '::init'
        );
      });
    });

    $('body').on('extensions::init', $.frontendAssets.init);
    $('ul.nav-tabs a').on('shown.bs.tab', $.frontendAssets.init);
  },

  register: function(extension, init_function, setup_function, default_storage) {
    $.frontendAssets.scripts[extension] = init_function;
    $.frontendAssets.startup[extension] = setup_function;
    if (typeof default_storage == 'undefined') {
      default_storage = {};
    }
    $.frontendAssets.storage[extension] = default_storage;
    $('.init-' + extension).on('extension::bs-tooltip::init', init_function);
  },

  captureTrigger: function(event_name) {
    if (typeof event_name == 'string' && event_name.match(new RegExp('^extension::(.*)::init$')) != null) {
      var extension = event_name.replace('init-', '');
      if (typeof $.frontendAssets.startup[extension] != 'undefined') {
        $.frontendAssets.startup[extension]();
        delete $.frontendAssets.startup[extension];
      }
    }
  },

  /**
   * Init any discovered extensions.
   *
   * @return void
   */
  init: function(event, restrict_search) {
    var search_criteria = '[class*="init-"]:visible';

    if (typeof restrict_search == 'object') {
      var search_result = $(restrict_search).find(search_criteria);
    } else if (typeof restrict_search == 'string') {
      var search_result = $(restrict_search + ' ' + search_criteria);
    } else {
      var search_result = $(search_criteria);
    }

    search_result.each(function(key, element) {
      var element_init_classes = $.grep(
        $(element)
          .attr('class')
          .split(' '),
        function(s) {
          return s.match(new RegExp('init-'));
        }
      );
      element_init_classes.forEach(function(class_name) {
        var extension = class_name.replace('init-', '');
        if (typeof $.frontendAssets.scripts[extension] != 'undefined') {
          $(element).on('extension::' + extension + '::init', $.frontendAssets.scripts[extension]);
          $(element).trigger('extension::' + extension + '::init');
        }
      });
    });
  },
};

(function($) {
  var trigger = $.fn['trigger'];
  $.fn['trigger'] = function(e) {
    $.frontendAssets.captureTrigger(e.type);
    return trigger.apply(this, arguments);
  };
})(jQuery);

$(function() {
  $.frontendAssets.autoInit();
});

// Legacy.
var findAndApplyScriptExtensions = (findAndInit = $.frontendAssets.init);
