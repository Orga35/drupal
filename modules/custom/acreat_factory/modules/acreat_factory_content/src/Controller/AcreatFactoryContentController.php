<?php

namespace Drupal\acreat_factory_content\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Utility\Token;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\block_content\Entity\BlockContent;
use Drupal\block\Entity\Block;
use Drupal\node\Entity\Node;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\acreat_helper\Controller\AcreatHelperController;
use Drupal\user\Entity\User;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;



/**
 * Provides default content
 */
class AcreatFactoryContentController extends ControllerBase
{
  
  /**
   * The YAML parser
   */
  protected $parser;
  
  /**
   * The Sync directory
   */
  protected $syncDirectory;
  
  
  /**
   * {@inheritdoc}
   */
  public function __construct()
  {
    // Initialize the parser
    $this->parser = new Parser();
    
    // Get the content sync directory
    $this->syncDirectory = config_get_config_directory(CONFIG_SYNC_DIRECTORY) . "/content";
  }
  
  /**
   * {@inheritdoc}
   */
  public function import()
  {
    $build = array();
    
    // Import nodes
    $createdNodes = $this->importNodes();
    $build['nodes_success'] = array(
      "#markup" => (count($createdNodes['success']) > 0) ? implode("<br />", $createdNodes['success']) : 'Aucun',
      "#prefix" => '<h3>Contenus créés</h3>',
      "#weight" => 0
    );
    
    // Import blocks
    $createdBlocks = $this->importBlocks();
    $build['blocks_success'] = array(
      "#markup" => (count($createdBlocks['success']) > 0) ? implode("<br />", $createdBlocks['success']) : 'Aucun',
      "#prefix" => '<h3>Blocs créés</h3>',
      "#weight" => 1
    );
    
    // Import links
    $createdLinks = $this->importLinks();
    $build['links_success'] = array(
      "#markup" => (count($createdLinks['success']) > 0) ? implode("<br />", $createdLinks['success']) : 'Aucun',
      "#prefix" => '<h3>Liens de menu créés</h3>',
      "#weight" => 2
    );
    
    // Import users
    $createdUsers = $this->importUsers();
    $build['users_success'] = array(
      "#markup" => (count($createdUsers['success']) > 0) ? implode("<br />", $createdUsers['success']) : 'Aucun',
      "#prefix" => '<h3>Utilisateurs créés</h3>',
      "#weight" => 3
    );

    return $build;
  }
  
  /**
   * {@inheritdoc}
   */
  public function importNodes()
  {
    // Get the content types list
    $contentTypes = \Drupal::service('entity.manager')->getStorage('node_type')->loadMultiple();
    $contentTypesList = [];
    foreach ($contentTypes as $contentType) {
      $contentTypesList[] = $contentType->id();
    }
    
    // Get the content files
    $nodeFiles = new Finder();
    $nodeFiles->name('{' . implode(',', $contentTypesList) . '}.*.yml')->in($this->syncDirectory);
    
    $created = array('success' => array(), 'fail' => array());
    foreach($nodeFiles as $nodeFile) {
      // Parse the content file
      $fileContent = file_get_contents($nodeFile);
      $nodeDefinition = $this->parser->parse($fileContent);
      
      // Import images
      $field_photos_values = $field_photos_value = array();
      if(in_array('field_photos', array_keys($nodeDefinition)) && !empty($nodeDefinition['field_photos'])) {
        // Get "Images" field instance infos
        $field_images_infos = FieldConfig::loadByName('node', $nodeDefinition['type'], 'field_photos')->get('settings');
        
        // Generate the "Images" field instance directory
        $field_images_directory = \Drupal::Token()->replace('public://'.$field_images_infos['file_directory'], array());
        
        if(file_prepare_directory($field_images_directory, FILE_CREATE_DIRECTORY)) {
          $photos = explode(';', $nodeDefinition['field_photos']);
          
          foreach($photos as $photo) {
            $photo = $this->syncDirectory.'/'.$photo;
            if(!file_exists($photo)) {
              \Drupal::logger('acreat_factory_content')->notice("The file @photo from @node_file does not exists.", array('@photo' => $photo, '@node_file' => basename($nodeFile)));
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
      
      // Import attached files
      $field_files_values = $field_files_value = array();
      if(in_array('field_files', array_keys($nodeDefinition)) && !empty($nodeDefinition['field_files'])) {
        // Get "Images" field instance infos
        $field_files_infos = FieldConfig::loadByName('node', $nodeDefinition['type'], 'field_files')->get('settings');
        
        // Generate the "Images" field instance directory
        $field_files_directory = \Drupal::Token()->replace('public://'.$field_files_infos['file_directory'], array());
        
        if(file_prepare_directory($field_files_directory, FILE_CREATE_DIRECTORY)) {
          $files = explode(';', $nodeDefinition['field_files']);
          
          foreach($files as $file) {
            $file = $this->syncDirectory.'/'.$file;
            if(!file_exists($file)) {
              \Drupal::logger('acreat_factory_content')->notice("The file @file from @node_file does not exists.", array('@file' => $file, '@node_file' => basename($nodeFile)));
              continue;
            }
            
            // Get the file contents
            $data = file_get_contents($file);
            
            // Save the file
            $file = file_save_data($data, $field_files_directory.'/'.basename($file), FILE_EXISTS_RENAME);
            
            // Associates the file info to the content field
            $field_files_value = [
              'target_id' => $file->id()
            ];
            $field_files_values[] = $field_files_value;
          }
        }
      }
      
      // Programmatically creates a node
      $node = Node::create([
        'type'          => $nodeDefinition['type'],
        'uid'           => $nodeDefinition['uid'],
        'title'         => $nodeDefinition['title'],
        'revision'      => $nodeDefinition['revision'],
        'status'        => $nodeDefinition['status'],
        'promote'       => $nodeDefinition['promote'],
        'created'       => time(),
        'langcode'      => 'fr',
        'field_photos'  => $field_photos_values,
        'field_files'   => $field_files_values
      ]);
    
      $node->set('body', [
        'value'  => $nodeDefinition['body'],
        'format' => 'html'
      ]);
    
      if($node->save()) {
        $created['success'][] = "<strong>" . $nodeDefinition['title'] . "</strong> (" . basename($nodeFile) . ")";
        \Drupal::logger('acreat_factory_content')->notice("Content @node_title automatically created from @node_file file parsing.", array('@node_title' => $nodeDefinition['title'], '@node_file' => basename($nodeFile)));
        
        if(in_array('link', array_keys($nodeDefinition))) {
          // Menu link creation
          $menu_link = MenuLinkContent::create([
            'title'     => $nodeDefinition['link']['title'],
            'link'      => ['uri' => 'entity:node/' . $node->id()],
            'menu_name' => $nodeDefinition['link']['menu'],
            'weight'    => $nodeDefinition['link']['weight'],
            'expanded'  => $nodeDefinition['link']['expanded'],
            'langcode'  => 'fr',
          ]);
          $menu_link->save();
        }
        
        if($nodeDefinition['is_403'] == true) {
          // Is 403 content ?
          \Drupal::configFactory()->getEditable('system.site')->set('page.403', '/node/' . $node->id())->save();
        } elseif($nodeDefinition['is_404'] == true) {
          // Is 404 content ?
          \Drupal::configFactory()->getEditable('system.site')->set('page.404', '/node/' . $node->id())->save();
        } elseif($nodeDefinition['is_front_content'] == true) {
          // Is front page content ?
          \Drupal::configFactory()->getEditable('system.site')->set('page.front', '/node/' . $node->id())->save();
        }
      } else {
        $created['fail'][] = "An error occured creating content " . $nodeDefinition['title'] . " from " . basename($nodeFile) . " file parsing.";
        \Drupal::logger('acreat_factory_content')->notice("An error occured creating content @node_title from @node_file file parsing.", array('@node_title' => $nodeDefinition['title'], '@node_file' => basename($nodeFile)));
      }
    }

    return $created;
  }
  
  /**
   * {@inheritdoc}
   */
  public function importBlocks()
  {
    // Get the content files
    $blockFiles = new Finder();
    $blockFiles->name('block.*.yml')->in($this->syncDirectory);
    
    $created = array('success' => array(), 'fail' => array());
    foreach($blockFiles as $blockFile) {
      // Parse the content file
      $fileContent = file_get_contents($blockFile);
      $blockDefinition = $this->parser->parse($fileContent);
      
      $blockContent = BlockContent::create([
        'type' => $blockDefinition['type'],
        'info' => $blockDefinition['settings']['label']
      ]);
      
      $fields = $blockDefinition['fields'];
      foreach ($fields as $field_name => $infos) {
        if ($blockContent->hasField($field_name)) {
          switch($infos['type']) {
            case 'html':
              $blockContent->set($field_name, [
                'value'  => $infos['content'],
                'format' => $infos['type']
              ]);
            break;
            case 'images':
              // Import images
              $field_photos_values = $field_photos_value = array();
              
              // Get "Images" field instance infos
              $field_images_infos = FieldConfig::loadByName('block_content', 'slider', 'field_images')->get('settings');
              
              // Generate the "Images" field instance directory
              $field_images_directory = \Drupal::Token()->replace('public://'.$field_images_infos['file_directory'], array());
              
              if(file_prepare_directory($field_images_directory, FILE_CREATE_DIRECTORY)) {
                $photos = explode(';', $infos['content']);
                
                foreach($photos as $photo) {
                  $photo = $this->syncDirectory.'/'.$photo;
                  if(!file_exists($photo)) {
                    \Drupal::logger('acreat_factory_content')->notice("The file @photo from @block_file does not exists.", array('@photo' => $photo, '@block_file' => basename($blockFile)));
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
              
              // Set field values
              $blockContent->set($field_name, $field_photos_values);
            break;
          }
        }
      }
    
      $blockContent->save();
      
      $block = Block::create([
        'plugin'      => 'block_content:' . $blockContent->uuid(),
        'theme'       => \Drupal::config('system.theme')->get('default'),
        'id'          => $blockDefinition['id'],
        'region'      => $blockDefinition['region'],
        'provider'    => $blockDefinition['settings']['provider'],
        'weight'      => $blockDefinition['weight'],
        'visibility'  => $blockDefinition['visibility'],
        'settings'    => $blockDefinition['settings']
      ]);
    
      if($block->save()) {
        $created['success'][] = "Block " . $blockDefinition['settings']['label'] . " automatically created from " . basename($blockFile) . " file parsing.";
        \Drupal::logger('acreat_factory_content')->notice("Content @block_title automatically created from @block_file file parsing.", array('@block_title' => $blockDefinition['settings']['label'], '@block_file' => basename($blockFile)));
      } else {
        $created['fail'][] = "An error occured creating block " . $blockDefinition['settings']['label'] . " from " . basename($blockFile) . " file parsing.";
        \Drupal::logger('acreat_factory_content')->notice("An error occured creating block @block_title from @block_file file parsing.", array('@block_title' => $blockDefinition['settings']['label'], '@block_file' => basename($blockFile)));
      }
    }
    
    return $created;
  }
  
  /**
   * {@inheritdoc}
   */
  public function importLinks()
  {
    // Get the content files
    $linkFiles = new Finder();
    $linkFiles->name('link.*.yml')->in($this->syncDirectory);
    
    $created = array('success' => array(), 'fail' => array());
    foreach($linkFiles as $linkFile) {
      // Parse the content file
      $fileContent = file_get_contents($linkFile);
      $linkDefinition = $this->parser->parse($fileContent);
      
      // Menu link creation
      $menu_link = MenuLinkContent::create([
        'title'     => $linkDefinition['title'],
        'link'      => ['uri' => $linkDefinition['path']],
        'menu_name' => $linkDefinition['menu'],
        'weight'    => $linkDefinition['weight'],
        'expanded'  => $linkDefinition['expanded'],
        'langcode'  => 'fr',
      ]);
    
      if($menu_link->save()) {
        $created['success'][] = "Block " . $linkDefinition['title'] . " automatically created from " . basename($linkFile) . " file parsing.";
        \Drupal::logger('acreat_factory_content')->notice("Link @link_title automatically created from @link_file file parsing.", array('@link_title' => $linkDefinition['title'], '@link_file' => basename($linkFile)));
      } else {
        $created['fail'][] = "An error occured creating block " . $linkDefinition['title'] . " from " . basename($linkFile) . " file parsing.";
        \Drupal::logger('acreat_factory_content')->notice("An error occured creating block @link_title from @link_file file parsing.", array('@link_title' => $linkDefinition['title'], '@link_file' => basename($linkFile)));
      }
    }
    
    return $created;
  }
  
  /**
   * {@inheritdoc}
   */
  public function importUsers()
  {
    // Get the content files
    $userFiles = new Finder();
    $userFiles->name('user.*.yml')->in($this->syncDirectory);
    
    $created = array('success' => array(), 'fail' => array());
    foreach($userFiles as $userFile) {
      // Parse the content file
      $fileContent = file_get_contents($userFile);
      $userDefinition = $this->parser->parse($fileContent);
      
      // User creation
      $user = User::create();
      $user->enforceIsNew();
      $user->setEmail($userDefinition['email']);
      $user->setUsername($userDefinition['user_name']);
      $user->setPassword($userDefinition['password']);
      $user->set('init', $userDefinition['email']);
      $user->set('langcode', 'fr');
      $user->set('preferred_langcode', 'fr');
      $user->set('preferred_admin_langcode', 'fr');
      $user->activate();
      
      $roles = explode(';', $userDefinition['roles']);
      foreach($roles as $rid) {
        $user->addRole($rid);
      }
      
      if($user->save()) {
        $created['success'][] = "User " . $userDefinition['user_name'] . " automatically created from " . basename($userFile) . " file parsing.";
        \Drupal::logger('acreat_factory_content')->notice("User @user_name automatically created from @user_file file parsing.", array('@user_name' => $userDefinition['user_name'], '@user_file' => basename($userFile)));
      } else {
        $created['fail'][] = "An error occured creating block " . $userDefinition['user_name'] . " from " . basename($userFile) . " file parsing.";
        \Drupal::logger('acreat_factory_content')->notice("An error occured creating block @user_name from @user_file file parsing.", array('@user_name' => $userDefinition['user_name'], '@user_file' => basename($userFile)));
      }
    }
    
    return $created;
  }

  /**
   * {@inheritdoc}
   */
  public function parse($filename)
  {
    $parsed = yaml_parse_file($filename);
    return $parsed;
  }
  
}