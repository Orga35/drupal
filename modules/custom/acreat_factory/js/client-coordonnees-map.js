/**
 * @file
 * Acreat Factory : Client Coordonnées Map
 */

Drupal.behaviors.initCoordonneesMap = {
  attach: function (context, settings) {
    var mapBlock      = jQuery("#block-client-coordonnees-map .map");
    var markerPos     = new google.maps.LatLng(mapBlock.data("latitude"), mapBlock.data("longitude"));
    var mapOptions    = {
      zoom: 11,
      center: markerPos
    };
    
    if (drupalSettings.acreatFactory.mapsStyles) {
      mapOptions.styles = eval(drupalSettings.acreatFactory.mapsStyles);
    }
    
    if (mapBlock.length) {
      var map = new google.maps.Map(mapBlock.get(0), mapOptions);
      var marker = new google.maps.Marker({
        position: markerPos,
        map: map,
        icon: drupalSettings.acreatFactory.markerUrl
      });
    }
  }
};