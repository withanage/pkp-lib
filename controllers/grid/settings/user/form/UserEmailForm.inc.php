<?php

/**
 * @file controllers/grid/settings/user/form/UserEmailForm.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class UserEmailForm
 * @ingroup controllers_grid_settings_user_form
 *
 * @brief Form for sending an email to a user
 */

use APP\template\TemplateManager;
use PKP\form\Form;

use PKP\mail\MailTemplate;

class UserEmailForm extends Form
{
    /** @var the user id of user to send email to */
    public $userId;

    /**
     * Constructor.
     *
     * @param $userId int User ID to contact.
     */
    public function __construct($userId)
    {
        parent::__construct('controllers/grid/settings/user/form/userEmailForm.tpl');

        $this->userId = (int) $userId;

        $this->addCheck(new \PKP\form\validation\FormValidator($this, 'subject', 'required', 'email.subjectRequired'));
        $this->addCheck(new \PKP\form\validation\FormValidator($this, 'message', 'required', 'email.bodyRequired'));
        $this->addCheck(new \PKP\form\validation\FormValidatorPost($this));
        $this->addCheck(new \PKP\form\validation\FormValidatorCSRF($this));
    }

    /**
     * Assign form data to user-submitted data.
     *
     * @see Form::readInputData()
     */
    public function readInputData()
    {
        $this->readUserVars([
            'subject',
            'message',
        ]);
    }

    /**
     * @copydoc Form::Fetch
     *
     * @param null|mixed $template
     */
    public function fetch($request, $template = null, $display = false)
    {
        $userDao = DAORegistry::getDAO('UserDAO'); /** @var UserDAO $userDao */
        $user = $userDao->getById($this->userId);

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'userId' => $this->userId,
            'userFullName' => $user->getFullName(),
            'userEmail' => $user->getEmail(),
        ]);

        return parent::fetch($request, $template, $display);
    }

    /**
     * Send the email
     *
     * @copydoc Form::execute()
     */
    public function execute(...$functionArgs)
    {
        $userDao = DAORegistry::getDAO('UserDAO'); /** @var UserDAO $userDao */
        $toUser = $userDao->getById($this->userId);
        $request = Application::get()->getRequest();
        $fromUser = $request->getUser();

        $email = new MailTemplate();

        $email->addRecipient($toUser->getEmail(), $toUser->getFullName());
        $email->setReplyTo($fromUser->getEmail(), $fromUser->getFullName());
        $email->setSubject($this->getData('subject'));
        $email->setBody($this->getData('message'));
        $email->assignParams();

        parent::execute(...$functionArgs);

        if (!$email->send()) {
            import('classes.notification.NotificationManager');
            $notificationMgr = new NotificationManager();
            $notificationMgr->createTrivialNotification($request->getUser()->getId(), NOTIFICATION_TYPE_ERROR, ['contents' => __('email.compose.error')]);
        }
    }
}
