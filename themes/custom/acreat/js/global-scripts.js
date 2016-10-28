/**
 * @file
 * Acreat : jQuery Front Page Slick Banner Initialization
 */

Drupal.behaviors.initFrontPageBannerSlick = {
  attach: function (context, settings) {
    if (jQuery(".banner .slider").length) {
      jQuery(".banner .slider .field--type-image").slick({
        autoplay: true,
        autplaySpeed: 8000,
        arrows: false,
        fade: true
      });
    }
  }
};