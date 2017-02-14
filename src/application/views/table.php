	<div class="{class}">
        {title}
		<div class="table-responsive">
			<table class="table table-striped">
				<thead>
					<tr>
						{head}
						<th {class}>{column}</th>
						{/head}
					</tr>
				</thead>
				<tbody>
					{row}
					<tr>
						{column}
						<td {class}>{value}</td>
						{/column}
					</tr>
					{/row}
				</tbody>
			</table>
		</div>
        <small>Last updated: {updated}</small>
	</div>
