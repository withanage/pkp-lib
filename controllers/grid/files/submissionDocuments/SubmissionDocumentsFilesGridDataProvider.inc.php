<?php
/**
 * @file controllers/grid/files/submissionDocuments/SubmissionDocumentsFilesGridDataProvider.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubmissionDocumentsFilesGridDataProvider
 * @ingroup controllers_grid_files_submissionDocuments
 *
 * @brief The base data provider for the submission documents library files grid.
 */

import('lib.pkp.classes.controllers.grid.CategoryGridDataProvider');

class SubmissionDocumentsFilesGridDataProvider extends CategoryGridDataProvider
{
    /**
     * @copydoc GridDataProvider::getAuthorizationPolicy()
     */
    public function getAuthorizationPolicy($request, $args, $roleAssignments)
    {
        import('lib.pkp.classes.security.authorization.SubmissionAccessPolicy');
        return new SubmissionAccessPolicy($request, $args, $roleAssignments, 'submissionId');
    }

    //
    // Getters and Setters
    //

    /**
     * Get the authorized submission.
     *
     * @return Submission
     */
    public function getSubmission()
    {
        return $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);
    }

    /**
     * @copydoc GridDataProvider::getRequestArgs()
     */
    public function getRequestArgs()
    {
        $submission = $this->getSubmission();
        return [
            'submissionId' => $submission->getId(),
        ];
    }

    /**
     * @copydoc CategoryGridHandler::loadCategoryData()
     *
     * @param null|mixed $filter
     */
    public function loadCategoryData($request, $fileType, $filter = null)
    {

        // Retrieve all library files for the given submission document category.
        $submission = $this->getSubmission();
        import('lib.pkp.classes.context.LibraryFile');
        $libraryFileDao = DAORegistry::getDAO('LibraryFileDAO'); /** @var LibraryFileDAO $libraryFileDao */
        $libraryFiles = $libraryFileDao->getBySubmissionId($submission->getId(), $fileType);

        return $libraryFiles->toAssociativeArray();
    }
}
