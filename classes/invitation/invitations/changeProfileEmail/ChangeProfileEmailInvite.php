<?php

/**
 * @file classes/invitation/invitations/changeProfileEmail/ChangeProfileEmailInvite.php
 *
 * Copyright (c) 2024 Simon Fraser University
 * Copyright (c) 2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ChangeProfileEmailInvite
 *
 * @brief Change Profile Email invitation
 */

namespace PKP\invitation\invitations\changeProfileEmail;

use APP\core\Application;
use APP\facades\Repo;
use Exception;
use Illuminate\Mail\Mailable;
use PKP\facades\Locale;
use PKP\invitation\core\contracts\IBackofficeHandleable;
use PKP\invitation\core\enums\InvitationAction;
use PKP\invitation\core\enums\InvitationStatus;
use PKP\invitation\core\Invitation;
use PKP\invitation\core\InvitationActionRedirectController;
use PKP\invitation\core\traits\HasMailable;
use PKP\invitation\core\traits\ShouldValidate;
use PKP\invitation\invitations\changeProfileEmail\handlers\ChangeProfileEmailInviteRedirectController;
use PKP\invitation\invitations\changeProfileEmail\payload\ChangeProfileEmailInvitePayload;
use PKP\invitation\models\InvitationModel;
use PKP\mail\mailables\ChangeProfileEmailInvitationNotify;

class ChangeProfileEmailInvite extends Invitation implements IBackofficeHandleable
{
    use HasMailable;
    use ShouldValidate;

    public const INVITATION_TYPE = 'changeProfileEmail';

    protected array $notAccessibleAfterInvite = [
        'newEmail',
    ];

    public static function getType(): string
    {
        return self::INVITATION_TYPE;
    }

    public function getNotAccessibleAfterInvite(): array
    {
        return array_merge(parent::getNotAccessibleAfterInvite(), $this->notAccessibleAfterInvite);
    }

    public function getMailable(): Mailable
    {
        $request = Application::get()->getRequest();

        $receiver = $this->getMailableReceiver();

        $mailable = new ChangeProfileEmailInvitationNotify();
        $mailable->recipients([$receiver]);
        $mailable->sender($request->getUser());

        $context = $request->getContext();

        $contextId = null;
        $locale = Locale::getLocale();
        $contactName = '';
        if (isset($context)) {
            $contextId = $context->getId();
            $locale = $context->getPrimaryLocale();
            $contactName = $context->getContactName();
        } else {
            $site = $request->getSite();
            $contactName = $site->getData('contactName');
        }

        $emailTemplate = Repo::emailTemplate()->getByKey($contextId, $mailable::getEmailTemplateKey());
        $mailable->subject($emailTemplate->getLocalizedData('subject', $locale))
            ->body($emailTemplate->getLocalizedData('body', $locale));

        $mailable->setData($locale);

        $this->setMailable($mailable);

        $acceptUrl = $this->getActionURL(InvitationAction::ACCEPT);
        $declineUrl = $this->getActionURL(InvitationAction::DECLINE);

        $this->mailable->buildViewDataUsing(function () use ($acceptUrl, $declineUrl, $contactName) {
            return [
                'acceptInvitationUrl' => $acceptUrl,
                'declineInvitationUrl' => $declineUrl,
                'newEmail' => $this->getSpecificPayload()->newEmail,
                'siteContactName' => $contactName
            ];
        });

        return $this->mailable;
    }

    protected function preInviteActions(): void
    {
        // Check if everything is in order regarding the properties
        if (!isset($this->getSpecificPayload()->newEmail)) {
            throw new Exception('The invitation can not be dispatched because the email property is missing');
        }

        // Invalidate any other related invitation
        $pendingInvitations = InvitationModel::byStatus(InvitationStatus::PENDING)
            ->byType(self::INVITATION_TYPE)
            ->byUserId($this->getUserId())
            ->get();

        foreach($pendingInvitations as $pendingInvitation) {
            $pendingInvitation->markAs(InvitationStatus::DECLINED);
        }
    }

    public function finalize(): void
    {
        $user = Repo::user()->get($this->getUserId());

        if (!$user) {
            throw new Exception();
        }

        $user->setEmail($this->getSpecificPayload()->newEmail);

        Repo::user()->edit($user);

        $this->invitationModel->markAs(InvitationStatus::ACCEPTED);
    }

    public function getInvitationActionRedirectController(): ?InvitationActionRedirectController
    {
        return new ChangeProfileEmailInviteRedirectController($this);
    }

    /**
     * @inheritDoc
     */
    protected function createPayload(): ChangeProfileEmailInvitePayload
    {
        return new ChangeProfileEmailInvitePayload();
    }

    /**
     * @inheritDoc
     */
    public function getSpecificPayload(): ChangeProfileEmailInvitePayload
    {
        return $this->payload;
    }

    /**
     * @inheritDoc
     */
    public function getValidationRules(string $context = self::VALIDATION_CONTEXT_DEFAULT): array 
    {
        return [
            'newEmail' => 'required|email',
        ];
    }
}
