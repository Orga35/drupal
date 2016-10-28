/**
 * @file
 * Acreat : jQuery Multiselect initialization
 */

Drupal.behaviors.initMultiselect = {
  attach: function (context, settings) {
    jQuery("select.multiselect").multiselect({
      header: false,
      minWidth: 0,
      height: "auto",
      checkAllText: 'Sélectionner tout',
      uncheckAllText: 'Désélectionner tout',
      noneSelectedText: 'Sélectionner',
      selectedText: '# sélectionnés',
      selectedList: 3,
      multiple: false
    });
  }
};