<?php

namespace Drupal\guardian\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class GuardianSettingsForm.
 *
 * @package Drupal\guardian\Form
 */
class GuardianSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'guardian_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['guardian.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('guardian.settings');

    $form['field_description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Disabled description'),
      '#description' => $this->t('Show user why account field has been disabled by Guardian.'),
      '#required' => TRUE,
      '#default_value' => $config->get('field_description'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('guardian.settings')
      ->set('field_description', $form_state->getValue('field_description'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
