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
define('RAXSDK_COMPUTE_REGION', 'DFW');
define('RAXSDK_OBJSTORE_REGION', 'DFW');
require('lib/rackspace.inc');
//require('lib/compute.inc');

//Authenticate
$endpoint = 'https://identity.api.rackspacecloud.com/v2.0/';
$credentials = array(
    'username' => 'lnavarro',
    'apiKey' => '4fd5ed0f68fc14f1f1ed5f46ef6218e6',
    'tenantName' => '759583'
);
$cloud = new OpenCloud\Rackspace($endpoint, $credentials);

$compute = $cloud->Compute('cloudServersOpenStack', 'DFW');


// first, find the image
$ilist = $compute->ImageList();
$found = FALSE;
$distro = "org.openstack__1__os_distro";
$version = "org.openstack__1__os_version";
while (!$found && $image = $ilist->Next()) {
    printf("Checking image %s...", $image->name);
    if ($image->metadata->$distro == 'org.centos' &&
        $image->metadata->$version >= 6.3) {
        $found = TRUE;
        $myimage = $image;
        print("FOUND IT");
    }
    print("\n");
}

// next, find the flavor
$flist = $compute->FlavorList();
$found = FALSE;
while (!$found && $flavor = $flist->Next()) {
    printf("Checking flavor %s...", $flavor->name);
    if ($flavor->ram == 512) {
        $myflavor = $flavor;
        $found = TRUE;
        print("FOUND IT");
    }
    print("\n");
}

// let's create the server
$server = $compute->Server();
$server->name = 'test2';
print("Creating server...");
$server->Create(array(
                     'image' => $myimage,
                     'flavor' => $myflavor));
print("requested, now waiting...\n");
print("ID=".$server->id."...\n");
$server->WaitFor("ACTIVE", 600, 'dot');
print("done\n");
exit(0);

function dot($server) {
    printf("%s %3d%%\n", $server->status, $server->progress);
}

