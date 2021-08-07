<?php

namespace Drupal\security_pack;

/**
 * Class for helper functions.
 */
class SecurityPackOperation {
  private $config_factory;

  public function __construct() {
    $this->config_factory = \Drupal::configFactory();
  }

  public function configure_loginsecurity_defaults() {
    $config = $this->get_editable_config('login_security.settings');
    $config->set('track_time', 1);
    $config->set('host_wrong_count', 10);
    $config->set('activity_threshold', 50);
    $config->set('last_login_timestamp', 1);
    $config->save();
  }

  public function configure_autologout_defaults() {
    $config = $this->get_editable_config('autologout.settings');
    $config->set('timeout', 1800);
    $config->set('no_individual_logout_threshold', TRUE);
    $config->set('no_dialog', TRUE);
    $config->set('enforce_admin', TRUE);
    $config->save();
  }

  public function configure_antibot_defaults() {
    $config = $this->get_editable_config('antibot.settings');
    $config->delete('forms_ids');
    $config->set('form_ids', explode("\r\n", '*'));
    $config->set('show_form_ids', FALSE);
    $config->save();
  }

  public function configure_seckit_defaults() {
    $config = $this->get_editable_config('seckit.settings');
    $config->set('seckit_xss.csp.checkbox', TRUE);
    $config->set('seckit_xss.csp.report-only', TRUE);
    $config->set('seckit_xss.csp.default-src', "'self'");
    $config->set('seckit_xss.csp.script-src', "'self'");
    $config->set('seckit_xss.csp.style-src', "'self' 'unsafe-inline'");
    $config->set('seckit_xss.csp.upgrade-req', TRUE);
    $config->set('seckit_xss.x_xss.select', 2);
    $config->set('seckit_csrf.origin', TRUE);
    $config->set('seckit_csrf.origin_whitelist', '');
    $config->set('seckit_ssl.hsts', TRUE);
    $config->set('seckit_ssl.hsts_subdomains', TRUE);
    $config->set('seckit_ssl.hsts_max_age', 1000);
    $config->set('seckit_ssl.hsts_preload', TRUE);
    $config->save();

  }

  private function get_editable_config($config) {
    return $this->config_factory->getEditable($config);
  }
}
