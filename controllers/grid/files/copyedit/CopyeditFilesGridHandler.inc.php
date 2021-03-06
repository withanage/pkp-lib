<?php

/**
 * @file controllers/grid/files/copyedit/CopyeditFilesGridHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CopyeditFilesGridHandler
 * @ingroup controllers_grid_files_copyedit
 *
 * @brief Handle the copyedited files grid
 */

import('lib.pkp.controllers.grid.files.fileList.FileListGridHandler');

use PKP\core\JSONMessage;

class CopyeditFilesGridHandler extends FileListGridHandler
{
    /**
     * Constructor
     *  FILE_GRID_* capabilities set.
     */
    public function __construct()
    {
        import('lib.pkp.controllers.grid.files.copyedit.CopyeditFilesGridDataProvider');
        parent::__construct(
            new CopyeditFilesGridDataProvider(),
            null
        );
        $this->addRoleAssignment(
            [
                ROLE_ID_SUB_EDITOR,
                ROLE_ID_MANAGER,
                ROLE_ID_ASSISTANT,
                ROLE_ID_AUTHOR,
            ],
            [
                'fetchGrid', 'fetchRow',
            ]
        );
        $this->addRoleAssignment(
            [
                ROLE_ID_SUB_EDITOR,
                ROLE_ID_MANAGER,
                ROLE_ID_ASSISTANT
            ],
            [
                'selectFiles'
            ]
        );


        $this->setTitle('submission.copyedited');
    }

    //
    // Public handler methods
    //
    /**
     * @copydoc GridHandler::initialize()
     *
     * @param null|mixed $args
     */
    public function initialize($request, $args = null)
    {
        if (0 != count(array_intersect(
            $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES),
            [ROLE_ID_MANAGER, ROLE_ID_ASSISTANT, ROLE_ID_SUB_EDITOR]
            // Authors may also view this grid, and shouldn't be able to do anything (just view).
        ))) {
            $this->setCapabilities(new FilesGridCapabilities(FILE_GRID_EDIT | FILE_GRID_MANAGE | FILE_GRID_VIEW_NOTES | FILE_GRID_DELETE));
        }
        parent::initialize($request, $args);
    }

    /**
     * Show the form to allow the user to select files from previous stages
     *
     * @param $args array
     * @param $request PKPRequest
     *
     * @return JSONMessage JSON object
     */
    public function selectFiles($args, $request)
    {
        import('lib.pkp.controllers.grid.files.copyedit.form.ManageCopyeditFilesForm');
        $manageCopyeditFilesForm = new ManageCopyeditFilesForm($this->getSubmission()->getId());
        $manageCopyeditFilesForm->initData();
        return new JSONMessage(true, $manageCopyeditFilesForm->fetch($request));
    }
}
