<?php
/*
Plugin Name: 360&deg; Product Rotation
Plugin URI: https://www.yofla.com/3d-rotate/wordpress-plugin-360-product-rotation/
Description: 360 Product Rotation :: creates a 360 product view from a series of photos
Author: YoFLA
Author URI: https://www.yofla.com/
Developer: Matus Laco
Developer URI: https://www.yofla.com/
Version: 1.3.2
Last Modified: 05/2017
License: GPLv2
*/

if (!defined("ABSPATH")) exit;

define('YOFLA_360_PLUGIN_MAIN', __FILE__);
define('YOFLA_360_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('YOFLA_360_PLUGIN_URL', plugin_dir_url(__FILE__));

include_once ( YOFLA_360_PLUGIN_PATH.'includes/inc.constants.php' );


class YoFLA360
{

    private $_errors;

    /**
     * @var YoFLA360 The single instance of the class
     */
    protected static $_instance = null;

    /**
     * Main YoFLA360 Instance
     *
     * Ensures only one instance of YoFLA360 is loaded or can be loaded.
     *
     * @static
     * @see YoFLA360()
     * @return YoFLA360 - Main instance
     */
    public static function instance()
    {
        if ( is_null( self::$_instance ) )
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {

        $this->_errors = array();

        if (in_array("woocommerce/woocommerce.php", apply_filters("active_plugins", get_option("active_plugins"))))
        {
            include_once YOFLA_360_PLUGIN_PATH.'includes/class-yofla360-woocommerce.php';
            new YoFLA360Woocommerce();
        }

        //includes
        include_once YOFLA_360_PLUGIN_PATH.'includes/class-yofla360-utils.php';

        //in wordpress admin area
        if (is_admin())
        {
            include_once YOFLA_360_PLUGIN_PATH.'includes/class-yofla360-activation.php';
            include_once YOFLA_360_PLUGIN_PATH.'includes/class-yofla360-settings.php';
            include_once YOFLA_360_PLUGIN_PATH.'includes/class-yofla360-addmedia.php';

            new YoFLA360Activation();
            new YoFLA360Settings();
            new YoFLA360Addmedia();

            $this->checkUpgrading();

        }
        //in wordpress frontend
        else
        {
            include_once YOFLA_360_PLUGIN_PATH.'includes/class-yofla360-shortcodes.php';
            include_once YOFLA_360_PLUGIN_PATH.'includes/class-yofla360-frontend.php';
            include_once YOFLA_360_PLUGIN_PATH.'includes/class-yofla360-viewdata.php';

            //init shortcodes
            new YoFLA360Shortcodes();
        }

        //in wordpress admin or on wordpress frontend

    }

    private function checkUpgrading()
    {
        //check if upgrading...
        if (get_option(YOFLA_360_VERSION_KEY) != YOFLA_360_VERSION_NUM) {
            // Execute your upgrade logic here
            // no action
            // Update version
            update_option(YOFLA_360_VERSION_KEY, YOFLA_360_VERSION_NUM);
        }
    }

    /**
     * Adds an error
     *
     * @param $msg
     */
    public function add_error($msg)
    {
        $this->_errors[] = $msg;
    }


    /**
     * Return errors, if any
     */
    public function get_errors()
    {
       if( sizeof($this->_errors) > 0)
       {
          return YoFLA360()->Frontend()->format_error( implode('<br>'.PHP_EOL, $this->_errors) );
       }
       else
       {
           return false;
       }
    }


    /**
     * Handle for Utils functions
     *
     * @return YoFLA360Frontend
     */
    public function Utils()
    {
        return YoFLA360Utils::instance();
    }

    /**
     * Handle for Frontend functions
     *
     * @return YoFLA360Frontend
     */
    public function Frontend()
    {
        return YoFLA360Frontend::instance();
    }
}

/**
 * Returns main instance of YoFLA360 class
 *
 * @return YoFLA360
 */
function YoFLA360(){
    return YoFLA360::instance();
}

//initialize
YoFLA360();


