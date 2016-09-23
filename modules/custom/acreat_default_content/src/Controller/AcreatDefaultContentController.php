<?php

namespace Drupal\acreat_default_content\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Utility\Token;
use Drupal\node\Entity\Node;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;


/**
 * Provides default content
 */
class AcreatDefaultContentController extends ControllerBase
{
  /**
   * {@inheritdoc}
   */
  public function import()
  {
    module_load_include('module', 'acreat_helper', 'acreat_helper');
    
    // Initialize the parser
    $yaml = new Parser();
    
    // Get the content sync directory
    $directory = config_get_config_directory(CONFIG_SYNC_DIRECTORY) . "/content";
    
    // Get the content files
    $nodeFiles = $this->scan($directory);
    
    $node_files = array();
    foreach($nodeFiles as $nodeFile) {
      // Parse the content file
      $nodeDefinition = $yaml->parse(file_get_contents($nodeFile));
      
      // Import des photos
      $field_photos_values = $field_photo_value = array();
      if(in_array('field_photos', array_keys($nodeDefinition)) && !empty($nodeDefinition['field_photos'])) {
        // Get "Images" field instance infos
        $field_images_infos = FieldConfig::loadByName('node', $nodeDefinition['type'], 'field_photos')->get('settings');
        
        // Generate the "Images" field instance directory
        $field_images_directory = \Drupal::Token()->replace('public://'.$field_images_infos['file_directory'], array());
        
        if(file_prepare_directory($field_images_directory, FILE_CREATE_DIRECTORY)) {
          $photos = explode(';', $nodeDefinition['field_photos']);
          
          foreach($photos as $photo) {
            $photo = $directory.'/'.$photo;
            if(!file_exists($photo)) {
              \Drupal::logger('acreat_default_content')->notice("The file @photo from @node_file does not exists.", array('@photo' => $photo, '@node_file' => basename($nodeFile)));
              continue;
            }
            
            // Get the file contents
            $data = file_get_contents($photo);
            
            // Save the file
            $file = file_save_data($data, $field_images_directory.'/'.basename($photo), FILE_EXISTS_RENAME);
            
            // Associates the file info to the content field
            $field_photos_value = [ 'target_id' => $file->id() ];
            $field_photos_values[] = $field_photos_value;
          }
        }
      }
      
      // Import des fichiers joints
      $field_files_values = $field_files_value = array();
      if(in_array('field_files', array_keys($nodeDefinition)) && !empty($nodeDefinition['field_files'])) {
        // Get "Images" field instance infos
        $field_files_infos = FieldConfig::loadByName('node', $nodeDefinition['type'], 'field_files')->get('settings');
        
        // Generate the "Images" field instance directory
        $field_files_directory = \Drupal::Token()->replace('public://'.$field_files_infos['file_directory'], array());
        
        if(file_prepare_directory($field_files_directory, FILE_CREATE_DIRECTORY)) {
          $files = explode(';', $nodeDefinition['field_files']);
          
          foreach($files as $file) {
            $file = $directory.'/'.$file;
            if(!file_exists($file)) {
              \Drupal::logger('acreat_default_content')->notice("The file @file from @node_file does not exists.", array('@file' => $file, '@node_file' => basename($nodeFile)));
              continue;
            }
            
            // Get the file contents
            $data = file_get_contents($file);
            
            // Save the file
            $file = file_save_data($data, $field_files_directory.'/'.basename($file), FILE_EXISTS_RENAME);
            
            // Associates the file info to the content field
            $field_files_value = [ 'target_id' => $file->id() ];
            $field_files_values[] = $field_files_value;
          }
        }
      }
      
      // Programmatically creates a node
      $node = Node::create([
        'type'      => $nodeDefinition['type'],
        'uid'       => $nodeDefinition['uid'],
        'title'     => $nodeDefinition['title'],
        'revision'  => $nodeDefinition['revision'],
        'status'    => $nodeDefinition['status'],
        'promote'   => $nodeDefinition['promote'],
        'created'   => time(),
        'langcode'  => 'fr',
        'field_photos' => $field_photos_values,
        'field_files'  => $field_files_values
      ]);
    
      $node->set('body', [
        'value' => $nodeDefinition['body'],
        'format' => 'html'
      ]);
    
      if($node->save()) {
        \Drupal::logger('acreat_default_content')->notice("Content @node_title automatically created from @node_file file parsing.", array('@node_title' => $nodeDefinition['title'], '@node_file' => basename($nodeFile)));
      } else {
        \Drupal::logger('acreat_default_content')->notice("An error occured creating content @node_title from @node_file file parsing.", array('@node_title' => $nodeDefinition['title'], '@node_file' => basename($nodeFile)));
      }
      
      $node_files[] = $nodeFile->getRelativePathname();
    }

    $build = array(
      "#markup" => implode("<br />", $node_files),
      "#prefix" => "<h3>Fichiers traités :</h3>"
    );

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function scan($directory)
  {
    $contentTypes = \Drupal::service('entity.manager')->getStorage('node_type')->loadMultiple();
    
    $contentTypesList = array();
    foreach ($contentTypes as $contentType) {
      $contentTypesList[$contentType->id()] = $contentType->label();
    }
    
    $files = new Finder();
    $files->name('{'.implode(',', array_keys($contentTypesList)).'}.*.yml')->in($directory);
    
    return $files;
  }

  /**
   * {@inheritdoc}
   */
  public function parse($filename)
  {
    $parsed = yaml_parse_file ($filename);
    
    return $parsed;
  }
}