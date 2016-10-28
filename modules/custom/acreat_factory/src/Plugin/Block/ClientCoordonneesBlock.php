<?php
 
/**
 * @file
 * Contains \Drupal\acreat_factory\Plugin\Block\ClientCoordonneesBlock
 */

namespace Drupal\acreat_factory\Plugin\Block;

use \Drupal\Core\Block\BlockBase;


/**
 * Provides a custom "Coordonnées" block.
 *
 * @Block(
 *   id = "client_coordonnees_block",
 *   admin_label = @Translation("AcreatFactory : Client : Coordonnées"),
 * )
 */

class ClientCoordonneesBlock extends BlockBase
{
  
  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $configuration = \Drupal::config('acreat_factory.client');
    
    return array(
      '#theme'  => 'client_coordonnees',
      '#client' => $configuration->get('client')
    );
  }
  
}