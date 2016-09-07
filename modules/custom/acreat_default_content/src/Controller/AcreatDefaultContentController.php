<?php

namespace Drupal\acreat_default_content\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides Acreat websites default content
 */
class AcreatDefaultContentController extends ControllerBase
{

    /**
     * {@inheritdoc}
     */
    public function import()
    {
        $files = $this->scan(base_path().'/sync/');


        $build = array(
            '#type' => 'markup',
            '#markup' => implode(', ', $files),
        );

        return $build;
    }

    /**
     * {@inheritdoc}
     */
    public function scan($directory)
    {
        $files = file_scan_directory($directory, '/yml$/');

        return $files;
    }

}