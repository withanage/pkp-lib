<?php

/**
 * @file classes/db/XMLDAO.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class XMLDAO
 * @ingroup db
 *
 * @brief Operations for retrieving and modifying objects from an XML data source.
 */


import('lib.pkp.classes.xml.XMLParser');

class XMLDAO {

	/**
	 * Parse an XML file and return data in an object.
	 * @see xml.XMLParser::parse()
	 * @param $file string
	 */
	function &parse($file) {
		$parser = new XMLParser();
		$data =& $parser->parse($file);
		return $data;
	}

	/**
	 * Parse an XML file with the specified handler and return data in an object.
	 * @see xml.XMLParser::parse()
	 * @param $file string
	 * @param $handler reference to the handler to use with the parser.
	 */
	function &parseWithHandler($file, &$handler) {
		$parser = new XMLParser();
		$parser->setHandler($handler);
		$data =& $parser->parse($file);
		return $data;
	}

	/**
	 * Parse an XML file and return data in an array.
	 * @see xml.XMLParser::parseStruct()
	 * @param $file string
	 * @param $tagsToMatch array
	 */
	function &parseStruct($file, $tagsToMatch = array()) {
		$parser = new XMLParser();
		$data =& $parser->parseStruct($file, $tagsToMatch);
		return $data;
	}
}


