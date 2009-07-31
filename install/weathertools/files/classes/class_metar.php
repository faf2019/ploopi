<?
require_once 'Services/Weather/Metar.php'; // PEAR

// Traducation partielle de
// http://meteocentre.com/doc/metarf.html

class metar extends Services_Weather_Metar
{
    function metar()
    {
        $options = array();
        $error = null;
        
        if (!defined("SERVICES_WEATHER_DEBUG")) define("SERVICES_WEATHER_DEBUG", false);
        
        $perror = null;
        $status = null;
        
        $this->Services_Weather_Common($options, $perror);
        if (Services_Weather::isError($perror)) {
            $error = $perror;
            return;
        }

        // Set options accordingly
        if (isset($options["dsn"])) {
            if (isset($options["dbOptions"])) {
                $status = $this->setMetarDB($options["dsn"], $options["dbOptions"]);
            } else {
                $status = $this->setMetarDB($options["dsn"]);
            }
        }
        if (Services_Weather::isError($status)) {
            $error = $status;
            return;
        }

        // Setting the data sources for METAR and TAF - have to watch out for older API usage
        if (($source = isset($options["source"])) || isset($options["sourceMetar"])) {
            $sourceMetar = $source ? $options["source"] : $options["sourceMetar"];
            if (($sourcePath = isset($options["sourcePath"])) || isset($options["sourcePathMetar"])) {
                $sourcePathMetar = $sourcePath ? $options["sourcePath"] : $options["sourcePathMetar"];
            } else {
                $sourcePathMetar = "";
            }
        } else {
            $sourceMetar = "http";
            $sourcePathMetar = "";
        }
        if (isset($options["sourceTaf"])) {
            $sourceTaf = $options["sourceTaf"];
            if (isset($option["sourcePathTaf"])) {
                $sourcePathTaf = $options["sourcePathTaf"];
            } else {
                $soucePathTaf = "";
            }
        } else {
            $sourceTaf = "http";
            $sourcePathTaf = "";
        }
        $status = $this->setMetarSource($sourceMetar, $sourcePathMetar, $sourceTaf, $sourcePathTaf);
        if (Services_Weather::isError($status)) {
            $error = $status;
            return;
        }
    }	
	
	function _parseWeatherData($data)
	{
		static $compass;
		static $clouds;
		static $cloudtypes;
		static $conditions;
		static $sensors;
		if (!isset($compass)) {
			$compass = array(
				"N", "NNE", "NE", "ENE",
				"E", "ESE", "SE", "SSE",
				"S", "SSO", "SO", "OSO",
				"O", "ONO", "NO", "NNO"
			);			
			$clouds    = array(
				"skc"         => "ciel clair", // sky clear
				"nsc"         => "pas de nuages", // no significant cloud
				"few"         => "peu de nuages", // few
				"sct"         => "nuages épars", // scattered
				"bkn"         => "nuages dispersés", // broken (6/10 => 9/10)
				"ovc"         => "ciel couvert", // overcast
				"vv"          => "ciel obscurci (visbilité verticale)", //vertical visibility
				"tcu"         => "cumulus bourgeonnants", // Towering Cumulus
				"cb"          => "cumulonimbus", // Cumulonimbus
				"clr"         => "dégagé en dessous de 3 657 m" // clear below 12,000 ft
			);
			$cloudtypes = array(
				"low" => array(
					"/" => "Couvert", // Overcast
					"0" => "Aucun", // None                              
					"1" => "Cumulus (beau temps)", // fair weather
					"2" => "Cumulus (bourgeonnant)", // towering                  
					"3" => "Cumulonimbus (sans enclume)", // no anvil
					"4" => "Stratocumulus (provenant de Cumulus)", // from cumulus       
					"5" => "Stratocumulus (ne provenant pas de Cumulus)", // not cumulus
					"6" => "Stratus ou Fractostratus (dégagé)", // fair     
					"7" => "Cumulus Fractus/Stratus Fractus (mauvais temps)", // Fractocumulus/Fractostratus (bad weather)
					"8" => "Cumulus et Stratocumulus", // Cumulus and Stratocumulus          
					"9" => "Cumulonimbus (orage)" // Cumulonimbus (thunderstorm)
				),
				"middle" => array(
					"/" => "Couvert", // Overcast
					"0" => "Aucun", // None                                
					"1" => "Altostratus (fin)", // thin
					"2" => "Altostratus (épais)", // thick               
					"3" => "Altocumulus (fin)", // thin
					"4" => "Altocumulus (inégal)", // patchy            
					"5" => "Altocumulus (épaississement)", // thickening
					"6" => "Altocumulus (provenant de Cumulus)",          
					"7" => "Altocumulus (avec Altocumulus, Altostratus, Nimbostratus)",
					"8" => "Altocumulus (avec tourelles)", // turrets            
					"9" => "Altocumulus (chaotique)" // chaotic
				),
				"high" => array(
					"/" => "Couvert", // Overcast
					"0" => "Aucun", // None                                
					"1" => "Cirrus (filaments)",
					"2" => "Cirrus (dense)",                      
					"3" => "Cirrus (souvent avec Cumulonimbus)", // (often w/ Cumulonimbus)
					"4" => "Cirrus (épaississement)", // thickening                 
					"5" => "Cirrus/Cirrostratus (bas dans le ciel)", // low in sky
					"6" => "Cirrus/Cirrostratus (haut dans le ciel)", // high in sky
					"7" => "Cirrostratus (tout le ciel)", // entire sky
					"8" => "Cirrostratus (partiel)", // partial            
					"9" => "Cirrocumulus ou Cirrocumulus/Cirrus/Cirrostratus"
				)
			);
			$conditions = array(
				"+"           => "gros(se)", // heavy                   
				"-"           => "léger(e)", // light

				"vc"          => "au voisinage", // vicinity                
				"re"          => "récents", // recent
				"nsw"         => "pas de temps significatif", // no significant weather

				"mi"          => "mince", // shallow                 
				"bc"          => "bancs", // patches
				"pr"          => "partiel", // partial                 
				"ts"          => "orage", // thunderstorm
				"bl"          => "chasse poussière/sable/neige élevée",                 
				"sh"          => "averse(s)", // showers
				"dr"          => "chasse poussière/sable/neige basse", // low drifting            
				"fz"          => "givrant",
				"xx"          => "violent",

				"dz"          => "bruine",                 
				"ra"          => "pluie",
				"sn"          => "neige",                    
				"sg"          => "neige en grains", // snow grains
				"ic"          => "cristaux de glace", // ice crystals           
				"pe"          => "granules de glace", // ice pellets
				"pl"          => "grésil",   // ice pellets         
				"gr"          => "grêle", // hail
				"gs"          => "grésil et/ou neige roulée",
				"up"          => "inconnu",

				"br"          => "brume", // mist                    
				"fg"          => "brouillard", // fog
				"fu"          => "fumée", // smoke                   
				"va"          => "cendres volcaniques", // volcanic ash
				"sa"          => "sable", // sand                    
				"hz"          => "brume sèche", // haze
				"py"          => "gouttes", //  spray   
				"du"          => "poussière généralisée", // widespread dust

				"sq"          => "bourrasques/grains", // squall    
				"ss"          => "tempête de sable", // sandstorm
				"ds"          => "tempête de poussière", // duststorm               
				"po"          => "tourbillons de poussière/sable", // well developed dust/sand whirls
				"fc"          => "trombe, entonnoir nuageux", // funnel cloud

				"+fc"         => "tornade/trombe marine" // tornado/waterspout
			);
			$sensors = array(
				"rvrno"  => "Runway Visual Range Detector offline",
				"pwino"  => "Present Weather Identifier offline",
				"pno"    => "Tipping Bucket Rain Gauge offline",
				"fzrano" => "Freezing Rain Sensor offline",
				"tsno"   => "Lightning Detection System offline",
				"visno"  => "2nd Visibility Sensor offline",
				"chino"  => "2nd Ceiling Height Indicator offline"
			);
		}

		$metarCode = array(
			"report"      => "METAR|SPECI",
			"station"     => "\w{4}",
			"update"      => "(\d{2})?(\d{4})Z",
			"type"        => "AUTO|COR",
			"wind"        => "(\d{3}|VAR|VRB)(\d{2,3})(G(\d{2,3}))?(FPS|KPH|KT|KTS|MPH|MPS)",
			"windVar"     => "(\d{3})V(\d{3})",
			"visFrac"     => "(\d{1})",
			"visibility"  => "(\d{4})|((M|P)?((\d{1,2}|((\d) )?(\d)\/(\d))(SM|KM)))|(CAVOK)",
			"runway"      => "R(\d{2})(\w)?\/(P|M)?(\d{4})(FT)?(V(P|M)?(\d{4})(FT)?)?(\w)?",
			"condition"   => "(-|\+|VC|RE|NSW)?(MI|BC|PR|TS|BL|SH|DR|FZ)?((DZ)|(RA)|(SN)|(SG)|(IC)|(PE)|(PL)|(GR)|(GS)|(UP))*(BR|FG|FU|VA|DU|SA|HZ|PY)?(PO|SQ|FC|SS|DS)?",
			"clouds"      => "(SKC|CLR|NSC|((FEW|SCT|BKN|OVC|VV)(\d{3}|\/{3})(TCU|CB)?))",
			"temperature" => "(M)?(\d{2})\/((M)?(\d{2})|XX|\/\/)?",
			"pressure"    => "(A)(\d{4})|(Q)(\d{4})",
			"trend"       => "NOSIG|TEMPO|BECMG",
			"remark"      => "RMK"
		);

		$remarks = array(
			"nospeci"     => "NOSPECI",
			"autostation" => "AO(1|2)",
			"presschg"    => "PRES(R|F)R",
			"seapressure" => "SLP(\d{3}|NO)",
			"precip"      => "(P|6|7)(\d{4}|\/{4})",
			"snowdepth"   => "4\/(\d{3})",
			"snowequiv"   => "933(\d{3})",
			"cloudtypes"  => "8\/(\d|\/)(\d|\/)(\d|\/)",
			"sunduration" => "98(\d{3})",
			"1htempdew"   => "T(0|1)(\d{3})((0|1)(\d{3}))?",
			"6hmaxtemp"   => "1(0|1)(\d{3})",
			"6hmintemp"   => "2(0|1)(\d{3})",
			"24htemp"     => "4(0|1)(\d{3})(0|1)(\d{3})",
			"3hpresstend" => "5([0-8])(\d{3})",
			"sensors"     => "RVRNO|PWINO|PNO|FZRANO|TSNO|VISNO|CHINO",
			"maintain"    => "[\$]"
		);

		if (SERVICES_WEATHER_DEBUG) {
			for ($i = 0; $i < sizeof($data); $i++) {
				echo $data[$i]."\n";
			}
		}
		// Start with parsing the first line for the last update
		$weatherData = array();
		$weatherData["station"]   = "";
		$weatherData["dataRaw"]   = implode(" ", $data);
		$weatherData["update"]    = strtotime(trim($data[0])." GMT");
		$weatherData["updateRaw"] = trim($data[0]);
		// and prepare the rest for stepping through
		array_shift($data);
		$metar = explode(" ", preg_replace("/\s{2,}/", " ", implode(" ", $data)));

		// Add a few local variables for data processing
		$trendCount = 0;             // If we have trends, we need this
		$pointer    =& $weatherData; // Pointer to the array we add the data to
		for ($i = 0; $i < sizeof($metar); $i++) {
			// Check for whitespace and step loop, if nothing's there
			$metar[$i] = trim($metar[$i]);
			if (!strlen($metar[$i])) {
				continue;
			}

			if (SERVICES_WEATHER_DEBUG) {
				$tab = str_repeat("\t", 3 - floor((strlen($metar[$i]) + 2) / 8));
				echo "\"".$metar[$i]."\"".$tab."-> ";
			}

			// Initialize some arrays
			$result   = array();
			$resultVF = array();
			$lresult  = array();

			$found = false;
			foreach ($metarCode as $key => $regexp) {
				// Check if current code matches current metar snippet
				if (($found = preg_match("/^".$regexp."$/i", $metar[$i], $result)) == true) {
					switch ($key) {
						case "station":
							$pointer["station"] = $result[0];
							unset($metarCode["station"]);
							break;
						case "wind":
							// Parse wind data, first the speed, convert from kt to chosen unit
							if ($result[5] == "KTS") {
								$result[5] = "KT";
							}
							$pointer["wind"] = $this->convertSpeed($result[2], $result[5], "mph");
							if ($result[1] == "VAR" || $result[1] == "VRB") {
								// Variable winds
								$pointer["windDegrees"]   = "Variable";
								$pointer["windDirection"] = "Variable";
							} else {
								// Save wind degree and calc direction
								$pointer["windDegrees"]   = intval($result[1]);
								$pointer["windDirection"] = $compass[round($result[1] / 22.5) % 16];
							}
							if (is_numeric($result[4])) {
								// Wind with gusts...
								$pointer["windGust"] = $this->convertSpeed($result[4], $result[5], "mph");
							}
							break;
						case "windVar":
							// Once more wind, now variability around the current wind-direction
							$pointer["windVariability"] = array("from" => intval($result[1]), "to" => intval($result[2]));
							break;
						case "visFrac":
							// Possible fractional visibility here. Check if it matches with the next METAR piece for visibility
							if (!isset($metar[$i + 1]) || !preg_match("/^".$metarCode["visibility"]."$/i", $result[1]." ".$metar[$i + 1], $resultVF)) {
								// No next METAR piece available or not matching. Match against next METAR code
								$found = false;
								break;
							} else {
								// Match. Hand over result and advance METAR
								if (SERVICES_WEATHER_DEBUG) {
									echo $key."\n";
									echo "\"".$result[1]." ".$metar[$i + 1]."\"".str_repeat("\t", 2 - floor((strlen($result[1]." ".$metar[$i + 1]) + 2) / 8))."-> ";
								}
								$key = "visibility";
								$result = $resultVF;
								$i++;
							}
						case "visibility":
							$pointer["visQualifier"] = "à";
							if (is_numeric($result[1]) && ($result[1] == 9999)) {
								// Upper limit of visibility range
								$visibility = $this->convertDistance(10, "km", "sm");
								$pointer["visQualifier"] = "Supérieur à";
							} elseif (is_numeric($result[1])) {
								// 4-digit visibility in m
								$visibility = $this->convertDistance(($result[1]/1000), "km", "sm");
							} elseif (!isset($result[11]) || $result[11] != "CAVOK") {
								if ($result[3] == "M") {
									$pointer["visQualifier"] = "Inférieur à";
								} elseif ($result[3] == "P") {
									$pointer["visQualifier"] = "Supérieur à";
								}
								if (is_numeric($result[5])) {
									// visibility as one/two-digit number
									$visibility = $this->convertDistance($result[5], $result[10], "sm");
								} else {
									// the y/z part, add if we had a x part (see visibility1)
									if (is_numeric($result[7])) {
										$visibility = $this->convertDistance($result[7] + $result[8] / $result[9], $result[10], "sm");
									} else {
										$visibility = $this->convertDistance($result[8] / $result[9], $result[10], "sm");
									}
								}
							} else {
								$pointer["visQualifier"] = "Supérieur à";
								$visibility = $this->convertDistance(10, "km", "sm");
								$pointer["clouds"] = array(array("amount" => "pas de nuage en dessous", "height" => 5000));
								$pointer["condition"] = "pas de temps significatif"; // no significant weather
							}
							$pointer["visibility"] = $visibility;
							break;
						case "condition":
							// First some basic setups
							if (!isset($pointer["condition"])) {
								$pointer["condition"] = "";
							} elseif (strlen($pointer["condition"]) > 0) {
								$pointer["condition"] .= ",";
							}

							if (in_array(strtolower($result[0]), $conditions)) {
								// First try matching the complete string
								$pointer["condition"] .= " ".$conditions[strtolower($result[0])];
							} else {
								// No luck, match part by part
								array_shift($result);
								$result = array_unique($result);
								foreach ($result as $condition) {
									if (strlen($condition) > 0) {
										$pointer["condition"] .= " ".$conditions[strtolower($condition)];
									}
								}
							}
							$pointer["condition"] = trim($pointer["condition"]);
							break;
						case "clouds":
							if (!isset($pointer["clouds"])) {
								$pointer["clouds"] = array();
							}

							if (sizeof($result) == 5) {
								// Only amount and height
								$cloud = array("amount" => $clouds[strtolower($result[3])]);
								if ($result[4] == "///") {
									$cloud["height"] = "au niveau de la station ou en dessous";
								} else {
									$cloud["height"] = $result[4] * 100;
								}
							} elseif (sizeof($result) == 6) {
								// Amount, height and type
								$cloud = array("amount" => $clouds[strtolower($result[3])], "type" => $clouds[strtolower($result[5])]);
								if ($result[4] == "///") {
									$cloud["height"] = "au niveau de la station ou en dessous";
								} else {
									$cloud["height"] = $result[4] * 100;
								}
							} else {
								// SKC or CLR or NSC
								$cloud = array("amount" => $clouds[strtolower($result[0])]);
							}
							$pointer["clouds"][] = $cloud;
							break;
						case "temperature":
							// normal temperature in first part
							// negative value
							if ($result[1] == "M") {
								$result[2] *= -1;
							}
							$pointer["temperature"] = $this->convertTemperature($result[2], "c", "f");
							if (sizeof($result) > 4) {
								// same for dewpoint
								if ($result[4] == "M") {
									$result[5] *= -1;
								}
								$pointer["dewPoint"] = $this->convertTemperature($result[5], "c", "f");
								$pointer["humidity"] = $this->calculateHumidity($result[2], $result[5]);
							}
							if (isset($pointer["wind"])) {
								// Now calculate windchill from temperature and windspeed
								$pointer["feltTemperature"] = $this->calculateWindChill($pointer["temperature"], $pointer["wind"]);
							}
							break;
						case "pressure":
							if ($result[1] == "A") {
								// Pressure provided in inches
								$pointer["pressure"] = $result[2] / 100;
							} elseif ($result[3] == "Q") {
								// ... in hectopascal
								$pointer["pressure"] = $this->convertPressure($result[4], "hpa", "in");
							}
							break;
						case "trend":
							// We may have a trend here... extract type and set pointer on
							// created new array
							if (!isset($weatherData["trend"])) {
								$weatherData["trend"] = array();
								$weatherData["trend"][$trendCount] = array();
							}
							$pointer =& $weatherData["trend"][$trendCount];
							$trendCount++;
							$pointer["type"] = $result[0];
							while (isset($metar[$i + 1]) && preg_match("/^(FM|TL|AT)(\d{2})(\d{2})$/i", $metar[$i + 1], $lresult)) {
								if ($lresult[1] == "FM") {
									$pointer["from"] = $lresult[2].":".$lresult[3];
								} elseif ($lresult[1] == "TL") {
									$pointer["to"] = $lresult[2].":".$lresult[3];
								} else {
									$pointer["at"] = $lresult[2].":".$lresult[3];
								}
								// As we have just extracted the time for this trend
								// from our METAR, increase field-counter
								$i++;
							}
							break;
						case "remark":
							// Remark part begins
							$metarCode = $remarks;
							$weatherData["remark"] = array();
							break;
						case "autostation":
							// Which autostation do we have here?
							if ($result[1] == 0) {
								$weatherData["remark"]["autostation"] = "Automatic weatherstation w/o precipitation discriminator";
							} else {
								$weatherData["remark"]["autostation"] = "Automatic weatherstation w/ precipitation discriminator";
							}
							unset($metarCode["autostation"]);
							break;
						case "presschg":
							// Decoding for rapid pressure changes
							if (strtolower($result[1]) == "r") {
								$weatherData["remark"]["presschg"] = "Pression augmente rapidement"; // Pressure rising rapidly
							} else {
								$weatherData["remark"]["presschg"] = "Pression chute rapidement"; // Pressure falling rapidly
							}
							unset($metarCode["presschg"]);
							break;
						case "seapressure":
							// Pressure at sea level (delivered in hpa)
							// Decoding is a bit obscure as 982 gets 998.2
							// whereas 113 becomes 1113 -> no real rule here
							if (strtolower($result[1]) != "no") {
								if ($result[1] > 500) {
									$press = 900 + round($result[1] / 100, 1);
								} else {
									$press = 1000 + $result[1];
								}
								$weatherData["remark"]["seapressure"] = $this->convertPressure($press, "hpa", "in");
							}
							unset($metarCode["seapressure"]);
							break;
						case "precip":
							// Precipitation in inches
							static $hours;
							if (!isset($weatherData["precipitation"])) {
								$weatherData["precipitation"] = array();
								$hours = array("P" => "1", "6" => "3/6", "7" => "24");
							}
							if (!is_numeric($result[2])) {
								$precip = "indeterminable";
							} elseif ($result[2] == "0000") {
								$precip = "traceable";
							} else {
								$precip = $result[2] / 100;
							}
							$weatherData["precipitation"][] = array(
								"amount" => $precip,
								"hours"  => $hours[$result[1]]
							);
							break;
						case "snowdepth":
							// Snow depth in inches
							$weatherData["remark"]["snowdepth"] = $result[1];
							unset($metarCode["snowdepth"]);
							break;
						case "snowequiv":
							// Same for equivalent in Water... (inches)
							$weatherData["remark"]["snowequiv"] = $result[1] / 10;
							unset($metarCode["snowequiv"]);
							break;
						case "cloudtypes":
							// Cloud types
							$weatherData["remark"]["cloudtypes"] = array(
								"low"    => $cloudtypes["low"][$result[1]],
								"middle" => $cloudtypes["middle"][$result[2]],
								"high"   => $cloudtypes["high"][$result[3]]
							);
							unset($metarCode["cloudtypes"]);
							break;
						case "sunduration":
							// Duration of sunshine (in minutes)
							$weatherData["remark"]["sunduration"] = "Durée du couché de soleil: ".$result[1];
							unset($metarCode["sunduration"]);
							break;
						case "1htempdew":
							// Temperatures in the last hour in C
							if ($result[1] == "1") {
								$result[2] *= -1;
							}
							$weatherData["remark"]["1htemp"] = $this->convertTemperature($result[2] / 10, "c", "f");

							if (sizeof($result) > 3) {
								// same for dewpoint
								if ($result[4] == "1") {
									$result[5] *= -1;
								}
								$weatherData["remark"]["1hdew"] = $this->convertTemperature($result[5] / 10, "c", "f");
							}
							unset($metarCode["1htempdew"]);
							break;
						case "6hmaxtemp":
							// Max temperature in the last 6 hours in C
							if ($result[1] == "1") {
								$result[2] *= -1;
							}
							$weatherData["remark"]["6hmaxtemp"] = $this->convertTemperature($result[2] / 10, "c", "f");
							unset($metarCode["6hmaxtemp"]);
							break;
						case "6hmintemp":
							// Min temperature in the last 6 hours in C
							if ($result[1] == "1") {
								$result[2] *= -1;
							}
							$weatherData["remark"]["6hmintemp"] = $this->convertTemperature($result[2] / 10, "c", "f");
							unset($metarCode["6hmintemp"]);
							break;
						case "24htemp":
							// Max/Min temperatures in the last 24 hours in C
							if ($result[1] == "1") {
								$result[2] *= -1;
							}
							$weatherData["remark"]["24hmaxtemp"] = $this->convertTemperature($result[2] / 10, "c", "f");

							if ($result[3] == "1") {
								$result[4] *= -1;
							}
							$weatherData["remark"]["24hmintemp"] = $this->convertTemperature($result[4] / 10, "c", "f");
							unset($metarCode["24htemp"]);
							break;
						case "3hpresstend":
							// Pressure tendency of the last 3 hours
							// no special processing, just passing the data
							$weatherData["remark"]["3hpresstend"] = array(
								"presscode" => $result[1],
								"presschng" => $this->convertPressure($result[2] / 10, "hpa", "in")
							);
							unset($metarCode["3hpresstend"]);
							break;
						case "nospeci":
							// No change during the last hour
							$weatherData["remark"]["nospeci"] = "Pas de changement dans la dernière heure";
							unset($metarCode["nospeci"]);
							break;
						case "sensors":
							// We may have multiple broken sensors, so do not unset
							if (!isset($weatherData["remark"]["sensors"])) {
								$weatherData["remark"]["sensors"] = array();
							}
							$weatherData["remark"]["sensors"][strtolower($result[0])] = $sensors[strtolower($result[0])];
							break;
						case "maintain":
							$weatherData["remark"]["maintain"] = "Maintenance";
							unset($metarCode["maintain"]);
							break;
						default:
							// Do nothing, just prevent further matching
							unset($metarCode[$key]);
							break;
					}
					if ($found && !SERVICES_WEATHER_DEBUG) {
						break;
					} elseif ($found && SERVICES_WEATHER_DEBUG) {
						echo $key."\n";
						break;
					}
				}
			}
			if (!$found) {
				if (SERVICES_WEATHER_DEBUG) {
					echo "n/a\n";
				}
				if (!isset($weatherData["noparse"])) {
					$weatherData["noparse"] = array();
				}
				$weatherData["noparse"][] = $metar[$i];
			}
		}

		if (isset($weatherData["noparse"])) {
			$weatherData["noparse"] = implode(" ",  $weatherData["noparse"]);
		}

		return $weatherData;
	}
}
?>
