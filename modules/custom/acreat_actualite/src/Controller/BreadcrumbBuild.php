<?php

namespace Drupal\acreat_actualite\Controller;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;


/**
 * Provides default breadcrumb for "Actualité" content type
 */
class BreadcrumbBuild implements BreadcrumbBuilderInterface
{
  
  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $attributes) {
    $parameters = $attributes->getParameters()->all();
    if (!empty($parameters['node'])) {
      return $parameters['node']->getType() == 'actualite';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute('Home', '<front>'));
    $breadcrumb->addLink(Link::createFromRoute('Actualités', Url::fromUri('internal:/actualites')->getRouteName()));
    return $breadcrumb;
  }
  
}