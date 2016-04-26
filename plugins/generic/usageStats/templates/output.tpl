{**
 * plugins/generic/usageStats/templates/output.tpl
 *
 * Copyright (c) 2013-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Statistics display
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the graph handler.
		$('#usageStatsGraphContainer').pkpHandler('$.pkp.plugins.generic.usageStats.UsageStatsGraphHandler', 
			{ldelim}
				labels: {$labels},
				data: {$statistics},
				datasetMaxCount: {$datasetMaxCount},
				chartType: "{$chartType}"
			{rdelim}		
		);
	{rdelim});
</script>

<div id="usageStatsGraphContainer">
	<h3> Statistics </h3>
	{fbvElement id="statsSum" type=checkbox value="1" checked=true label="plugins.generic.usageStats.statsSum"}
	{if $statsYears}
		{fbvElement id="statsYear" type=select from=$statsYears selected=$year translate=false}
	{/if}
	<canvas id="usageStatsChart" width="400" height="200"></canvas>
	<div id="usageStatsWarning">{translate key="plugins.generic.usageStats.noStats"}</div>
</div>
