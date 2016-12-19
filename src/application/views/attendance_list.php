<div style="float: left; padding-right: 10px; padding-bottom: 10px">
	<table>
		<tr>
			<th>Name</th>
			<th>Attendance</th>
		</tr>
		{staff}
		<tr class="r{class}">
			<td>{name}</td>
			<td class="{attclass}">{attendance}</td>
		</tr>
		{/staff}
	</table>
</div>
<div style="padding-bottom: 10px">
	<strong>Current/Upcoming Vacations</strong>
	<table>
		<tr>
			<th>Name</th>
			<th>Dates</th>
		</tr>
		{holidays}
		<tr class="r{class}">
			<td>{name}</td>
			<td>{dates}</td>
		</tr>
		{/holidays}
	</table>
</div>
<p style="clear: both; font-style: italic; font-size: x-small;">Last updated: {updated}</p>
