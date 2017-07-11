<?php


/**
 * Class YoFLA360Addmedia
 *
 * Constructs the Media Upload page for uploading assets for the 360 views
 *
 */
class YoFLA360Addmedia {

    protected $plugin_url;
    protected $products_path;

    /**
     * Start up
     */
    public function __construct()
    {

        add_action( 'admin_menu', array( $this, 'add_plugin_media_page' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'load_custom_wp_admin_style' ) );
        
        //set path to plugins
        $this->plugin_url = plugins_url( '360-product-rotation/', YOFLA_360_PLUGIN_PATH);

        //set path to yofla360 folder within uploads
        $wp_uploads = wp_upload_dir();
        $this->products_path = $wp_uploads['basedir'].'/'.YOFLA_360_PRODUCTS_FOLDER.'/';
    }


    /**
     * Add sytles and scripts for plugin media page
     */
    function load_custom_wp_admin_style($hook)
    {
        if( $hook == 'media_page_yofla-360-media')
        {
            wp_register_style( 'yofla_360_add_media_css', $this->plugin_url.'styles/add-media.css', false, '1.0.0' );
            wp_enqueue_style( 'yofla_360_add_media_css' );

            wp_register_script( 'yofla_360_add_media_js_form', $this->plugin_url.'/js/vendor/jquery.form.js', false, '1.0.0' );
            wp_enqueue_script( 'yofla_360_add_media_js_form' );

            wp_register_script( 'yofla_360_add_media_js_add', $this->plugin_url.'/js/add-media.js', false, '1.0.0' );
            wp_enqueue_script( 'yofla_360_add_media_js_add' );

        }
    }

    /**
     * Add options page
     */
    public function add_plugin_media_page()
    {
        // This page will be under "Settings"
        add_media_page(
            '360&deg; Views',
            '360&deg; Views',
            'edit_posts',
            'yofla-360-media', //menu slug
            array( $this, 'create_plugin_media_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_plugin_media_page()
    {

        $y360_options = get_option( 'yofla_360_options' ); //read options stored in WP options database

        $is_using_license = !empty($y360_options['license_id']);
        $is_using_rotatetooljs_url = !empty($y360_options['rotatetooljs_url']) && filter_var($y360_options['rotatetooljs_url'], FILTER_VALIDATE_URL);
        $is_using_localengine = !empty($y360_options['local_engine']);

        if ($is_using_license || $is_using_rotatetooljs_url || $is_using_localengine)
        {
            $license_info = '';
        }
        else
        {
            $license_info = '<h2 style="color: red">License info</h2>';
            $license_info .= '<p style="font-size: 16px">You are using the <strong>free version</strong> of the player. You can set up your license';
            $license_info .= ' in the <a href="'.admin_url( 'options-general.php?page=yofla-360-admin').'">plugin settings</a>. </p>';
        }

        ?>
        <script type="text/javascript">
            var maxAllowedUploadSize = <?php echo $this->_file_upload_max_size();?>;
            var yofla360PluginUrl   = "<?php echo $this->plugin_url;?>";
        </script>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>360&deg; Views :: Manage & Embed</h2>

            <div id="yofla360_plugin_media_wrapper">
                <div id="yofla360_plugin_media_upload">

                    <?php echo $license_info ?>

                    <h3>Upload new 360&deg; view:</h3>
                    <ol style="font-size: 16px">
                        <li>
                           Use FTP to upload the <i>folder with images</i> or the <i>folder exported by the 3DRT Setup Utility</i> to the <strong>wp-content/uploads/yofla360/</strong> directory on your server.
                        </li>
                        <li>
                            or try this <a href="#" onclick="jQuery('#yofla-upload').toggle()">build-in uploader.</a>
                            Experimental, might not work reliable with your server.
                            <div id="yofla-upload" style="display: none">
                                <br>
								Upload a .zip archive of the directory exported by the <a href="https://www.yofla.com/3d-rotate/support/manuals/first-steps/#3drtsetuputility" target="_blank">3DRT Setup Utility</a>, or just images in a .zip archive.
								Max. upload size is <?php echo $this->_formatBytes( $this->_file_upload_max_size(),2 );?>
								(based on your PHP settings).
								<br/>
								If uploading or unzipping fails,  <a href="https://www.yofla.com/3d-rotate/contact/">please let me know</a>. You can always use FTP to upload 360 views to uploads/yofla360 folder.

								<div id="upload-wrapper">
									<form action="<?php echo plugins_url( 'plugin-media-upload.php', __FILE__);?>" method="post" enctype="multipart/form-data" id="MyUploadForm">
										<input name="FileInput" id="FileInput" type="file" accept=".zip" />
										<input type="submit"  id="submit-btn" value="Upload" />
										<img src="<?php echo $this->plugin_url;?>/images/ajax-loader.gif" id="loading-img" style="display:none;" alt="Please Wait"/>
									</form>
									<div id="progressbox" >
										<div id="progressbar"></div >
										<div id="statustxt">0%</div>
									</div>
									<div id="output"></div>
								</div>
                            </div>
                        </li>

						<li>
							You can download this sample  <a href="https://s3.amazonaws.com/360.yofla.com/downloads/360-View-Demo.zip">360-View-Demo.zip</a>
							zip file and upload it using the built-in .zip uploader or you can upload it extracted using FTP the the uploads/yofla360 directory.
						</li>
                    </ol>

                </div>

                <div id="yofla360_plugin_media_list">
                    <h3>Available 360&deg; views:</h3>
                    <ul class="products_list">
                    </ul>
                </div>
            </div>


        </div>
    <?php
    }



    // Returns a file size limit in bytes based on the PHP upload_max_filesize
    // and post_max_size
    private function _file_upload_max_size()
    {
        static $max_size = -1;

        if ($max_size < 0) {
            // Start with post_max_size.
            $max_size = $this->_parse_size(ini_get('post_max_size'));

            // If upload_max_size is less, then reduce. Except if upload_max_size is
            // zero, which indicates no limit.
            $upload_max = $this->_parse_size(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
            }
        }
        return $max_size;
    }

    private function _parse_size($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }

    private function _formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
