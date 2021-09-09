<?php

namespace Drupal\security_pack;

/**
 * Class for helper functions.
 */
class SecurityPackOperation {

  public function __construct() {
  }

  /**
   * Install the default set of configuration.
   */
  public function import_default_config() {
    $configs = [
      'antibot.settings',
      'autologout.settings',
      'login_security.settings',
      'seckit.settings',
      'password_policy.password_policy.security_pack_default',
    ];
    module_load_include('inc', 'security_pack', 'includes/helpers');
    $config_location = [drupal_get_path('module', 'security_pack') . '/config/optional'];
    foreach($configs as $config) {
      \Drupal::configFactory()->getEditable($config)->delete();
      _security_pack_import_single_config($config, $config_location);
    }
    drupal_flush_all_caches();
  }

}
