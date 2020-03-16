<?php

namespace Drupal\municipalities_registration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\t;
use Drupal\municipalities_registration\Services\PostitService;
use Drupal\Core\Database\Connection;

class MunicipalitiesRegistrationSetting extends ConfigFormBase {

    const SETTINGS = 'municipalities_registration.configuration';

    private $database;

    public function getFormId() {
        return 'municipalities_registration';
    }

    protected function getEditableConfigNames() {
        return [
            static::SETTINGS,
        ];
    }

    public function __construct(Connection $con) {
        $this->database = $con;
    }

    public static function create(ContainerInterface $container) {
        return new static(
                $container->get('database')
        );
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config(static::SETTINGS);
        $form = parent::buildForm($form, $form_state);

        $form['municipalities_registration'] = [
            '#type' => 'details',
            '#title' => $this->t('Postit API settings'),
            '#open' => TRUE,
        ];

        $form['municipalities_registration_api'] = [
            '#type' => 'details',
            '#title' => t('Postit API'),
            '#open' => TRUE,
        ];

        $form['municipalities_registration_api']['clear'] = [
            '#type' => 'submit',
            '#value' => t('Get municipalities'),
            '#submit' => ['::submitApi'],
        ];

        $form['municipalities_registration']['api_key'] = [
            '#type' => 'textfield',
            '#title' => $this->t('API key'),
            '#maxlength' => 50,
            '#required' => TRUE,
            '#default_value' => $config->get('api_key') ? $config->get('api_key') : '',
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $this->configFactory->getEditable(static::SETTINGS)
                ->set('api_key', $form_state->getValue('api_key'))
                ->save();

        parent::submitForm($form, $form_state);
    }

    public function submitApi(array &$form, FormStateInterface $form_state) {
        if ($form_state->isValueEmpty('api_key')) {
            $this->messenger()->addError($this->t('API key field is empty'));
            return false;
        }
        $service = new PostitService($form_state->getValue('api_key'));
        $get_municipalities = $service->getMunicipalities();
        if (false === $get_municipalities) {
            $this->messenger()->addError($this->t('API service error'));
            return false;
        }

        $data = $service->getData();
        if (!empty($data)) {
            $query = $this->database->insert('municipalities_registration_items')->fields(['title']);
            foreach ($data as $item) {
                $update_query = $this->database->query("SELECT title FROM municipalities_registration_items WHERE title='" . $item['municipality'] . "'")->fetchAssoc();
                if (false == $update_query) {
                    $query->values([
                        'title' => $item['municipality']
                    ]);
                }
            }
            $query->execute();
        }

        $this->messenger()->addStatus($this->t('Municipalities have been inserted.'));
    }

}
