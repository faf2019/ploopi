<?php
/*
	Copyright (c) 2002-2007 Netlor
	Copyright (c) 2007-2008 Ovensia
	Contributors hold Copyright (c) to their code submissions.

	implements a Paice/Husk Stemmer written in PHP by Alexis Ulrich (http://alx2002.free.fr)

	This file is part of Ploopi.

	Ploopi is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	Ploopi is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with Ploopi; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class stemmer
{
	function stemmer()
	{
		$this->rule_pattern = array();
		$this->PaiceHuskStemmerRules = array();

		include 'stemmer/PaiceHuskStemRules_fr.php';
		include 'stemmer/PaiceHuskStemRules_en.php';
	}


	// the rule patterns include all accented forms for a given language

	// we include both languages to prevent I/O of files everywhere

	// returns the number of the first rule from the rule number $rule_number
	// that can be applied to the given reversed form
	// returns -1 if no rule can be applied, ie the stem has been found
	function getFirstRule($reversed_form, $rule_number, $language='en')
	{

		$nb_rules = sizeOf($this->PaiceHuskStemmerRules[$language]);
		for ($i=$rule_number; $i<$nb_rules; $i++)
		{
			// gets the letters from the current rule
			$rule = $this->PaiceHuskStemmerRules[$language][$i];
			$rule = preg_replace($this->rule_pattern[$language], "\\1", $rule);
			if (strncasecmp($rule,$reversed_form,strlen($rule)) == 0) return $i;
		}
		return -1;
	}


	/*
	 * Check the acceptability of a stem for a given language
	 *
	 * $reversed_stem:	the stem to check in reverse form
	 * $language:		text language (default: French)
	 */
	function checkAcceptability($reversed_stem, $language='en')
	{
		switch ($language)
		{
			case 'en': # English
				if (preg_match("/[aeiouy]$/",$reversed_stem))
				{
					// if the form starts with a vowel then at least two letters must remain after stemming (e.g., "owed"/"owing" --> "ow", but not "ear" --> "e")
					return (strlen($reversed_stem) >= 2);
				}
				else
				{
					// if the form starts with a consonant then at least three letters must remain after stemming
					if (strlen($reversed_stem) < 3) return False;
					// and at least one of these must be a vowel or "y" (e.g., "saying" --> "say" and "crying" --> "cry", but not "string" --> "str", "meant" --> "me" or "cement" --> "ce")
					return (preg_match("/[aeiouy]/",$reversed_stem));
				}
			break;

			case 'fr': # French
				if (preg_match("/[aàâeèéêëiîïoôuûùy]$/",$reversed_stem))
				{
					// if the form starts with a vowel then at least two letters must remain after stemming (e.g.: "étaient" --> "ét")
					return (strlen($reversed_stem) > 2);
				}
				else
				{
					// if the form starts with a consonant then at least two letters must remain after stemming
					if (strlen($reversed_stem) <= 2)
					{
						return False;
					}
					// and at least one of these must be a vowel or "y"
					return (preg_match("/[aàâeèéêëiîïoôuûùy]/",$reversed_stem));
				}
			break;
		break;

		default:
		die("Error in checkAcceptability function: the language <i>$language</i> is not supported.");
		}
	}


	/*
	 * the actual Paice/Husk stemmer
	 * which returns a stem for the given form
	 *
	 * $form:		the word for which we want the stem
	 * $language:	the word language (default: French)
	 */
	function get($form, $language='en')
	{
		$intact = True;
		$stem_found = False;
		$reversed_form = strrev($form);
		$rule_number = 0;
		// that loop goes through the rules' array until it finds an ending one (ending by '.') or the last one ('end0.')
		while (True)
		{
			$rule_number = $this->getFirstRule($reversed_form, $rule_number, $language);
			if ($rule_number == -1)
			{
				// no other rule can be applied => the stem has been found
				break;
			}
			$rule = $this->PaiceHuskStemmerRules[$language][$rule_number];
			preg_match($this->rule_pattern[$language], $rule, $matches);
			if (($matches[2] != '*') || ($intact))
			{
				$reversed_stem = $matches[4] . substr($reversed_form,$matches[3],strlen($reversed_form)-$matches[3]);
				if ($this->checkAcceptability($reversed_stem,$language))
				{
					$reversed_form = $reversed_stem;
					if ($matches[5] == '.') break;
				}
				else
				{
					// go to another rule
					$rule_number++;
				}
			}
			else
			{
				// go to another rule
				$rule_number++;
			}
		}
		return strrev($reversed_form);
	}
}
