<?php

/**
 * @file tools/parseCitations.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CitationsParsingTool
 * @ingroup tools
 *
 * @brief CLI tool to parse existing citations
 */

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/tools/bootstrap.inc.php');

class CitationsParsingTool extends \PKP\cliTool\CommandLineTool
{
    /**
     * Constructor.
     *
     * @param $argv array command-line arguments
     */
    public function __construct($argv = [])
    {
        parent::__construct($argv);

        if (!sizeof($this->argv)) {
            $this->usage();
            exit(1);
        }

        $this->parameters = $this->argv;
    }

    /**
     * Print command usage information.
     */
    public function usage()
    {
        echo "Parse and save submission(s) citations.\n"
            . "Usage:\n"
            . "{$this->scriptName} all\n"
            . "{$this->scriptName} context context_id [...]\n"
            . "{$this->scriptName} submission submission_id [...]\n";
    }

    /**
     * Parse citations
     */
    public function execute()
    {
        $submissionDao = DAORegistry::getDAO('SubmissionDAO'); /** @var SubmissionDAO $submissionDao */
        $citationDao = DAORegistry::getDAO('CitationDAO');
        $contextDao = Application::getContextDAO();

        switch (array_shift($this->parameters)) {
            case 'all':
                $contexts = $contextDao->getAll();
                while ($context = $contexts->next()) {
                    $submissions = $submissionDao->getByContextId($context->getId());
                    while ($submission = $submissions->next()) {
                        $this->_parseSubmission($submission);
                    }
                }
                break;
            case 'context':
                foreach ($this->parameters as $contextId) {
                    $context = $contextDao->getById($contextId);
                    if (!isset($context)) {
                        printf("Error: Skipping ${contextId}. Unknown context.\n");
                        continue;
                    }
                    $submissions = $submissionDao->getByContextId($contextId);
                    while ($submission = $submissions->next()) {
                        $this->_parseSubmission($submission);
                    }
                }
                break;
            case 'submission':
                foreach ($this->parameters as $submissionId) {
                    $submission = $submissionDao->getById($submissionId);
                    if (!isset($submission)) {
                        printf("Error: Skipping ${submissionId}. Unknown submission.\n");
                        continue;
                    }
                    $this->_parseSubmission($submission);
                }
                break;
            default:
                $this->usage();
                break;
        }
    }

    /**
     * Parse the citations of one submission
     *
     * @param Submission $submission
     */
    private function _parseSubmission($submission)
    {
        foreach ((array) $submission->getData('publications') as $publication) {
            if (!empty($publication->getData('citationsRaw'))) {
                DAORegistry::getDAO('CitationDAO')->importCitations($publication->getId(), $publication->getData('citationsRaw'));
            }
        }
    }
}

$tool = new CitationsParsingTool($argv ?? []);
$tool->execute();
