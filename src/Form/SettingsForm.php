<?php

namespace Drupal\security_pack\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Security Pack settings for this site.
 */
class SettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'security_pack_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['notice'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Notice'),
    ];

    $form['notice']['warning'] = [
      '#type' => 'label',
      '#title' => $this->t('This will reset all settings. If you have made any manual modifications, please note them down now.'),
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Reset Security Pack configuration'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::service('security_pack.config_importer')->importDefaultConfig();
    \Drupal::messenger()->addMessage('The configuration has been reset successfully');
  }

}
