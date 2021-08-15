<?php

namespace Drupal\security_pack\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\security_pack\SecurityPackOperation;

/**
 * Configure Security Pack settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'security_pack_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['security_pack.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['reset'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Reset Security Pack configuration to default?'),
      '#default_value' => FALSE,
      '#description' => $this->t('This will reset the configuration for included modules back to default.')
    ];

    $form['notice'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Notice'),
    );

    $form['notice']['warning'] = [
      '#type' => 'label',
      '#title' => $this->t('This will reset all settings. If you have made any manual modifications, please note them down now.'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    if($form_state->getValue('reset')) {
      $security_pack = new SecurityPackOperation();
      $security_pack->import_default_config();
    }
  }
}
