<?php
function mos_listing_admin_menu () {
    add_menu_page( 
        'Mos Property Listing', 
        'Property Listing', 
        'manage_options', 
        'mos-listing-options', 
        'mos_listing_page',
        plugins_url( 'images/logo-white-min.png', __FILE__ ),
        60 
    );
}
add_action("admin_menu", "mos_listing_admin_menu");

if ( !function_exists('mos_plugins_dashboard_page')) {
    function mos_plugins_dashboard_page () {
        ?>
        <div class="wrap">
            <h1><?php _e("Mos Plugins Dashboard") ?></h1>
            <?php settings_errors(); ?>
        </div>
        <?php
    }
}
function mos_listing_page () {
	?>
	        <div class="wrap">
            	<h1><?php _e("Mos Property Listing") ?></h1>
            	<?php settings_errors(); ?>
                <div class="well well-lg">
                    <div class="alert alert-warning">
                        <strong>IMPORTANT:</strong> Be sure to create a full database backup of your site before you begin the import process.
                    </div> 
                	<form id="form" class="form-inline" method="post" action="<?php echo admin_url('admin-ajax.php') ?>" enctype="multipart/form-data">
                		<div class="form-group">
                			<label class="sr-only" for="email">Email:</label>
                			<input type="file" id="file" placeholder="Upload XML Here" name="file">
                		</div>
                        <input type="hidden" name="action" id="action" value="mos_xml_upload"/>
                		<button type="submit" class="btn btn-default" id="mos_xml_upload_submit">Submit</button>
                	</form>
                    <button id="processBtn" type="button" class="btn btn-info" style="display: none;">
                        Processing <span class="fa fa-spinner fa-pulse"></span>
                    </button> 
                </div>
            </div>
	<?php 
}