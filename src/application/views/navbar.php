<nav class="navbar navbar-default">
	<div class="container-fluid">
		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="https://www.ggpsystems.co.uk/">GGP</a>
		</div>

		<!-- Collect the nav links, forms, and other content for toggling -->
		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			<ul class="nav navbar-nav">
				<li{attendance_active}><a href="{base_url}attendance">Attendance{attendance_active_span}</a></li>
				<li{dllog_active}><a href="{base_url}download_log">Download Log{dllog_active_span}</a></li>
				<li{holidays_active}><a href="{base_url}holidays">Holidays{holidays_active_span}</a></li>
				<li{machines_active}><a href="{base_url}machines">Machines{machines_active_span}</a></li>
				<li{intranet_active}><a href="{base_url}intranet">Telephone Directory{intranet_active_span}</a></li>
				<li{wol_active}><a href="{base_url}wol">Wake on Lan{wol_active_span}</a></li>
			</ul>
		</div><!-- /.navbar-collapse -->
	</div><!-- /.container-fluid -->
</nav>