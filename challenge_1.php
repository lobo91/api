<?php
/**
 * Created By: Lou Navarro
 * Name: challenge_1.php
 * Date: 3/24/13
 *
 * Challenge 1: Write a script that builds three 512 MB Cloud Servers that
 * follow a similar naming convention (ie., web1, web2, web3) and returns the
 * IP and login credentials for each server. Use any image you want.
 * Worth 1 point
 */

//Change the parameters below to indicate number of servers to create,
//and what prefix their names will have.  Servers will be named
//name_prefix1,name_prefix2, etc
$name_prefix = 'web';
$number_servers = '3';

define('RAXSDK_COMPUTE_REGION', 'DFW');
define('RAXSDK_COMPUTE_NAME','cloudServersOpenStack');

require('lib/rackspace.inc');

//Authenticate and initialize
//read in credentials ini file
$credentials = parse_ini_file('.rackspace_cloud_credentials');

$endpoint = 'https://identity.api.rackspacecloud.com/v2.0/';
$cloud = new OpenCloud\Rackspace($endpoint, $credentials);
$compute = $cloud->Compute();

// first, find the image - hard coded to centos 6.3
$ilist = $compute->ImageList();
$found = FALSE;
$distro = "org.openstack__1__os_distro";
$version = "org.openstack__1__os_version";
while (!$found && $image = $ilist->Next()) {
   if ($image->metadata->$distro == 'org.centos' &&
        $image->metadata->$version >= 6.3) {
        $found = TRUE;
        $myimage = $image;
    }
}

// next, find the flavor - hard coded to 512M slice
$flist = $compute->FlavorList();
$found = FALSE;
while (!$found && $flavor = $flist->Next()) {
    if ($flavor->ram == 512) {
        $myflavor = $flavor;
        $found = TRUE;
    }
}

$i = 1;
// let's create the server
$creds = array();
while ($i <= $number_servers) {
    $server = $compute->Server();
    $server->name = $name_prefix . $i;
    print("Creating server...");
    $server->Create(array(
                         'image' => $myimage,
                         'flavor' => $myflavor));
    print("requested, now waiting...\n");
    print("ID=".$server->id."...\n");
// Uncomment the line below to have a display of server building 
    $server->WaitFor("ACTIVE", 600, 'dot');
    $creds[$i] = $server;
    $i++;
}

//output the server name, ID, and root password
$i = 1;
while ($i <= $number_servers) {
    $server = $creds[$i];
    echo "\n\nID: {$server->id}\nName: {$server->name}\nPassword: {$server->adminPass}\n";
    $i++;
}

function dot($server) {
    printf("%s %3d%%\n", $server->status, $server->progress);
}
