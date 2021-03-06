<?php

/**
 * @file controllers/grid/files/review/WorkflowReviewRevisionsGridHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class WorkflowReviewRevisionsGridHandler
 * @ingroup controllers_grid_files_review
 *
 * @brief Display in workflow pages the file revisions that authors have uploaded.
 */

use PKP\submission\SubmissionFile;

import('lib.pkp.controllers.grid.files.fileList.FileListGridHandler');

class WorkflowReviewRevisionsGridHandler extends FileListGridHandler
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $stageId = (int) Application::get()->getRequest()->getUserVar('stageId');
        $fileStage = $stageId === WORKFLOW_STAGE_ID_INTERNAL_REVIEW ? SubmissionFile::SUBMISSION_FILE_INTERNAL_REVIEW_REVISION : SubmissionFile::SUBMISSION_FILE_REVIEW_REVISION;
        import('lib.pkp.controllers.grid.files.review.ReviewGridDataProvider');
        parent::__construct(
            new ReviewGridDataProvider($fileStage),
            null,
            FILE_GRID_ADD | FILE_GRID_EDIT | FILE_GRID_VIEW_NOTES | FILE_GRID_DELETE
        );

        $this->addRoleAssignment(
            [ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT],
            ['fetchGrid', 'fetchRow', 'addFile']
        );

        $this->setTitle('editor.submission.revisions');
    }
}
