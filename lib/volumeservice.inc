<?php
/**
 * The OpenStack Cinder (Volume) service
 *
 * @copyright 2012 Rackspace Hosting, Inc.
 * See COPYING for licensing information
 *
 * @package phpOpenCloud
 * @version 1.0
 * @author Glen Campbell <glen.campbell@rackspace.com>
 */

namespace OpenCloud;

require_once('nova.inc');
require_once('volume.inc');
require_once('volumetype.inc');
require_once('snapshot.inc');

class VolumeService extends Nova {

	/**
	 * creates the VolumeService object
	 */
	public function __construct(OpenStack $conn, $name, $region, $urltype) {
		parent::__construct($conn, 'volume', $name, $region, $urltype);
	}

	/**
	 * Returns a Volume object
	 *
	 * @api
	 * @param string $id the Volume ID
	 * @return VolumeService\Volume
	 */
	public function Volume($id=NULL) {
		return new VolumeService\Volume($this, $id);
	}

	/**
	 * Returns a Collection of Volume objects
	 *
	 * @api
	 * @param boolean $details if TRUE, return all details
	 * @param array $filters array of filter key/value pairs
	 * @return Collection
	 */
	public function VolumeList($details=TRUE, $filter=array()) {
		$url = $this->Url('volumes') .
				($details ? '/detail' : '');
		return $this->Collection(
			$url,
			'Volume',
			'volumes');
	}

	/**
	 * Returns a VolumeType object
	 *
	 * @api
	 * @param string $id the VolumeType ID
	 * @return VolumeService\Volume
	 */
	public function VolumeType($id=NULL) {
		return new VolumeService\VolumeType($this, $id);
	}

	/**
	 * Returns a Collection of VolumeType objects
	 *
	 * @api
	 * @param array $filters array of filter key/value pairs
	 * @return Collection
	 */
	public function VolumeTypeList($filter=array()) {
		return $this->Collection(
			$this->Url('types'),
			'VolumeType',
			'types');
	}

	/**
	 * returns a Snapshot object associated with this volume
	 *
	 * @return Snapshot
	 */
	public function Snapshot($id=NULL) {
		return new VolumeService\Snapshot($this, $id);
	}

	/**
	 * Returns a Collection of Snapshot objects
	 *
	 * @api
	 * @param boolean $detail TRUE to return full details
	 * @param array $filters array of filter key/value pairs
	 * @return Collection
	 */
	public function SnapshotList($filter=array()) {
		return $this->Collection(
			$this->Url('snapshots'),
			'Snapshot',
			'snapshots');
	}

}
