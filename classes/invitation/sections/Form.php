<?php

namespace PKP\invitation\sections;

use Exception;
use PKP\components\forms\FormComponent;
use stdClass;

class Form extends Section
{
    public string $type = 'form';
    public FormComponent $form;

    /**
     * @param FormComponent $form The form to show in this step
     *
     * @throws Exception
     */
    public function __construct(string $id, string $name, string $description, FormComponent $form)
    {
        parent::__construct($id, $name, $description);
        $this->form = $form;
    }

    public function getState(): stdClass
    {
        $config = parent::getState();
        foreach ($this->form->getConfig() as $key => $value) {
            $config->$key = $value;
        }
        unset($config->pages[0]['submitButton']);

        return $config;
    }
}
