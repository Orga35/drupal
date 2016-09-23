<?php

/**
* @file Contains the code to generate the custom drush commands.
*/

/**
* Implements hook_drush_command().
*/
function acreat_default_content_drush_command() {
  $items = array();
  
  $items['import-default-content'] = [
    'callback'            => 'drush_command_import_default_content',
    'description'         => 'Provides default content from the sync directory',
    'drupal dependencies' => ['acreat_default_content'],
    'aliases'             => ['import:content'],
  ];
  
  return $items;
}

/**
* Call back function drush_custom_drush_command_say_hello()
* The call back function name in the  following format
*   drush_{module_name}_{item_id_for_command}()
*/
function drush_command_import_default_content() {
  $importController = new \Drupal\acreat_default_content\Controller\AcreatDefaultContentController;
  $importController->import();
}