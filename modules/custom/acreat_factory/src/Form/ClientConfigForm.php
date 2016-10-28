<?php
 
/**
 * @file
 * Contains \Drupal\acreat_factory\Form\ClientConfigForm
 */
 
namespace Drupal\acreat_factory\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\acreat_helper\Controller\AcreatHelperController;


class ClientConfigForm extends ConfigFormBase
{
 
  /** 
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'client_config_form';
  }
 
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $form = parent::buildForm($form, $form_state);
    
    $client_config = \Drupal::config('acreat_factory.client')->get('client');
    
    $default_config_fields = array(
      'email' => array(
        'type'          => 'textfield',
        'title'         => $this->t('acreat_factory.client.email'),
        'default_value' => '',
        'required'      => TRUE
      ),
      // ---
      'denomination_sociale' => array(
        'type'          => 'textfield',
        'title'         => $this->t('acreat_factory.client.denomination_sociale'),
        'default_value' => '',
        'required'      => TRUE
      ),
      // ---
      'adresse' => array(
        'type'          => 'textarea',
        'title'         => $this->t('acreat_factory.client.adresse'),
        'rows'          => 3,
        'default_value' => '',
        'required'      => TRUE
      ),
      // ---
      'code_postal' => array(
        'type'          => 'textfield',
        'title'         => $this->t('acreat_factory.client.code_postal'),
        'default_value' => '',
        'required'      => TRUE
      ),
      // ---
      'ville' => array(
        'type'          => 'textfield',
        'title'         => $this->t('acreat_factory.client.ville'),
        'default_value' => '',
        'required'      => TRUE
      ),
      // ---
      'telephone' => array(
        'type'          => 'textfield',
        'title'         => $this->t('acreat_factory.client.telephone'),
        'default_value' => '',
        'required'      => FALSE
      ),
      // ---
      'mobile' => array(
        'type'          => 'textfield',
        'title'         => $this->t('acreat_factory.client.mobile'),
        'default_value' => '',
        'required'      => FALSE
      ),
      // ---
      'fax' => array(
        'type'          => 'textfield',
        'title'         => $this->t('acreat_factory.client.fax'),
        'default_value' => '',
        'required'      => FALSE
      ),
      // ---
      'latitude' => array(
        'type'          => 'textfield',
        'title'         => $this->t('acreat_factory.client.latitude'),
        'default_value' => '',
        'required'      => FALSE
      ),
      // ---
      'longitude' => array(
        'type'          => 'textfield',
        'title'         => $this->t('acreat_factory.client.longitude'),
        'default_value' => '',
        'required'      => FALSE
      ),
      // ---
      'facebook_url' => array(
        'type'          => 'textfield',
        'title'         => $this->t('acreat_factory.client.facebook_url'),
        'default_value' => '',
        'required'      => FALSE
      ),
      // ---
      'twitter_url' => array(
        'type'          => 'textfield',
        'title'         => $this->t('acreat_factory.client.twitter_url'),
        'default_value' => '',
        'required'      => FALSE
      ),
      // ---
      'google_plus_url' => array(
        'type'          => 'textfield',
        'title'         => $this->t('acreat_factory.client.google_plus_url'),
        'default_value' => '',
        'required'      => FALSE
      ),
      // ---
      'maps_api_key' => array(
        'type'          => 'textfield',
        'title'         => $this->t('acreat_factory.client.maps_api_key'),
        'default_value' => '',
        'required'      => FALSE
      ),
      // ---
      'maps_styles' => array(
        'type'          => 'textarea',
        'title'         => $this->t('acreat_factory.client.maps_styles'),
        'rows'          => 3,
        'default_value' => '',
        'required'      => FALSE
      )
    );
    
    if(isset($client_config) && is_array($client_config))
      $active_config_fields = array_replace_recursive($default_config_fields, $client_config);
    else
      $active_config_fields = $default_config_fields;
    
    foreach($active_config_fields as $field_key => $field_infos) {
      foreach($field_infos as $field_info_key => $field_info_value) {
        $form[$field_key]['#' . $field_info_key] = $field_info_value;
      }
    }
    
    return $form;
  }
 
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $config = $this->config('acreat_factory.client');
    
    $values = $form_state->getValues();
    unset($values['submit'],$values['form_build_id'], $values['form_token'], $values['form_id'], $values['op']);
    
    foreach($values as $key => $value) {
      $config->set('client.' . $key . '.default_value', $value);
    }
    
    $config->save();
    
    return parent::submitForm($form, $form_state); 
  }
  
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames()
  {
    return [
      'acreat_factory.client'
    ];
  }
 
}