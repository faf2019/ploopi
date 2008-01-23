<?php

/*
 *
 * implements a Paice/Husk Stemmer written in PHP by Alexis Ulrich (http://alx2002.free.fr)
 *
 * Tool kit
 *
 * This code is in the public domain.
 *
 */

// sometimes, it's too long...
set_time_limit(0);

require_once('PaiceHuskStemmer.php');

// punctuation characters
$punctuation = array('.', ',', ';', ':', '!', '?', '"', '\'', '(', ')', '--');



/*
 * standardized punctuation: each punctuation mark has a space before and after it
 *
 * 	$text:	string, the text to be processed
 *	$lang:	language of the text (default: English)
 */
function standardizePunctuation($text, $lang='en') {
	// puts a space before and after a punctuation mark, 
	// whatever the number of spaces there were before and after it
	$text = preg_replace('/( )*(["\'\.,;:\(\)\?!])( )*/', ' \\2 ', $text);
	// whitespace
	$text = preg_replace('/\s/', ' ', $text);
	if ($lang == 'en') {
		// handles the didn't, couldn't...
		$text = str_replace('n \' t', 'n\'t', $text);
		// handles the o'clock
		$text = str_replace('o \' clock', 'o\'clock', $text);
	}
	return $text;
}


/*
 * uses typographic rules to return a well-written string from a standardized-punctuated one
 *
 * 	$text:	string, the text to be processed
 *	$lang:	language of the text (default: English)
 */
function localizePunctuation($text, $lang='en') {
	if ($lang == 'en') {
		$patterns = array(
					'/ " /',			# keeps the space before opening double-quote
					'/ "$/',			# removes space before ending string double-quote
					'/( )(")([\.,;:\)!\?])/',	# removes spaces before non-ending string double-quote
					'/ \' /',			# keeps the space before opening simple-quote
					'/ \'s /',			# removes the space before an 's (like in "it's")
					'/ \'$/',			# removes space before ending string simple-quote
					'/( )(\')([\.,;:\)!\?])/',	# removes spaces before non-ending string simple-quote
					'/s \'/',			# handles five minutes' walk and Thomas' car
					'/s<\/([a-z]+)> \'/i',		# the same with </xyz> closing tag
					'/ ([\.,;:\)!\?])/',		# .,;:)!? without space before
					'/\( /',			# no space after (
					'/ \. \. \./'			# ...
					);
		$replace = array(
					' "',				# keeps the space before opening double-quote
					'"',				# removes space before ending string double-quote
					'"\\3',				# removes spaces before non-ending string double-quote
					' \'',				# keeps the space before opening simple-quote
					'\'s ',				# removes the space before an 's (like in "it's")
					'\'',				# removes space before ending string simple-quote
					'\'\\3',			# removes spaces before non-ending string simple-quote
					's\' ',				# handles five minutes' walk and Thomas' car
					's</\\1>\' ',			# the same with </xyz> closing tag
					'\\1',				# .,;:)!? without space before
					'(',				# no space after (
					'...'				# ...
					);
	}
	else {
		$patterns = array();
		$replace = array();
	}
	$text = preg_replace($patterns, $replace, $text);
	return $text;
}


/*
 * indexes the given text and returns an array of three arrays:
 *	- 'original': the original text
 *	- 'modified': the modified text, ie the standardized-punctuation form
 *	- 'index': an array of three-element arrays:
 *			- 'form': the form of the word in the original text
 *			- 'index': the index of the form in the modified text
 *			- 'stem': the stem of the form
 *
 * 	$text:		string, the text to be processed
 *	$lang:		language of the text (default: English)
 */
function indexText($text, $lang='en') {
	global $punctuation;
	require_once('stoplist_'.$lang.'.inc.php');
	$indexArray = array();
	$thisText = standardizePunctuation($text, $lang);
	$thisTextWords = explode(' ',$thisText);
	$thisTextIndex = array();
	$wordIndex = 0;
	for ($i=0; $i<sizeOf($thisTextWords); $i++) {
		$form = $thisTextWords[$i];
		$word = strtolower($form);
		// words which length is 1 or 0 are not processed.
		if ((!@in_array($word, $punctuation)) && (strlen($word) > 1) && (!@in_array($word, $stoplist))) {
			$thisTextIndex[] = array('form'=>$form, 'stem'=>PaiceHuskStemmer($word,$lang), 'index'=>$wordIndex);
		}
		$wordIndex = $wordIndex + strlen($word) + 1; // the last space
	}
	return array('original'=>$text, 'modified'=>$thisText, 'index'=>$thisTextIndex);
}


/*
 *	displays some statistics for the Paice/Husk stemmer (default language: English)
 *
 * 	$texts:		array of texts
 *	$stem_lang:	language of the texts the given stemmer can handle (default: English)
 *	$if_lang:	language of the interface (default: English)
 *	$precision:	number of digits after the decimal point (default: 1)
 */
function getStatistics($texts, $stem_lang='en', $if_lang='en', $precision=2) {
	global $punctuation;
	$total_sample_words = 0;
	$total_number_stems = 0;
	$total_length = 0;
	$max_length = 0;
	$min_length = 50;
	$unique_words = array();
	$unique_stems = array();
	$nb_changed_words = 0;
	$nb_removed_characters = 0;
	$nb_removed_characters_distribution = array();
	if ($if_lang == 'fr') {
		$vocab['statistics_of_Paice_Husk_Stemmer'] = 'Statistiques du stemmer Paice/Husk.';
		$vocab['Paice_Husk_Stemmer'] = 'Stemmer Paice/Husk';
		$vocab['sample_size'] = 'Taille de l\'&eacute;chantillon&nbsp;:';
		$vocab['words'] = 'mots';
		$vocab['dispatched_in'] = 'r&eacute;partis en';
		$vocab['text_units'] = 'unit&eacute;s textuelles';
		$vocab['number_of_unique_words'] = 'Nombre de mots uniques&nbsp;:';
		$vocab['number_of_stems_found'] = 'Nombre de racines&nbsp;:';
		$vocab['number_of_unique_stems_found'] = 'Nombre de racines uniques&nbsp;:';
		$vocab['min_max_value_of_found_stems_length'] = 'Longueur min/max des racines&nbsp;:';
		$vocab['mean_value_of_found_stems_length'] = 'Longueur moyenne des racines&nbsp;:';
		$vocab['number_of_words_per_conflation_class'] = 'Nombre de mots par classe de conflation&nbsp;:';
		$vocab['index_compression'] = 'Indice de compression&nbsp;:';
		$vocab['word_change_factor'] = 'Facteur de changement par mot&nbsp;:';
		$vocab['number_of_characters_removed'] = 'Nombre de caract&egrave;res supprim&eacute;s&nbsp;:';
		$vocab['mean_removal_rate'] = 'Taux moyen de suppression&nbsp;:';
		$vocab['median_removal_rate'] = 'Taux m&eacute;dian de suppression&nbsp;:';
	}
	else {
		// if ($if_lang == 'en') {
		$vocab['statistics_of_Paice_Husk_Stemmer'] = 'Statistics of the Paice/Husk stemmer.';
		$vocab['Paice_Husk_Stemmer'] = 'Paice/Husk stemmer';
		$vocab['sample_size'] = 'Size of the sample:';
		$vocab['words'] = 'words';
		$vocab['dispatched_in'] = 'dispatched in';
		$vocab['text_units'] = 'text units';
		$vocab['number_of_unique_words'] = 'Number of unique words:';
		$vocab['number_of_stems_found'] = 'Number of stems found:';
		$vocab['number_of_unique_stems_found'] = 'Number of unique stems found:';
		$vocab['min_max_value_of_found_stems_length'] = 'Min/Max value of found stems length:';
		$vocab['mean_value_of_found_stems_length'] = 'Mean value of found stems length:';
		$vocab['number_of_words_per_conflation_class'] = 'Number of words per conflation class:';
		$vocab['index_compression'] = 'Index Compression:';
		$vocab['word_change_factor'] = 'Word Change Factor:';
		$vocab['number_of_characters_removed'] = 'Number of characters removed:';
		$vocab['mean_removal_rate'] = 'Mean removal rate:';
		$vocab['median_removal_rate'] = 'Median removal rate:';
	}
	
	for ($i=0; $i<sizeOf($texts); $i++) {
		$total_sample_words += str_word_count($texts[$i]);
		$textsArray = indexText($texts[$i], $stem_lang);
		$modified_text = $textsArray['modified'];
		$modified_words = explode(' ',$modified_text);
		foreach ($modified_words as $word)
			if ((!in_array($word,$unique_words)) && ($word != '') && (!in_array($word, $punctuation)))
				$unique_words[] = $word;
		
		$total_number_stems += sizeOf($textsArray['index']);
		foreach ($textsArray['index'] as $stems) {
			$stem_length = strlen($stems['stem']);
			$form_length = strlen(strtolower($stems['form']));
			if (!in_array($stems['stem'],$unique_stems))
				$unique_stems[] = $stems['stem'];
			if ($stems['stem'] != strtolower($stems['form']))
				$nb_changed_words++;
			$nb_removed_characters += $form_length - $stem_length;
			$nb_removed_characters_distribution[] = $form_length - $stem_length;

			$total_length += $stem_length;
			if ($stem_length < $min_length) $min_length = $stem_length;
			if ($stem_length > $max_length) $max_length = $stem_length;
		}
	}
	$total_sample_unique_words = sizeOf($unique_words);
	$total_number_unique_stems = sizeOf($unique_stems);
	
	sort($nb_removed_characters_distribution);
	$sizeOf_nb_removed_characters_distribution = sizeOf($nb_removed_characters_distribution);
	if ($sizeOf_nb_removed_characters_distribution%2 == 0)
		$median_removal_rate = 0.5 * ($nb_removed_characters_distribution[$sizeOf_nb_removed_characters_distribution/2] + $nb_removed_characters_distribution[1+($sizeOf_nb_removed_characters_distribution/2)]);
	else $median_removal_rate = $nb_removed_characters_distribution[($sizeOf_nb_removed_characters_distribution+1)/2];

	echo "<b>${vocab['statistics_of_Paice_Husk_Stemmer']}</b><br><br>\n";
	echo $vocab['sample_size'].' '.$total_sample_words.' '.$vocab['words'].' ('.$vocab['dispatched_in'].' '.sizeOf($texts).' '.$vocab['text_units'].")<br>\n";
	echo $vocab['number_of_unique_words']." $total_sample_unique_words<br>\n";
	echo "<br>\n";
	echo '<table border="1" rules="group">'."\n";
	echo '<thead><th></th><th>&nbsp;'.$vocab['Paice_Husk_Stemmer']."&nbsp;</th></thead>\n";
	echo "<tfoot></tfoot>\n";
	
	echo '<tbody><tr><td align="right">'.$vocab['number_of_stems_found']."&nbsp;</td>\n";
	echo "<td align=\"center\">$total_number_unique_stems</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo '<td align="right">'.$vocab['number_of_unique_stems_found']."&nbsp;</td>\n";
	echo "<td align=\"center\">$total_number_unique_stems</td>\n";
	echo "</tr></tbody>\n";
	
	echo '<tbody><tr><td align="right">'.$vocab['min_max_value_of_found_stems_length']."&nbsp;</td>\n";
	echo "<td align=\"center\">$min_length / $max_length</td>\n";
	echo "</tr>";
	echo '<tr><td align="right">'.$vocab['mean_value_of_found_stems_length']."&nbsp;</td>";
	echo '<td align="center">'.round(($total_length/$total_number_stems),$precision)."</td>\n";
	echo "</tr></tbody>\n";

	echo '<tbody><tr><td align="right">'.$vocab['number_of_words_per_conflation_class']."&nbsp;</td>\n";
	echo '<td align="center">'.round($total_sample_unique_words/$total_number_unique_stems,$precision)."</td>\n";
	echo '<tr><td align="right">'.$vocab['index_compression']."&nbsp;</td>\n";
	echo '<td align="center">'.round(($total_sample_unique_words - $total_number_unique_stems)/$total_sample_unique_words,$precision)."</td>\n";
	echo '<tr><td align="right">'.$vocab['word_change_factor']."&nbsp;</td>\n";
	echo '<td align="center">'.round($nb_changed_words/$total_sample_words,$precision)."</td>\n";
	echo "</tr></tbody>\n";

	echo '<tbody><tr><td align="right">'.$vocab['number_of_characters_removed']."&nbsp;</td>\n";
	echo "<td align=\"center\">$nb_removed_characters</td>\n";
	echo '<tr><td align="right">'.$vocab['mean_removal_rate']."&nbsp;</td>\n";
	echo '<td align="center">'.round($nb_removed_characters/$total_number_stems,$precision)."</td>\n";
	echo '<tr><td align="right">'.$vocab['median_removal_rate']."&nbsp;</td>\n";
	echo "<td align=\"center\">$median_removal_rate</td>\n";
	echo "</tr></tbody>\n";

	echo "</table>\n";
}


/*
 * lists the most common suffixes of a corpus in the given $language
 * which are not listed in the matching words2stems_<language>.inc.php file
 *
 * $language:		text language (default: English)
 * $checkWords2Stems:	boolean (default value: False)
 *			if True, lists only the words which are not listed in the matching words2stems_<language>.inc.php file
 */
function listSuffixes($language='en', $checkWords2Stems = False) {
	// opens the file
	$corpus_handle = @fopen('corpus_'.$language.'.txt','r');
	if ($checkWords2Stems) require_once('words2stems_'.$language.'.inc.php');
	if (!$corpus_handle)
		die('Failed to open the file corpus_'.$language.'.txt');
		
	$total_words = 0;
	$words = array();
	$line = trim(fgets($corpus_handle));
	while (!feof($corpus_handle)) {
		// removes the punctuation
		if (($line != '') && (!preg_match('/^p[0-9]/',$line))) {
			$line = preg_replace('/( )*(["\'\.,;:\(\)\?!])( )*/', ' ', $line);
			
			// extract the words
			$thoseWords = explode(' ',$line);
			if (!preg_match('/[0-9]+/',$word))
				$total_words += sizeOf($thoseWords);
			
			// creates an array of reversed words as key and their number of occurrences as value
			// if they have a length of at least 4 characters
			foreach($thoseWords as $word) {
				$word = strrev(strtolower(trim($word)));
				if ((strlen($word) > 3) && (!preg_match('/[0-9]+/',$word))) {
					if (!array_key_exists($word,$words)) $words[$word] = 1;
					else $words[$word]++;
				}
			}
		}
		$line = trim(fgets($corpus_handle));
	}
	
	// computes their frequency (in percentage of their number of occurrences)
	// such as x% of the words in the corpus have the form...
	foreach($words as $word => $occ) {
		$words[$word] = round($occ/$total_words,7)*100;
	}
	
	// sorts them by frequency
	arsort($words);
	
	// removes those having a frequency less than 0.0001 %
	while (array_pop($words) < 0.0001);

	if ($checkWords2Stems) {
		// removes the words already handled by 'words2stems_<language>.inc.php'
		// rewrites $words2stems keys utf8-encoded
		$new_words2stems = array();
		foreach($words2stems as $word => $stem)
			$new_words2stems[utf8_decode($word)] = $stem;
		$words2stems = $new_words2stems;
		unset($new_words2stems);
		
		$new_words = array();
		foreach($words as $word => $freq) {
			if (!array_key_exists(strrev($word), $words2stems))
				$new_words[$word] = $freq;
		}
		$words = $new_words;
		unset($new_words);
	}
	
	$words_per_frequency = array();
	foreach($words as $word => $freq) {
		if (!in_array($freq, $words_per_frequency)) {
			$words_per_frequency["$freq"][] = $word;
		}
	}
	unset($words);
	
	// writes them out in a text file
	$stems_handle = @fopen('corpus_stems_'.$language.'.txt','w');
	if (!$stems_handle)
		die('Failed to open the file corpus_stems_'.$language.'.txt');
	foreach($words_per_frequency as $frequency => $words) {
		// sorts them by inverse suffixes
		sort($words);
		if (!fwrite($stems_handle,"\nfrequency: ".$frequency."\n---------\n"))
			die('Failed to write in the file corpus_stems_'.$language.'.txt');
		foreach ($words as $word) {
			$reversed = strrev($word);
			if (!fwrite($stems_handle,$word."\t(".$reversed.")\n"))
				die('Failed to write in the file corpus_stems_'.$language.'.txt');
		}
	}
		
	// closes the files
	fclose($corpus_handle);
	fclose($stems_handle);
	
	// displays ok message
	echo "Corpus size: $total_words forms.<br>";
	echo 'The stems of the file <b>corpus_'.$language.'.txt</b> have been saved in <b>corpus_stems_'.$language.'.txt</b>.';
}


/*
 * displays for each word of the words2stems array the word and the returned stem
 * after having processed it with the new set of rules if the returned stem is 
 * different from the expected one.
 *
 * $language:	text language (default: French)
 * $if_lang:	language of the interface (default: English)
 */ 
function developPaiceHusk($language='fr', $if_lang='en') {
	require_once('words2stems_'.$language.'.inc.php');
	if ($if_lang == 'fr') {
		$vocab['troubles'] = 'Des probl&egrave;mes sont encore pr&eacute;sents avec les racines suivantes...';
		$vocab['instead_of'] = 'au lieu de';
		$vocab['exact_match'] = 'Les racines trouv&eacute;es correspondent &agrave; celles list&eacute;es dans le fichier';
		$vocab['file'] = '';
	}
	else {
		// if ($if_lang == 'en') {
		$vocab['troubles'] = 'There\'s still some troubles with the following stems...';
		$vocab['instead_of'] = 'instead of';
		$vocab['exact_match'] = 'The stems found match exactly the ones listed in the';
		$vocab['file'] = ' file';
	}
	
	$cpt = 0;
	foreach($words2stems as $word => $stem) {
		$stemmer_stem = PaiceHuskStemmer($word,$language);
		if ($stem != $stemmer_stem) {
			if ($cpt == 0) echo '<br><b>',$vocab['troubles'].'</b><br><blockquote>';
			$cpt++;
			echo "$cpt. $word => <i>$stemmer_stem</i> ".$vocab['instead_of']." <i>$stem</i><br>";
		}
	}
	if ($cpt != 0) echo '</blockquote>';
	else echo '<br><b>'.$vocab['exact_match'].' <i>words2stems_'.$language.'</i>'.$vocab['file'].'.</b>';
}

?>
