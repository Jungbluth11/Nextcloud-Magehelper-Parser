<?php
declare(strict_types=1);

namespace OCA\Xmlviewer\Parser\Magehelper;

use OCA\Xmlviewer\Parser\XmlParserInterface;

class Parser implements XmlParserInterface {
	public function supportsRoot(string $rootNode): bool {
		return $rootNode === "magehelper";
	}

	public function getTemplatePath(): string {
		return __DIR__ . "/template.php";
	}

	public function parse(\SimpleXMLElement $xml, string $source): ?array {
		$artifacts = [];
		
		foreach ($xml->artifacts->artifact ?? [] as $artifact) {
			switch((string)$artifact->data["name"]){
				case "Magierstab":
					$artifacts[] = $this->addStaff($artifact);
					break;
				case "Kristallkugel":
					$artifacts[] = $this->addCrystallBall($artifact);
					break;
				default:
					$boundSpells = [];
			
					foreach ($artifact->boundSpells->boundSpell ?? [] as $boundSpell) {
						$boundSpells[] = (string)$boundSpell["name"];
					}
			
					$artifacts[] = [
						"name" => (string)$artifact->data["name"],
						"properties" => [],
						"boundSpells" => $boundSpells
					];
					break;
			}
		}

		$spells = [];
		
		foreach ($xml->spellStorage->spells->spell ?? [] as $spell) {
			$spells[] = (string)$spell["name"] . " (" . (string)$spell["zfp"] . " ZfP)";
		}

		$pet = [
			"species" => (string)$xml->pet->species,
			"properties" => [],
			"attributes" => [],
			"spells" => [],
		];
		
		$pet["flying"] = strtolower((string)$xml->pet->flying);
		$pet["mightyCompanion"] = strtolower((string)$xml->pet->mightyCompanion);
		
		$attributes = [];
		
		foreach ($xml->pet->attributes->attribute as $attribute) {
			$attributes[] = [
				"name" => $this->label((string)$attribute["name"]),
				"value" => (string)$attribute["current"],
			];
		}
		
		$attributes[] = ["name" => "RkP", "value" => (string)$xml->pet->rkp];
		$attributes[] = ["name" => "AE", "value" => (string)$xml->pet->ae];
		
		$pet["attributes"] = $attributes;
		
		foreach ($xml->pet->spells->spell as $spell) {
			$pet["spells"][] = (string)$spell;
		}

		$timers = [];
		
		foreach ($xml->timers->timer ?? [] as $timer) {
			$timers[] = [
				"text" => (string)$timer["text"],
				"duration" => $this->formatDuration((int)$timer["duration"]),
			];
		}

		return [
			"artifacts" => $artifacts,
			"spells" => $spells,
			"pet" => $pet,
			"timers" => $timers,
		];
	}
	
	private function addStaff($artifact): array {
		$merkmalsfoki = [];
		$afvTotal = 0;
		$afvUsed = 0;
		$material = (int) $artifact->data["material"];
		$length = (int) $artifact->data["length"];
		$pasp = (int) $artifact->data["pasp"];
		$isFlameSwordFive = strtolower((string)$artifact->data["FlameSwordFive"]) === "true";
		$hasApport = strtolower((string)$artifact->data["apport"]) === "true";
		$lostPoints = 0;
		$materialStrings = [
			"Normal",
			"Blutulme",
			"Bosparanie",
			"Steineiche",
			"Eisenbaum",
			"Mohagoni",
			"Zyklopenzeder",
			"Raschtulszeder",
			"Mammutbaum",
			"Walnussbaum",
			"Baum der Reisenden"
		];
		$lengthStrings = [
			"Magierstab als Stab", 
			"Magierstab m. Kristallkugel",
			"Magierstab (kurz)",
			"Magierstab (sehr kurz)"
		];
		
		$lengthMod = match ($length) {
			1 => 27,
			2 => 18,
			3 => 15,
			default => 24,
		};
		
		$materialMod = match ($material) {
			1, 7 => 1,
			2, 5, 8, 9, 10 => -1,
			default => 0,
		};

		$afv = $isFlameSwordFive ? 0 : $lengthMod + $materialMod + ($pasp / 3);
		if ($hasApport) {
			$afv = 0;
		}

		$afvTotal = (int) $afv;
		
		if (strtolower((string)$artifact->data["FlameSwordFour"]) === "true") {
			$lostPoints += 7;
		}
				
		$boundSpells = [];
		
		foreach($artifact->boundSpells->boundSpell ?? [] as $spell){
			$spellName= (string)$spell["name"];
			$points = (int)$spell["points"];
			
			switch ((string)$spell["name"]) {
				case "Merkmalsfokus":
					$merkmalsfoki[] = (string)$spell["characteristic"];
					$spellName = $spellName . " (" . (string)$spell["characteristic"] . ")";
					break;
				case "Hammer des Magus":
					$spellName = $spellName . " (3W6+" . (string)$artifact->data["hammerRkp"] . ")";
					break;
				case "Doppeltes Maß":
					$points = (string)$spell["points"];
					$spellName = $spellName . " (" . $points . " Volumenpunkte)";
					break;
				case "Schuppenhaut":
					$spellName = $spellName . " (" . (string)$artifact->data["reptileSkinVariant"] . ")";
					break;
			}

			switch ($material) {
				case 4:
					if ($spellName === "Modifikationsfokus") {
						$points--;
					} elseif ($spellName === "Flammenschwert") {
						$points++;
					}
					break;
				case 6:
					if ($spell->Characteristic === "Antimagie") {
						$points -= 2;
					}
					break;
				case 8:
					$merkmale = count(array_filter($merkmalsfoki, static fn($item) => in_array($item, ["Einfluss", "Herrschaft"], true)));
					if ($merkmale === 2) {
						$points--;
					}
					break;
			}

			$afvUsed += $points;
			$boundSpells[] = $spellName;
		}
		
		$properties = [
			["label" => "Länge", "value" => $lengthStrings[$length]],
			["label" => "Material", "value" => $materialStrings[$material]],
			["label" => "gebundene pAsp", "value" => $pasp],
			["label" => "Verbleibende Volumenpunkte", "value" => $hasApport ? 0 : $afvTotal - $afvUsed - $lostPoints]
		];
		
		return [
			"name" => (string)$artifact->data["name"],
			"properties" => $properties,
			"boundSpells" => $boundSpells
		];
	}
	
	private function addCrystallBall($artifact): array {
		$materialStrings = ["Glas", "Künstlicher Kristall", "Natürlicher Kristall"];
		$material = $materialStrings[(int)$artifact->data["material"]];
		$boundSpells = [];
		
		foreach($artifact->boundSpells->boundSpell ?? [] as $spell){
			$spellName = (string)$spell["name"];
			
			if ((string)$spell["variant"] ?? "") {
				$spellName = $spellName . " ( Variante: " . (string)$spell["variant"] . ")";
			}
			
			$boundSpells[] = $spellName;
		}
		
		return [
			"name" => (string)$artifact->data["name"],
			"properties" => [["label" => "Material", "value" => $material]],
			"boundSpells" => $boundSpells
		];
	}

	private function label(string $value): string {
		$labels = [
			"attack" => "Attacke",
			"attackflying" => "Attacke fliegend",
			"parry" => "Parade",
			"parryflying" => "Parade fliegend",
			"gs" => "GS",
			"gsflying" => "GS fliegend"
		];
		
		return array_key_exists($value, $labels) ? $labels[$value] : strtoupper($value);
	}
		
	private function formatDuration(int $durationKr): string {
		$durationMultiplier = [
			"KR" => 1,
			"SR" => 100,
			"Tage" => 28800,
		];

		if ($durationKr >= $durationMultiplier["Tage"]) {
			return intdiv($durationKr, $durationMultiplier["Tage"]) . " Tage";
		}

		elseif ($durationKr >= $durationMultiplier["SR"]) {
			return intdiv($durationKr, $durationMultiplier["SR"]) . " SR";
		}

		return intdiv($durationKr, $durationMultiplier["KR"]) . " KR";
	}
}

return new Parser();
