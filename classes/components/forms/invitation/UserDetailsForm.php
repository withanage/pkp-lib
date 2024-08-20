<?php

namespace PKP\components\forms\invitation;

use PKP\components\forms\FieldHTML;
use PKP\components\forms\FieldText;
use PKP\components\forms\FormComponent;

define('FORM_USER_DETAILS', 'userDetails');
class UserDetailsForm extends FormComponent
{
    /** @copydoc FormComponent::$id */
    public $id = FORM_USER_DETAILS;

    /** @copydoc FormComponent::$method */
    public $method = 'POST';

    /**
     * Constructor
     *
     * @param string $action URL to submit the form to
     * @param array $locales Supported locales
     * @param \PKP\context\Context $context Journal or Press to change settings for
     */
    public function __construct($action, $locales, $context)
    {
        $this->action = $action;
        $this->locales = $locales;

        $this->addField(new FieldText('email', [
            'label' => __('user.email'),
            'description' => __('invitation.email.description'),
            'isRequired' => true,
            'size' => 'large',
        ]))
            ->addField(new FieldHTML('orcid', [
                'label' => __('user.orcid'),
                'description' => __('invitation.orcid.description'),
                'isRequired' => false,
                'size' => 'large',
            ]))
            ->addField(new FieldText('givenName', [
                'label' => __('user.givenName'),
                'description' => __('invitation.givenName.description'),
                'isRequired' => false,
                'size' => 'large',
            ]))
            ->addField(new FieldText('familyName', [
                'label' => __('user.familyName'),
                'description' => __('invitation.familyName.description'),
                'isRequired' => false,
                'size' => 'large',
            ]));
    }
}
