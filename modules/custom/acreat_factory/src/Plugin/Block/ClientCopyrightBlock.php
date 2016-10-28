<?php
 
/**
 * @file
 * Contains \Drupal\acreat_factory\Plugin\Block\ClientCopyrightBlock
 */

namespace Drupal\acreat_factory\Plugin\Block;

use \Drupal\Core\Block\BlockBase;


/**
 * Provides a custom "Copyright" block.
 *
 * @Block(
 *   id = "client_copyright_block",
 *   admin_label = @Translation("AcreatFactory : Client : Copyright"),
 * )
 */

class ClientCopyrightBlock extends BlockBase
{
  
  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $configuration = \Drupal::config('acreat_factory.client');
    
    return array(
      '#theme'  => 'client_copyright',
      '#client' => $configuration->get('client')
    );
  }
  
}