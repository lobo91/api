<?php
/**
 * Created By: Lou Navarro
 * Name: challenge_2.php
 * Date: 3/29/13
 *
 * Challenge 2: Write a script that clones a server (takes an image
 * and deploys the image as a new server).
 * Worth 2 Points
 */

$server_name = 'api'; //server name to be cloned
$cloned_server_name = 'api_clone'; //name of the cloned server
$flavor_id = 2; //Flavor ID for the cloned  server

$image_name = $server_name . '_image_' . time();


define('RAXSDK_COMPUTE_REGION', 'DFW');
define('RAXSDK_COMPUTE_NAME','cloudServersOpenStack');

require('lib/rackspace.inc');

//Authenticate and initialize
//read in credentials ini file
$credentials = parse_ini_file('.rackspace_cloud_credentials');

$endpoint = 'https://identity.api.rackspacecloud.com/v2.0/';
$cloud = new OpenCloud\Rackspace($endpoint, $credentials);
$compute = $cloud->Compute();

//get server object associated with server_name
$server = $compute->ServerList(TRUE, array('name'=>$server_name))->First();

if (empty($server)) {
    die("Error: Server '$server_name' was not found in this account!'\n\n");
}

//create the image
echo "Image $image_name being created\n";
$server->CreateImage($image_name);
$image =  $compute->ImageList(TRUE, array('name'=>$image_name))->First();

//wait for the image creation to complete
while ($image->status <> 'ACTIVE') {
    echo ".";
    sleep(10);
    $image =  $compute->ImageList(TRUE, array('name'=>$image_name))->First();
}

echo "\nStatus: " . $image->status . "\n";
echo "Progress: " . $image->progress . "\n";

//create a new server from this image
$clone = $compute->Server();

$clone->Create(array( 'name' => $cloned_server_name,
                     'image' => $image,
                     'flavor' => $compute->flavor($flavor_id)));
echo "Clone server being created, please wait\n";
echo "ID=".$clone->id."...\n";
$clone->WaitFor("ACTIVE", 600, 'dot');
echo "Clone server created\n";
echo "\n\nID: {$clone->id}\nName: {$clone->name}\nPassword: {$clone->adminPass}\n";


function dot($server) {
    printf("%s %3d%%\n", $server->status, $server->progress);
}
