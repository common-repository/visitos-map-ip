<?php

if(function_exists('getrecordwithdnsservice'))
	return;

require("geoipcity.inc");
class geoipdata {

	var $gi = '';
	var $record = '';

	// uncomment for Shared Memory support
	// geoip_load_shared_mem("/usr/local/share/GeoIP/GeoIPCity.dat");
	// $gi = geoip_open("/usr/local/share/GeoIP/GeoIPCity.dat",GEOIP_SHARED_MEMORY);

	function __construct($path)
	 {
		$this->gi = geoip_open($path."GeoLiteCity.dat", GEOIP_STANDARD);
	 }
	 

	public function getGeoData($ip = false, $retdata = false)
	 {
	 	$ipdata = array();
		$this->record = geoip_record_by_addr($this->gi, $ip);

		if($retdata['code'])
			$ipdata['code'] = $this->record->country_code;

		if($retdata['name'])
			$ipdata['name'] = $this->record->country_name;

		if($retdata['city'])
			$ipdata['city'] = $this->record->city;

		if($retdata['latlon'])
			$ipdata['latlon'] = $this->record->latitude.', '.$this->record->longitude;
		
		return $ipdata;
		$this->closeGetData();
	 }
	 

	public function closeGetData()
	 {
		geoip_close($this->gi); 
	 }

}

?>
