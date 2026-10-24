<?php
$magehelper = $_["data"];
$col1 = [];
$col2 = [];

foreach($magehelper["pet"]["attributes"] as $attribute){
	switch($attribute["name"]){
		case "Attacke":
		case "Parade":
		case "GS":
			$col2[] = $attribute;
			break;
		case "Attacke fliegend":
		case "Parade fliegend":
		case "GS fliegend":
			if($magehelper["pet"]["flying"] === "true"){
				$col2[] = $attribute;
			}
			break;
		default:
			$col1[] = $attribute;
			break;
	}
}

$sections = [
	["Artefakte", $magehelper["artifacts"]],
	["Zauberspeicher", $magehelper["spells"]],
	["Vertrautentier", $magehelper["pet"]],
	["Timer", $magehelper["timers"]]
];
?>
	<nav class="xmlviewer-tabs" role="tablist">
		<?php foreach ($sections as $index => [$sectionTitle, $items]): ?>
		<button type="button" role="tab" id="xmlviewer-tab-<?php p((string)$index); ?>" aria-controls="xmlviewer-panel-<?php p((string)$index); ?>" aria-selected="<?php p($index === 0 ? 'true' : 'false'); ?>" tabindex="<?php p($index === 0 ? '0' : '-1'); ?>"><?php p($sectionTitle); ?></button>
		<?php endforeach; ?>
	</nav>
	<div class="xmlviewer-panels">
		<?php foreach ($sections as $index => [$sectionTitle, $items]): ?>
		<section class="xmlviewer-card" role="tabpanel" id="xmlviewer-panel-<?php p((string)$index); ?>" aria-labelledby="xmlviewer-tab-<?php p((string)$index); ?>"<?php if ($index !== 0): ?> hidden="hidden"<?php endif; ?>>
			<?php if ($sectionTitle === "Artefakte"): ?>
			<?php foreach ($items as $artifact): ?>
			<div class="xmlviewer-artifact">
			<center><h2><?php p($artifact["name"]); ?></h2></center>
			<div class="xmlviewer-flex-container xmlviewer-artifact-tables">
				<div>
					<table class="xmlviewer-table-100 xmlviewer-artifact-properties no-table">
						<tbody>
						<?php foreach ($artifact["properties"] as $property): ?>
							<tr>
								<td><b><?php p($property["label"]); ?>:</b></td>
								<td><?php p((string)$property["value"]); ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<div class="xmlviewer-table-wrap">
					<table class="xmlviewer-table-100 xmlviewer-table-single-column">
						<thead>
							<tr>
								<th><b>Gebundene Zauber</b></th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($artifact["boundSpells"] as $spell): ?>
							<tr>
								<td><?php p($spell); ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
			</div>
			<?php endforeach; ?>
			<?php elseif ($sectionTitle === "Zauberspeicher"): ?>
			<div class="xmlviewer-table-wrap xmlviewer-table-single-column">
				<table>
					<thead>
						<tr>
							<th>Zauber</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($items as $spell): ?>
						<tr>
							<td><?php p($spell); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php elseif ($sectionTitle === "Vertrautentier"): ?>
			<h2 class="xmlviewer-pet-species"><?php p($items["species"]); ?><?php if (strtolower($items["mightyCompanion"]) === "true"): ?> (Machtvoller Vertrauter)<?php endif; ?></h2>
			<div class="xmlviewer-flex-container">
				<div>
					<table class="xmlviewer-table-50 no-table">
						<tbody>
						<?php foreach ($col1 as $col): ?>
							<tr>
								<td><b><?php p($col["name"]); ?>:</b></td>
								<td><?php p($col["value"] === 0 ? "" : $col["value"]); ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<div>
					<table class="xmlviewer-table-50 no-table">
						<tbody>
						<?php foreach ($col2 as $col): ?>
							<tr>
								<td><b><?php p($col["name"]); ?>:</b></td>
								<td><?php p($col["value"]); ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<div class="xmlviewer-table-wrap xmlviewer-table-single-column xmlviewer-table-50">
				<table>
					<thead>
						<tr>
							<th><center><h3>Vertrautenzauber</h3></center></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($items["spells"] as $spell): ?>
						<tr>
							<td><?php p($spell); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			</div>				
			<?php elseif ($sectionTitle === "Timer"): ?>
			<div class="xmlviewer-table-wrap">
				<table class="xmlviewer-table-100">
					<thead>
						<tr>
							<th>Timer</th>
							<th>Dauer</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($items as $timer): ?>
						<tr>
							<td><?php p($timer["text"]); ?></td>
							<td><?php p($timer["duration"]); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php endif; ?>
			<?php if (count($items) === 0): ?>
			<br><p class="xmlviewer-empty">&nbsp; Keine Einträge</p>
			<?php endif; ?>
		</section>
		<?php endforeach; ?>
	</div>
