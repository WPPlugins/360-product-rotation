<?php

include_once ( "inc.wordpress.php" );


if(isset($_GET['action']))
{
    switch($_GET['action']){
        case 'y360list':
            send_yofla360_list();
            break;
        case 'trash':
            move_to_trash($_GET['path']);
            break;
    }
}


/**
 * Returns JSON encoded list of folders in the uploads/yofla360 folder
 *
 * @return string
 */
function send_yofla360_list()
{
    $dirs = YoFLA360()->Utils()->get_yofla360_directories_list();
    echo(json_encode($dirs));
}


/**
 * Moves specified 360 view folder in yofla360 folder to yofla360/trash
 *
 * @param $path
 * @return string
 */
function move_to_trash($path){

    if(!$path) return '';

    //sanitize parameter (GET)
    $path =  basename($path);

    $y360 = YoFLA360()->Utils()->get_products_path();

    $source = $y360.$path;
    $trash  = $y360.YOFLA_360_TRASH_FOLDER_NAME;
    if(!file_exists($trash)) mkdir($trash);

    $target = $trash.'/'.$path;
    $target = YoFLA360()->Utils()->get_safe_destination($target);

    if(!file_exists($source)) return '';

    rename($source,$target);

    return 'ok';
}
