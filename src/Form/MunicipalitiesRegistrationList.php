<?php

namespace Drupal\municipalities_registration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;
use Drupal\Core\Database\Connection;

class MunicipalitiesRegistrationList extends ConfigFormBase {

    const SETTINGS = 'municipalities_registration.configuration';

    private $database;

    public function __construct(Connection $con) {
        $this->database = $con;
    }

    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('database')
        );
    }

    protected function getEditableConfigNames() {
        return [
            static::SETTINGS,
        ];
    }

    public function getFormId() {
        return 'municipalities_registration_list';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {        
        $form['municipality_list'] = [
            '#type' => 'table',
            '#header' => [
                $this->t('Municipality'),
                $this->t('Delete'),
            ],
            '#empty' => $this->t('There are no municipalities.'),
        ];

        $result_list = $this->database->query("SELECT * FROM municipalities_registration_items")->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result_list as $key => $list) {
            $form['municipality_list'][$list['id']] = [
                'municipality' => [
                    '#markup' => $list['title']
                ],
                'delete' => [
                    '#markup' => Link::createFromRoute($this->t('Delete'), 'municipalities_registration.delete', ['id' => $list['id']])->toString()
                ]
            ];
        }

        return $form;
    }

}
