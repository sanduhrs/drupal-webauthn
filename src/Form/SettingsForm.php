<?php

namespace Drupal\webauthn\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Webauthn settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'webauthn_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['webauthn.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['relying_party'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Relying party'),
    ];
    $form['relying_party']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#default_value' => $this->config('webauthn.settings')->get('relying_party_name'),
    ];
    $form['relying_party']['id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('ID'),
      '#default_value' => $this->config('webauthn.settings')->get('relying_party_id'),
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
    $this->config('webauthn.settings')
      ->set('relying_party_name', $form_state->getValue('name'))
      ->set('relying_party_id', $form_state->getValue('id'))
      ->set('relying_party_icon', $form_state->getValue('icon'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
