<?php

/**
 * @file controllers/grid/settings/SetupGridHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SetupGridHandler
 * @ingroup controllers_grid_settings
 *
 * @brief Base class for setup grid handlers
 */

import('lib.pkp.classes.controllers.grid.GridHandler');

use PKP\core\JSONMessage;
use PKP\file\TemporaryFileManager;

class SetupGridHandler extends GridHandler
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->addRoleAssignment(
            [ROLE_ID_MANAGER],
            ['uploadImage']
        );
    }

    /**
     * @copydoc GridHandler::initialize()
     *
     * @param null|mixed $args
     */
    public function initialize($request, $args = null)
    {
        parent::initialize($request, $args);

        AppLocale::requireComponents(LOCALE_COMPONENT_APP_MANAGER);
    }

    /**
     * @copydoc PKPHandler::authorize()
     *
     * @param $contextRequired boolean
     */
    public function authorize($request, &$args, $roleAssignments, $contextRequired = true)
    {
        if ($contextRequired) {
            import('lib.pkp.classes.security.authorization.ContextAccessPolicy');
            $this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));
        }
        return parent::authorize($request, $args, $roleAssignments);
    }

    /**
     * Handle file uploads for cover/image art for things like Series and Categories.
     *
     * @param $args array
     * @param $request PKPRequest
     */
    public function uploadImage($args, $request)
    {
        $router = $request->getRouter();
        $context = $request->getContext();
        $user = $request->getUser();

        $temporaryFileManager = new TemporaryFileManager();
        $temporaryFile = $temporaryFileManager->handleUpload('uploadedFile', $user->getId());
        if ($temporaryFile) {
            $json = new JSONMessage(true);
            $json->setAdditionalAttributes([
                'temporaryFileId' => $temporaryFile->getId()
            ]);
            return $json;
        } else {
            return new JSONMessage(false, __('common.uploadFailed'));
        }
    }
}
