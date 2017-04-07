<?php

/**
 * Settings form for Guardian.
 */
function guardian_settings_form($form, $form_state) {
  $form['guardian_description_disabled_element'] = array(
    '#type' => 'textarea',
    '#title' => t('Disabled description'),
    '#description' => t('Show user why account field has been disabled by Guardian.'),
    '#default_value' => variable_get('guardian_description_disabled_element', t('Disabled by Guardian.')),
    '#required' => TRUE,
  );

  return system_settings_form($form);
}
