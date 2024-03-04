(($, Drupal, once) => {

  'use strict';

  /**
   * CTI Example Block.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.ctiExampleBlock = {
    attach: (context, settings) => {
      var $context = $(context);

      once('cti-example-block', 'html', $context).forEach(function () {
        // @todo TBD
      });
    }
  };
})(jQuery, Drupal, once);
