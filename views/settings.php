<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<?php
if(isset($_POST['save'])){
    update_option( 'wcall_host', $_POST['host']);
}
$host=get_option("wcall_host");
?>
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
                <input type="submit" name="save" value="Save">
            </div>
        </div>
        </form>
</div>