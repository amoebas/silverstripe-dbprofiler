<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"> 
<html>
	<head> 
		<title>Query profiler stats</title>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
	</head>
	<body>
		<div id="showonclickarea"></div>
		
		<table id="profile-request-data">
			<caption>Total for request</caption>
			<tbody>
				<tr>
					<th scope="row">Total time</th>
					<td>$TotalTime ms</td>
				</tr>
				<tr>
					<th scope="row">Peak Memory usage</th>
					<td>$MemoryUsage MB</td>
				</tr>
				<tr>
					<th scope="row">Total Size</th>
					<td>$TotalSize kB</td>
				</tr><tr>
					<th scope="row">Total number of queries</th>
					<td>$TotalQueries</td>
				</tr>
			</tbody>
		</table>

		<table id="profile-query-data" class="tablesorter">
			<caption>Queries</caption>
			<thead>
				<tr>
					<th id="head-requests" class="header">Queries</th>
					<th id="head-time" class="header">Time</th>
					<th id="head-query-size" class="header">Query size</th>
					<th id="head-query" class="header">Query</th>
					<th id="head-backtrace" class="header">Backtrace</th>
				</tr>
			</thead>
			<tbody>
				<% control Queries %>
				<tr>
					<td class="center">$Requests</td>
					<td class="right">$Time</td>
					<td class="right">$QuerySize</td>
					<td class="center">
						<a class="doit" href="ProfilerLogViewerController/Query/$ID">View</a>
						<a class="doit" href="ProfilerLogViewerController/Describe/$ID">Explain</a>
					</td>
					<td class="center">
						<a class="doit" href="ProfilerLogViewerController/Backtrace/$ID">Show</a>
					</td>
				</tr>
				<% end_control %>
			</tbody>
		</table>
	</body>
</html>