{variable_pre}
<table>
	<tr>
		<th>Name</th>
		<th>Last Backup</th>
		<th>OS (Additional on hover)</th>
		<th>Description (Additional on hover)</th>
		<th>IP Address (Additional on hover)</th>
		<th>Third Party Software</th>
		<th>Booking</th>
	</tr>
	{machine}
	<tr class="r{class}">
		<td>{name}</td>
		<td>{backup}</td>
		<td><span class="dropt">{os}{configuration}</span></td>
		<td><span class="dropt">{description}{further}</span></td>
		<td><span class="dropt">{ipv4}{mac}</span></td>
		<td>{software}</td>
		<td>{booking}</td>
	</tr>
	{/machine}
</table>

<p><strong>Key</strong></p>
<table>
	<tr>
		<td>&nbsp;Delphi<span id="sprite" style="float: left;"><img id="chocolate" src="/assets/images/spritesheet.png"
																	width="0" height="1" title="Delphi"
																	alt="Delphi"/></span></td>
		<td>&nbsp;Desktop<span id="sprite" style="float: left;"><img id="darkblue" src="/assets/images/spritesheet.png"
																	 width="0" height="1" title="Desktop"
																	 alt="Desktop"/></span></td>
		<td>&nbsp;Laptop<span id="sprite" style="float: left;"><img id="darkcyan" src="/assets/images/spritesheet.png"
																	width="0" height="1" title="Laptop"
																	alt="Laptop"/></span></td>
		<td>&nbsp;Server<span id="sprite" style="float: left;"><img id="ggpgreen" src="/assets/images/spritesheet.png"
																	width="0" height="1" title="Server"
																	alt="Server"/></span></td>
		<td>&nbsp;Software<span id="sprite" style="float: left;"><img id="dimgrey" src="/assets/images/spritesheet.png"
																	  width="0" height="1" title="Software"
																	  alt="Software"/></span></td>
		<td>&nbsp;VM<span id="sprite" style="float: left;"><img id="darkmagenta" src="/assets/images/spritesheet.png" width="0"
																height="1" title="VM" alt="VM"/></span></td>
	</tr>
	<tr>
		<td>[W] Weekly</td>
		<td>[B] Bi-weekly</td>
		<td>[M] Monthly</td>
		<td>[Q] Quarterly</td>
	</tr>
	<tr>
		<td colspan="6"><span style="color: red;">machine_name</span> Machine is normally not powered. Ask Murray.</td>
	</tr>
	<tr>
		<td colspan="6"><em>Tip: hover over the images to see the type description.</em></td>
	</tr>
</table>
