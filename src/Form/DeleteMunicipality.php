<?php

namespace Drupal\municipalities_registration\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;

class DeleteMunicipality extends ConfigFormBase {
    
    const SETTINGS = 'municipalities_registration.delete';
    
    private $database;

    public function getFormId() {
        return 'municipalities_registration_delete';
    }

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

    public function buildForm(array $form, FormStateInterface $form_state, int $id = null) {
        if ($id < 1) {
            drupal_set_message($this->t('Municipality does not exists.'), 'error');
            return $form;
        }

        $item = $this->database->select('municipalities_registration_items', 'mri')
            ->fields('mri',['title', 'id'])
            ->condition('id', $id)
            ->execute()
            ->fetchObject();
        
        if (empty($item)) {
            drupal_set_message($this->t('Municipality does not exists.'), 'error');
            return $form;
        }
        
        $form['id'] = [
            '#type' => 'hidden',
            '#maxlength' => 50,
            '#required' => TRUE,
            '#default_value' => $item->id,
        ];
        
        $form['helptext'] = [
            '#type' => 'item',
            '#markup' => "Are you sure you want to delete the <b>" . $item->title . "</b> municipality ?",
        ];
        $form['delete'] = [
            '#type' => 'submit',
            '#value' => $this->t('Delete'),
        ];

        $form['cancel'] = [
            '#type' => 'submit',
            '#value' => $this->t('Cancel'),
        ];
        
        return $form;
    }

    public function submitForm(array &$form, FormStateInterface $form_state, int $id=null) {
        if ($form_state->getValue('id')>0 && $form_state->getValue('op')->getUntranslatedString() === "Delete") {
            $this->database->delete('municipalities_registration_items')->condition('id', $form_state->getValue('id'))
  ->execute();
            $this->messenger()->addStatus($this->t('Municipality deleted successfully.'));
        }
        $form_state->setRedirectUrl(Url::fromRoute('municipalities_registration.list'));
    }

}
