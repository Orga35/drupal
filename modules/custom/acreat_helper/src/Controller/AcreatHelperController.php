<?php

namespace Drupal\acreat_helper\Controller;

use Drupal\Core\Controller\ControllerBase;


/**
 * Provides default content
 */
class AcreatHelperController extends ControllerBase
{
  
  /**
   * {@inheritdoc}
   */
  public static function debug($var, $die = true) {
    print '<pre>';
    print_r($var);
    print '</pre>';
    
    if($die)
      die;
  }
  
}