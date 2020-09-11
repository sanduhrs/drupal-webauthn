<?php

namespace Drupal\webauthn\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * The login form implementation.
 *
 * @package Drupal\webauthn\Form
 */
class Login extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'webauthn_login';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#required' => TRUE,
    ];
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Login'),
    ];
    $form['#attached'] = [
      'library' => [
        'webauthn/webauthn',
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
