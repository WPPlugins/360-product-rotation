<?php
/**
 * PHP Upload handler file for new 360 media upload
 */

include_once( "inc.wordpress.php" );

if ( ! current_user_can( 'upload_files' ) ) {
	die( 'User is not logged in or has not the rights to upload files' );
}

/**
 * Handle the upload
 */
$status_errors = process_upload();

/**
 * Evaluate status of upload and provide ajax Response
 *
 */
if ( sizeof( $status_errors ) > 0 ) {
	die( implode( ' ', $status_errors ) );
} else {
	die( '' );
}


/**
 * Checks validity of upload, extracts zip archive and moves it to correct destionation
 *
 * @return array
 */
function process_upload() {
	//errors
	$errors = array();

	/**
	 * Uplaod received
	 */
	if ( isset( $_FILES["FileInput"] ) && $_FILES["FileInput"]["error"] == UPLOAD_ERR_OK ) {
		$upload_dir      = wp_upload_dir();
		$products_folder = trailingslashit( $upload_dir['basedir'] ) . 'yofla360/';

		//check if this is an ajax request
		if ( ! isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) {
			die();
		}

		//allowed file type Server side check
		switch ( strtolower( $_FILES['FileInput']['type'] ) ) {
			//allowed file types
			case 'application/x-zip-compressed':
			case 'application/zip':
				/*
				case 'image/png':
				case 'image/gif':
				case 'image/jpeg':
				case 'image/pjpeg':
				case 'text/plain':
				case 'text/html': //html file
				case 'application/pdf':
				case 'video/mp4':
				*/
				break;
			default:
				die( 'Unsupported File!' ); //output error
		}

		$file_name_all     = strtolower( $_FILES['FileInput']['name'] );
		$file_name         = basename( $file_name_all, '.zip' ); //filename without extension
		$File_Ext          = substr( $file_name, strrpos( $file_name, '.' ) ); //get file extention
		$Random_Number     = rand( 0, 9999999999 ); //Random number to be added to name.
		$zip_filename      = $Random_Number . $File_Ext; //new file name
		$temp_directory    = trailingslashit( $products_folder . 'temp' . $Random_Number );
		$uploaded_zip_path = $products_folder . $zip_filename;

		if ( @move_uploaded_file( $_FILES['FileInput']['tmp_name'], $uploaded_zip_path ) ) {
			// do other stuff
			$zip = new ZipArchive;
			$res = $zip->open( $uploaded_zip_path );

			if ( $res === true ) {
				$zip->extractTo( $temp_directory );
				$zip->close();

				$status = copy_source_to_destination( $temp_directory, $products_folder, $file_name, $errors );

				if ( ! $status ) {
					$errors[] = 'Failed to move/rename the unzipped folder.';
					if ( ! delete_directory( $temp_directory ) ) {
						$errors[] = 'Failed to delete the temp. directory: ' . htmlspecialchars( $temp_directory );
					}
				}
			} else {
				$errors[] = 'Failed to unzip uploaded file.';
			}

			unlink( $uploaded_zip_path );
		} else {
			$errors[] = 'Error moving uploaded File!';
		}
	} else {
		$errors[] = 'Something wrong with upload! Is "upload_max_filesize" set correctly?';
	}

	return $errors;
}


/**
 * Checks the structure of the extracted zip archive to determine
 * from where to move the extracted zip file to where.
 *
 * Uploaded archive can have a structure like this:
 *
 * 1) just images with no sub folders -> make up destination folder, move
 * 2) a folder with just images -> move folder to uploads/yofla360
 * 3) just 3DRT output files -> make up destination folder,move
 * 4) a folder with 3DRT output files -> move folder to uploads/yofla360
 *
 * @param $path Is the (temp) path to the extracted zip directory
 * @param $products_folder
 * @param $file_name String the file name of the uploaded .zip archive, without extension
 * @param $errors
 *
 * @return bool
 */
function copy_source_to_destination( $path, $products_folder, $file_name, &$errors ) {

	// map directories and files in extracted archive
	$directories       = array();
	$files             = array();
	$files_backslashes = array(); //uncorrect created zip file

	if ( $handle = opendir( $path ) ) {
		while ( false !== ( $file = readdir( $handle ) ) ) {
			if ( $file != "." && $file != ".." ) {
				$file_path = $path . '/' . $file;
				if ( is_dir( $file_path ) ) {
					$directories[] = $file;
				} else {
					$files[] = $file;

					//detect wrong zip file structure
					if ( strpos( $file, '\\' ) > 0 ) {
						$files_backslashes[] = $file;
					}

				}
			}
		}
		closedir( $handle );
	} else {
		$errors[] = "Failed to open the directory " . htmlspecialchars( $path ) . " for reading!";

		return false;
	}


	//detect unallowed extensions
	$files_excluded = getExcludedFiles( $path, $file_name );
	if ( sizeof( $files_excluded ) > 0 ) {
		$errors[] = "Uploading of these files is not supported: ";
		$errors[] = implode( ',', $files_excluded );

		return false;
	}

	//zip contains just images and no subfolders
	if ( sizeof( $directories ) == 0 && sizeof( $files ) > 0 ) {

		//wrong zip file structure - folder names are encoded in file names, like: 'szow1\index.html'
		if ( sizeof( $files_backslashes ) == sizeof( $files ) ) {
			$result = process_incorrect_zip( $path, $files_backslashes, $products_folder );
			delete_directory( $path ); //in other cases the dir is moved...
			return $result;
		} // zip contains just images and no sub folders
		else {
			//make up new folder name
			$dir_name = makeup_folder_name( $file_name );

			$source = $path;

			$destination = YoFLA360()->Utils()->get_safe_destination( $products_folder . $dir_name );

			if ( mkdir( $destination ) ) {

				$destination_final = trailingslashit( $destination ) . 'images';

				//rename temp dir
				return rename_directory( $source, $destination_final, $errors );
			} else {
				$errors[] = "Failed to create the directory: " . htmlspecialchars( $destination ) . "";

				return false;
			}
		}
	} //zip contains folders and images (3drt output with no containing folder)
	elseif ( sizeof( $directories ) > 0 && sizeof( $files ) > 0 ) {
		//make up new folder name
		$dir_name = makeup_folder_name( $file_name );

		$source      = $path;
		$destination = YoFLA360()->Utils()->get_safe_destination( $products_folder . $dir_name );

		//rename temp dir
		return rename_directory( $source, $destination, $errors );
	} //archive contains one directory (images or 3drt output)
	elseif ( sizeof( $directories == 1 ) ) {
		//get the name of the directory in the extracted zip archive
		$dir_name = $directories[0];

		//determine folder name
		$source      = $path . $dir_name;
		$destination = YoFLA360()->Utils()->get_safe_destination( $products_folder . $dir_name );

		//move dir
		$status = rename_directory( $source, $destination, $errors );

		//remove now empty temp dir
		if ( $status ) {
			if ( ! rmdir( $path ) ) {
				$errors[] = 'Failed to delete the (empty) temp. directory: ' . htmlspecialchars( $path );
			}
		}

		return $status;
	} else {
		if ( ! delete_directory( $path ) ) {
			$errors[] = 'Failed to delete the temp. directory: ' . htmlspecialchars( $path );
		}
		$errors[] = 'Could not identify the directory structure of the uploaded .zip file!';

		return false;
	}
}

/**
 * Renames a directory, logs error.
 *
 * @param $source
 * @param $destination
 * @param $errors
 *
 * @return bool
 */
function rename_directory( $source, $destination, &$errors ) {

	if ( rename( $source, $destination ) ) {
		return true;
	} else {
		$errors[] = "Failed to rename temp directory (" . htmlspecialchars( $source ) . ") to destination directory (" . htmlspecialchars( $destination ) . ")";

		return false;
	}
}

/**
 * Delete a dir recursively
 *
 * @param $dir
 *
 * @return bool
 */
function delete_directory( $dir ) {
	$it    = new RecursiveDirectoryIterator( $dir, RecursiveDirectoryIterator::SKIP_DOTS );
	$files = new RecursiveIteratorIterator( $it, RecursiveIteratorIterator::CHILD_FIRST );
	foreach ( $files as $file ) {
		if ( $file->isDir() ) {
			rmdir( $file->getRealPath() );
		} else {
			unlink( $file->getRealPath() );
		}
	}

	return rmdir( $dir );
}

/**
 * Returns a name of the folder where the 360 rotation will be placed.
 *
 * If zip archive name is provided, use that, otherwise return random name
 *
 * @param null $file_name
 *
 * @return bool
 */
function makeup_folder_name( $file_name = null ) {
	if ( $file_name ) {
		$file_name = preg_replace( '/\s+/', '_', $file_name );
	}

	return ( $file_name ) ? $file_name : uniqid( '360view_' );
}


function process_incorrect_zip( $temp_path, $files_list, $products_folder ) {

	//check if folder already exists


	// loop files
	foreach ( $files_list as $kf => $file ) {

		// get dirs along path
		$file_dirs = explode( '\\', $file );

		//get file name
		$file_name = array_pop( $file_dirs );

		//store path with directories
		$file_path = $products_folder;

		//append path with fil edirectories
		for ( $i = 0; $i < sizeof( $file_dirs ); $i ++ ) {
			$dir       = $file_dirs[ $i ];
			$file_path = $file_path . $dir . '/';
		}

		//create path if it does not exist
		if ( false == file_exists( $file_path ) ) {
			if ( false == mkdir( $file_path, 0777, true ) ) {
				$errors[] = "Failed to create directory :" . $file_path;

				return false;
			}
		}

		//move file
		$source_path      = $temp_path . $file;
		$destination_path = $file_path . $file_name;
		if ( false == rename( $source_path, $destination_path ) ) {
			$errors[] = "Failed to move file to :" . $destination_path;

			return false;
		}
	}

	return true;
}


/**
 * Recursively walks directory and returns list of all files
 *
 * @param $dir
 * @param array $results
 *
 * @return array
 */
function getDirContents( $dir, &$results = array() ) {
	$files = scandir( $dir );

	foreach ( $files as $key => $value ) {
		$path = realpath( $dir . DIRECTORY_SEPARATOR . $value );
		if ( ! is_dir( $path ) ) {
			$results[] = $path;
		} else if ( $value != "." && $value != ".." ) {
			getDirContents( $path, $results );
			$results[] = $path;
		}
	}

	return $results;
}

/**
 * Returns list of files, that must not be in archive
 *
 * @param $path
 * @file_name Is the name of the .zip file, must me same as the zipped folder
 *
 * @return array
 */
function getExcludedFiles( $path, $file_name) {
	$files              = getDirContents( $path );
	$extensions_allowed = array( 'jpg', 'jpeg', 'js', 'html', 'css', 'png', 'txt',  'ini' );
	$names_allowed = array( '.DS_Store', 'assets','images','imageslarge','hotspoticons','hotspotimages');
	if($file_name) $names_allowed[] = $file_name;
	$files_excluded     = array();

	foreach ( $files as $k => $file ) {

		if(is_dir($file)){
			//no action if dir
		}
		else{
			$filename  = basename( $file );
			$extension = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );

			if ( in_array($filename, $names_allowed)) {
				//all ok on allowed files
			}
			else {
				if ( ! in_array( $extension, $extensions_allowed ) ) {
					$files_excluded[] = basename( $file );
				}
			}
		}

	}

	return $files_excluded;
}
