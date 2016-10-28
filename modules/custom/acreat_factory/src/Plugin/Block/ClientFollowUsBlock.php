<?php
 
/**
 * @file
 * Contains \Drupal\acreat_factory\Plugin\Block\ClientFollowUsBlock
 */

namespace Drupal\acreat_factory\Plugin\Block;

use \Drupal\Core\Block\BlockBase;


/**
 * Provides a custom "Follow us" block.
 *
 * @Block(
 *   id = "client_followus_block",
 *   admin_label = @Translation("AcreatFactory : Client : Follow Us"),
 * )
 */

class ClientFollowUsBlock extends BlockBase
{
  
  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $configuration = \Drupal::config('acreat_factory.client');
    
    return array(
      '#theme'  => 'client_followus',
      '#client' => $configuration->get('client')
    );
  }
  
}