<?php
if (isset($_POST['save'])) {
  update_option( 'wcall_host', $_POST['host']);
}
$host = get_option("wcall_host");
?>
<!-- @FIX Layout using Wordpress admin classes. -->
<div class="container-fluid" style="margin-top:40px;">
  <div class="row">
  <h2>Settings</h2>
  <div>

  <form method="post" action="">
  <div class="row">
    <div class="col-md-12">
      Host (Remote REST Host): <input type="text" value="<?php echo @$host; ?>" name="host" style="width:80%;" class="form-control" required>
    </div>

    <div class="col-md-12" style="margin-top:15px;">
        <input type="submit" class="button button-primary" name="save" value="Save">
    </div>
  </div>
  </form>
</div>
