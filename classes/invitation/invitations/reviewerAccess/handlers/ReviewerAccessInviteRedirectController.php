<?php

/**
 * @file classes/invitation/invitations/handlers/reviewerAccess/ReviewerAccessInviteRedirectController.php
 *
 * Copyright (c) 2024 Simon Fraser University
 * Copyright (c) 2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ReviewerAccessInviteRedirectController
 *
 */

namespace PKP\invitation\invitations\reviewerAccess\handlers;

use APP\core\Request;
use APP\facades\Repo;
use Exception;
use PKP\core\PKPApplication;
use PKP\invitation\core\enums\InvitationAction;
use PKP\invitation\core\enums\InvitationStatus;
use PKP\invitation\core\InvitationActionRedirectController;
use PKP\invitation\invitations\reviewerAccess\ReviewerAccessInvite;

class ReviewerAccessInviteRedirectController extends InvitationActionRedirectController
{
    public function getInvitation(): ReviewerAccessInvite
    {
        return $this->invitation;
    }

    public function acceptHandle(Request $request): void
    {
        if ($this->invitation->getStatus() !== InvitationStatus::ACCEPTED) {
            $request->getDispatcher()->handle404();
        }

        $context = $request->getContext();

        $reviewAssignment = Repo::reviewAssignment()->get($this->getInvitation()->reviewAssignmentId);

        if (!$reviewAssignment) {
            throw new Exception();
        }

        $url = PKPApplication::get()->getDispatcher()->url(
            PKPApplication::get()->getRequest(),
            PKPApplication::ROUTE_PAGE,
            $context->getData('urlPath'),
            'reviewer',
            'submission',
            null,
            [
                'submissionId' => $reviewAssignment->getSubmissionId(),
                'reviewId' => $reviewAssignment->getId(),
            ]
        );

        $request->redirectUrl($url);
    }

    public function declineHandle(Request $request): void
    {
        if ($this->invitation->getStatus() !== InvitationStatus::DECLINED) {
            $request->getDispatcher()->handle404();
        }

        $context = $request->getContext();

        $url = PKPApplication::get()->getDispatcher()->url(
            PKPApplication::get()->getRequest(),
            PKPApplication::ROUTE_PAGE,
            $context->getData('urlPath'),
            'user',
            'login',
            null,
            [
            ]
        );

        $request->redirectUrl($url);
    }

    public function preRedirectActions(InvitationAction $action)
    {
        if ($action == InvitationAction::ACCEPT) {
            $this->getInvitation()->finalize();
        } elseif ($action == InvitationAction::DECLINE) {
            $this->getInvitation()->decline();
        }
    }
}
