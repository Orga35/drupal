<?php

/**
* @file Contains the code to generate the custom drush commands.
*/

/**
 * Implements hook_drush_command().
 */
function acreat_factory_content_drush_command() {
  $items = array();
  
  $items['import-content'] = [
    'callback'            => 'drush_command_import_content',
    'description'         => 'Provides default content from the sync directory',
    'drupal dependencies' => ['acreat_factory_content'],
    'aliases'             => ['import:content'],
  ];
  
  return $items;
}

/**
 * drush_command_import_content 
 */
function drush_command_import_content() {
  $importController = new \Drupal\acreat_factory_content\Controller\AcreatFactoryContentController;
  $importController->import();
}