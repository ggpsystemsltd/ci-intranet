    <div class="address">
        <address>Author: [ {author_mailto} ]<br>
        Copyright &copy; <?php echo date('o'); ?> GGP Systems Ltd<br>
        Page rendered in <?php echo $this->benchmark->elapsed_time(); ?> seconds; <?php echo date('c'); ?><br>
        Remote IP: {remote_ip}</address>
    </div> <!-- address -->
    </div> <!-- container -->
    <!-- JavaScript at the end so the page loads faster -->
    <script src="//code.jquery.com/jquery-1.12.4.js" type="application/javascript"></script>
    <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js" type="application/javascript"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    {javascript}
</body>
</html>
