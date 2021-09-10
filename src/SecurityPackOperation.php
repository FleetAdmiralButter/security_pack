<?php

namespace Drupal\security_pack;

use Drupal;

/**
 * Class for helper functions.
 */
class SecurityPackOperation {

  /**
   * {@inheritdoc}
   */
  public function __construct() {
  }

  /**
   * Install the default set of configuration.
   */
  public function importDefaultConfig() {
    $configs = [
      'antibot.settings',
      'autologout.settings',
      'login_security.settings',
      'seckit.settings',
      'password_policy.password_policy.security_pack_default',
    ];
    module_load_include('inc', 'security_pack', 'includes/helpers');
    $config_location = [drupal_get_path('module', 'security_pack') . '/config/optional'];
    foreach ($configs as $config) {
      Drupal::configFactory()->getEditable($config)->delete();
      _security_pack_import_single_config($config, $config_location);
    }
    drupal_flush_all_caches();
  }

}
