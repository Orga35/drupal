<?php
 
/**
 * @file
 * Contains \Drupal\acreat_factory\Plugin\Block\ClientCoordonneesMapBlock
 */

namespace Drupal\acreat_factory\Plugin\Block;

use \Drupal\Core\Block\BlockBase;


/**
 * Provides a custom "Coordonnées & Map" block.
 *
 * @Block(
 *   id = "client_coordonnees_map_block",
 *   admin_label = @Translation("AcreatFactory : Client : Coordonnées & Map"),
 * )
 */

class ClientCoordonneesMapBlock extends BlockBase
{
  
  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $configuration = \Drupal::config('acreat_factory.client');
    
    return array(
      '#theme'  => 'client_coordonnees_map',
      '#client' => $configuration->get('client')
    );
  }
  
}