<?php

namespace Drupal\employees\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\Client;
use Drupal\Component\Serialization\Json;

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

        $employees = NULL;
        $client = new Client();
        $url = $form_state->getValue('api_url');

        try {
            $response = $client->get($url);

            if ($response->getStatusCode() === 200) {
                $json = $response->getBody()->getContents();
                $employees = Json::decode($json);
            } else {
                throw New \RuntimeException(
                    'Error webservice: ' . $response->getStatusCode()
                );
            }
        } catch (\RuntimeException $e) {
            watchdog_exception(__METHOD__, $e);
        }

        $batch = array(
            'title' => t('Importando empleados...'),
            'init_message' => t('Iniciando importación.'),
            'progress_message' => t('Se han procesado @current de @total. Tiempo estimado: @estimate.'),
            'error_message' => t('Error en la importación.'),
            'finished' => '\Drupal\employees\ImportEmployees::importEmployeesFinishedCallback',
        );
        
        //foreach($employees['data'] AS $employee) {
            $batch['operations'] = array(
                array(
                    '\Drupal\employees\ImportEmployees::importEmployees',
                    array($employees['data'], array())
                ),
            );
        //}
      
        batch_set($batch);
        \Drupal::messenger()->addMessage("Se importaron $total empleados");
      
        $form_state->setRebuild(TRUE);
        
        return parent::submitForm($form, $form_state);
    }
}