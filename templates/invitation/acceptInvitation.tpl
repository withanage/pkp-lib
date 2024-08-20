<?php
{**
 * templates/management/invitation.tpl
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2003-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief The users, roles and site access settings page.
 *
 * @hook Template::Settings::access []
 *}
{extends file="layouts/backend.tpl"}
{block name="page"}
    <accept-invitation-page
            :primary-locale="primaryLocale"
            :page-title="pageTitle"
            :page-title-description="pageTitleDescription"
            :steps="steps"
            :invitation-id="invitationId"
            :invitation-key="invitationKey"
    />
{/block}
