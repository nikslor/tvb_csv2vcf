<?php

/**
 * This script generates a VCF file out of a CSV file
 *
 * @author      Nicolas Christener <contact@0x17.ch>
 */

// input:

/*
 * Input file is a CSV with the following format:
 * "Field1"; "Field2"; ...
 *
 * In our case, the file has the following fields:
 *  0 => Anrede
 *  1 => Vorname
 *  2 => Nachname
 *  3 => Adresse
 *  4 => PLZ
 *  5 => Ort
 *  6 => Beitrag
 *  7 => Kategorie
 *  8 => STV
 *  9 => Gruppe
 * 10 => STV Kat
 * 11 => Funktion
 * 12 => Mail
 */

// output:

/* VERSION:3.0
 * N:Nicolas;Christener;;;
 * FN:Nicolas Christener
 * CATEGORIES:foo,bar
 * EMAIL;TYPE=INTERNET,OTHER:contact@0x17.ch
 * END:VCARD
 */

require_once 'CsvIterator.class.php';

$iterator = new CsvIterator('adr.csv', ',');

$allGroups = array();

foreach($iterator as $lineCount => $line) {
	//print_r($line);

	if($lineCount === 0) {
		continue;
	}

	// jump over entries w/o e-mail adr
	if($line[12] === '') {
		continue;
	}

	$groups = array();

	/* the categories have some encoded special info 
	 * i.e 'E/Aktivmitglied' = 'Ehrenmitglied' and 'Aktivmitglied'
	 */
	if (preg_match('/([AEFLV])\/(\S+)/', $line[9], $matches)) {
		// person is in two groups
		if ($matches[1] === 'A') {
			$groups[] = 'Aktivmitglied';
		}
		elseif ($matches[1] === 'E') {
			$groups[] = 'Ehrenmitglied';
		}
		elseif ($matches[1] === 'F') {
			$groups[] = 'Freimitglied';
		}
		elseif ($matches[1] === 'L') {
			$groups[] = 'Leiter_Team';
		}
		elseif ($matches[1] === 'V') {
			$groups[] = 'Vorstand';
		}

		$groups[] = $matches[2];
	}
	else {
		if($line[9] !== '') {
			$groups[] = $line[9];
		}
	}

	// some are empty -> unknown
	if (count($groups) === 0) {
		$groups[] = 'Unbekannt';
	}

	// Because evolution has global groups, we prefix the groups to better find them
	foreach($groups as $index => $group) {
		$groups[$index] = sprintf('TVB_%s', $group);
	}

	/* We need to add the groups manually to use
	 * them as filter therefore we need to know all the used groups.. collect
	 * them
	 */
	foreach($groups as $index => $group) {
		$allGroups[$group] = $group;
	}

	$string = sprintf(
		"BEGIN:VCARD\n".
		"VERSION:3.0\n".
		"N:%s;%s;;;\n".
		"FN:%s\n".
		"CATEGORIES:%s\n".
		"EMAIL;TYPE=INTERNET,OTHER:%s\n".
		"END:VCARD\n\n",
		$line[1],
		$line[2],
		$line[1].' '.$line[2],
		implode(',', $groups),
		$line[12]
	);

	file_put_contents('import.vcf', $string, FILE_APPEND);
}

echo "add those group to evolution:\n";
print_r($allGroups);
