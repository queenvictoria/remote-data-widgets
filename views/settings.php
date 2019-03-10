<?php
if (isset($_POST['save'])) {
  update_option( 'remote_data_widgets_remote_host', $_POST['remote_data_widgets_remote_host']);
}
$host = get_option("remote_data_widgets_remote_host");
?>
<!-- @FIX Layout using Wordpress admin classes. -->
<div class="container-fluid" style="margin-top:40px;">
  <div class="row">
  <h2>Remote data widgets settings</h2>
  <div>

  <form method="post" action="">
  <div class="row">
    <div class="col-md-12">
      <label for="remote_data_widgets_remote_host">Remote host</label>
      <input type="text" value="<?php echo @$host; ?>" name="remote_data_widgets_remote_host" class="regular-text" required>
    </div>

    <div class="col-md-12" style="margin-top:15px;">
        <input type="submit" class="button button-primary" name="save" value="Save settings">
    </div>
  </div>
  </form>
</div>
