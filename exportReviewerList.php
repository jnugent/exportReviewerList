<?php

/**
 * @file tools/exportReviewerList.php
 *
 * Copyright (c) 2014-2022 Simon Fraser University
 * Copyright (c) 2003-2022 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class exportReviewerList
 * @ingroup tools
 *
 * @brief CLI tool for exporting reviewer data as a CSV.
 */

require(dirname(__FILE__) . '/bootstrap.inc.php');

class exportReviewerList extends CommandLineTool {

	/** @var $journalId string */
	var $journalId;

	/**
	 * Constructor.
	 * @param $argv array command-line arguments
	 */
	function __construct($argv = array()) {
		parent::__construct($argv);

		if (!isset($this->argv[0])) {
			$this->usage();
			exit(1);
		}

		$this->journalPath = $this->argv[0];
	}

	/**
	 * Print command usage information.
	 */
	function usage() {
		echo "OJS reviewer export CSV tool\n"
			. "Use this tool to generate a list of reviewers as a CSV.\n\n"
			. "Usage: {$this->scriptName} [journalPath]\n"
			. "journalPath      The journal you wish to report reviewers from.\n";
	}

	/**
	 * Execute the command.
	 */
	function execute() {
		$roleDao = DAORegistry::getDAO('RoleDAO');
		$journalDao = DAORegistry::getDAO('JournalDAO');
		$userDao = DAORegistry::getDAO('UserDAO');

		$journalPath = $this->journalPath;
		$journal = $journalDao->getByPath($journalPath);
		if ($journal) {

			$reviewers = $roleDao->getUsersByRoleId(ROLE_ID_REVIEWER, $journal->getId());

			print join("\t", array('User ID', 'User Name', 'Full Name', 'Email Address', 'Country', 'Reviewer Interests', 'Affiliation', 'Contact Address'));
			print "\n";

			while ($reviewer = $reviewers->next()) {

				$affiliation = $reviewer->getLocalizedAffiliation();
				$affiliation = preg_replace('/["\']/', '', $affiliation);
				$affiliation = preg_replace('/\s+/', ' ', $affiliation);
				$affiliation = trim($affiliation);

				$address = $reviewer->getMailingAddress();
				$address = preg_replace('/["\']/', '', $address);
				$address = preg_replace('/\s+/', ' ', $address);
				$aaddress = trim($address);

				$interests = preg_replace('/["\'\\015]/', '', $reviewer->getInterestString());
				$interests = preg_replace('/\s+/', ' ', $interests);

				print join("\t", array(	$reviewer->getId(), $reviewer->getUsername(), preg_replace('/\s+/', ' ', $reviewer->getFullName()), $reviewer->getEmail(), $reviewer->getCountryLocalized(),
										$interests, $affiliation, $address) );
				print "\n";
			}
		} else {
			echo "The journal for that path does not exist.\n";
			exit(1);
		}
	}
}

$tool = new exportReviewerList(isset($argv) ? $argv : array());
$tool->execute();
