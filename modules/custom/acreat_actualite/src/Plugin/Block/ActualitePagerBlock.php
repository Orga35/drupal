<?php

namespace Drupal\acreat_actualite\Plugin\Block;

use \Drupal\Core\Block\BlockBase;
use \Drupal\Core\Cache\Cache;
use \Drupal\acreat_helper\Controller\AcreatHelperController;


/**
 * Provides a custom pager block for "Actualité" content.
 *
 * @Block(
 *   id = "actualite_pager_block",
 *   admin_label = @Translation("Actualité Pager Block"),
 * )
 */

class ActualitePagerBlock extends BlockBase
{
  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $node = \Drupal::routeMatch()->getParameter('node');
    
    $previous_url = $next_url = $previous_title = $next_title = NULL;
    if($node) {
      $view_results = views_get_view_result('actualites', 'pager');
      
      $nids = $titles = $entities = [];
      foreach ($view_results as $item) {
        $nids[]     = $item->_entity->nid->value;
        $titles[]   = $item->_entity->title->value;
        $entities[] = $item->_entity;
      }
      
      $current_node_index = array_search($node->nid->value, $nids);
      
      if(count($nids) > 1) {
        if($current_node_index > 0) {
          // Previous content url
          $previous_url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => $nids[($current_node_index - 1)]])->toString();
          
          // Previous content title
          $previous_title = $titles[($current_node_index - 1)];
        }
        
        if($current_node_index < (count($nids) - 1)) {
          // Next content url
          $next_url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => $nids[($current_node_index + 1)]])->toString();
          
          // Next content title
          $next_title = $titles[($current_node_index + 1)];
        }
      }
      
      return array(
        '#theme'          => 'acreat_actualite_pager',
        '#previous_url'   => $previous_url,
        '#previous_title' => $previous_title,
        '#next_url'       => $next_url,
        '#next_title'     => $next_title
      );
    }
  }

  /**
   * Returns the cache tags associated with this block
   */
  public function getCacheTags()
  {
    //With this when your node change your block will rebuild
    if($node = \Drupal::routeMatch()->getParameter('node')) {
      // If there is node add its cachetag
      return Cache::mergeTags(parent::getCacheTags(), array('node:' . $node->id()));
    } else {
      // Return default tags instead.
      return parent::getCacheTags();
    }
  }

  /**
   * Returns the cache context associated with this block
   */
  public function getCacheContexts()
  {
    // The block content depends on \Drupal::routeMatch() : I must set context of this block with 'route' context tag
    // Every new route this block will rebuild
    return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
  }
}