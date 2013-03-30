<?php
/**
 * Created By: Lou Navarro
 * Name: challenge_3.php
 * Date: 3/30/13
 *
 * Challenge 3: Write a script that accepts a directory as an argument as
 * well as a container name. The script should upload the contents
 * of the specified directory to the container (or create it if it
 * doesn't exist). The script should handle errors appropriately.
 * (Check for invalid paths, etc.)
 * Worth 2 Points
 */

define('DS','/'); //Directory separator: / for *nix, \ for windows

define('RAXSDK_OBJSTORE_REGION', 'DFW');


require('lib/rackspace.inc');

//Authenticate and initialize
//read in credentials ini file
$credentials = parse_ini_file('.rackspace_cloud_credentials');

$endpoint = 'https://identity.api.rackspacecloud.com/v2.0/';
$cloud = new OpenCloud\Rackspace($endpoint, $credentials);
$cloud->SetUploadProgressCallback('UploadProgress');
$swift = $cloud->ObjectStore();

//check that we were provided arguments. Otherwise
//print a brief usage message and die.
if (empty($argv[1]) || empty($argv[2]) || ($argv[1] == 'help')) {
    die('Usage: ' . $argv[0] . ' directory container_name' . "\n");
}
$dir = $argv[1];
$container_name = $argv[2];

//make sure the path provided in arg1 is valid
if (!is_dir($dir)) {
    die("Error: unable to find directory $dir\n");
}

//read the directory
$files = scandir($dir);

//interate through files.  Remove . and .. if present,
//and error out if a sub-directory is found, as subdirs are
//not allowed in cloud files
$clean_files = array();
foreach ($files as $file) {

    if ( ($file <> '.') && ($file <> '..') ) {
        if (is_dir($dir . DS . $file)) {
            die("Error: Directory $dir contains sub-directories. Please flatten directory before uploading to cloud files\n");
        } else {
            $clean_files[] = $file;
        }
    }
}

//make sure we have at least 1 file to upload
if (empty($clean_files)) {
    die("Error: No files found in directory $dir\n");
}

//check to see if the container name exists, if not, create it
try {
    // this will fail if the container doesn't exist
    $container = $swift->Container($container_name);
} catch (OpenCloud\ObjectStore\ContainerNotFoundError $e) {
    // here if the container was not found
    $container = $swift->Container();
    $container->Create($container_name);
}

//upload the files to the container

foreach($clean_files as $file) {
    $filename = $dir . DS . $file;
    $filetype = mime_content_type($filename);
    $object = $container->DataObject();
    $object->Create(array('name' => $file),$filename);
}

// progress callback function
function UploadProgress($len) {
    printf("[uploading %d bytes]\n", $len);
}