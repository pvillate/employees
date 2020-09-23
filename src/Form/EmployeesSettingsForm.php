<?php

namespace Drupal\employees\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class EmployeesSettingsForm extends ConfigFormBase {
    const SETTINGS = 'employees.settings';

    public function getFormId() {
        return 'employees_settings_form';
    }

    protected function getEditableConfigNames() {
        return [
            static::SETTINGS,
        ];
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        $form = parent::buildForm($form, $form_state);
        $config = $this->config(static::SETTINGS);
        $form['api_url'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Url del api'),
            '#default_value' => $config->get('api_url'),
        ];

        return parent::buildForm($form, $form_state);
    }

    public function validateForm(array &$form, FormStateInterface $form_state) {

    }
    
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $this->configFactory->getEditable(static::SETTINGS)
            ->set('url_parques', $form_state->getValue('api_url'))
            ->save();
        return parent::submitForm($form, $form_state);
    }
}