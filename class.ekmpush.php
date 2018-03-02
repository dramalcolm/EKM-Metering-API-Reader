<?php
/**
 * Created by PhpStorm.
 * User: douglasmalcolm
 * Date: 11/10/17
 * Time: 4:49 PM
 */
class ekmpush {

	protected $version, $api_key, $result_counter, $time_zone;

	public function __construct(){

	}

	public function configAPI($args){
		isset($args["version"])?$version=$args["version"]:$version="";
		isset($args["api_key"])?$api_key=$args["api_key"]:$api_key="";
		isset($args["count"])?$result_counter=$args["count"]:$result_counter="";
		isset($args["ts"])?$time_zone=$args["ts"]:$time_zone="";
		isset($args["format"])?$format=$args["format"]:$format="";
	}

	public function _pullEkmData(){


		$apiObject= $this->callApi($this->apiURL());
		//Since the apiObject returns an object, we want to now convert this object into an array
		$value_array = $this->objectToArray($apiObject);

		// This just displays the object but you can use whatever
		// code you would like to work with the object here
		$meter_info = $value_array["readMeter"]["ReadSet"];

		foreach($meter_info as $meter){

			//Saving the meter information to the MASTER TABLE
			$this->_saveMeterMasterRecord($meter);

			//Save details of each meter if found
			if(isset($meter["ReadData"])){
				$this->_saveMeterDetails($$meter["ReadData"]);
			}

		}

	}

	private function _saveMeterMasterRecord($meter_master){

			isset($meter_master["Meter"])?$meter_data=$meter_master["Meter"]:$meter_data="";
			isset($meter_master["Group"])?$Group=$meter_master["Group"]:$Group="";
			isset($meter_master["MAC_Addr"])?$MAC_Addr=$meter_master["MAC_Addr"]:$MAC_Addr="";
			isset($meter_master["Interval"])?$Interval=$meter_master["Interval"]:$Interval="";
			isset($meter_master["Protocol"])?$Protocol=$meter_master["Protocol"]:$Protocol="";
			isset($meter_master["Credits"])?$Credits=$meter_master["Credits"]:$Credits="";
			isset($meter_master["Bad_Reads"])?$Bad_Reads=$meter_master["Bad_Reads"]:$Bad_Reads="";
			isset($meter_master["Good_Reads"])?$Good_Reads=$meter_master["Good_Reads"]:$Good_Reads="";

			$in_val = array("meter"=>$meter_data,"m_group"=>$Group,"mac_addr"=>$MAC_Addr,"m_interval"=>$Interval,"protocol"=>$Protocol,"credits"=>$Credits,"bad_reads"=>$Bad_Reads,"good_reads"=>$Good_Reads);
			//You can then save this $in_val to a database as the MASTER RECORD for each meter

	}

	private function _saveMeterDetails($meter_details){

		foreach($meter_details as $details){
			//Adding details to the mDetails table
			$details["meter"] = $meter["Meter"];
			$details["version"] = $version["value"];

			//Removing the . from the JSON key without using array_map function
			if(isset($details["PERR.NR"])){
				$details["PERRNR"] = $details["PERR.NR"];
				unset($details["PERR.NR"]);
			}
			//Removing the . from the JSON key without using array_map function
			if(isset($details["PERR.RXP"])){
				$details["PERRRXP"] = $details["PERR.RXP"];
				unset($details["PERR.RXP"]);
			}

			//Loads acceptable key based on the meter version
			$this->acceptedKeys($version);
			//Remove unwanted key value pairs from details array
			$output = array_intersect_key( $details, array_flip( $this->whitelist ) );
			//You can now submit this $output array to a database
			
		}
			
	}

	private function apiURL(){
		//This URL is the api call to the EKM Server
		$_url = 'http://io.ekmpush.com/readMeter?&ver='.$version.'&key='.$api_key.'&fmt='.$format.'&cnt='.$result_counter.'&tz='.$time_zone;
		return $_url;
	}
	// Call the callApi function to create a usable
	// object named $apiObject from the API request URL.
	// Put the API request URL in the call
	private function callApi ($apiRequest='') {
		# we want text output for debugging. Either this works or you need to look into
		# source in your browser (or even better, debug with the `curl` commandline client.)
				//header('Content-Type: text/plain');

		# For quick and dirty debugging - start to tell about if something is worth
		# to warn or notice about. (do logging in production)
		error_reporting(~0); ini_set('display_errors', 1);

		$context = stream_context_create(array(
			'http' => array(
				'follow_location' => false,
				'timeout' => 36000,
			),
			'ssl' => array(
				'verify_peer' => true,
			),
		));

		$json=@file_get_contents($apiRequest);
		if(!$json){
			return 'Error connecting to EKM Server';
		}
		$jsonObject=json_decode($json);
		return ($jsonObject);
	}
	
	// This function list all the acceptable keys that will be mapped to database tables for saving if needed
	private function acceptedKeys($version){
		$this->accepted_keys = "";
		//Version 3 meter fields/column key listing
		if($version == 'v3'){
			$this->whitelist = array("meter", "Good", "Date", "Time", "Time_Stamp_UTC_ms", "Firmware", "Model", "kWh_Scale", "kWh_Tot", "kWh_Tariff_1", "kWh_Tariff_2", "kWh_Tariff_3", "kWh_Tariff_4", "Rev_kWh_Tot", "Rev_kWh_Tariff_1", "Rev_kWh_Tariff_2", "Rev_kWh_Tariff_3", "Rev_kWh_Tariff_4", "RMS_Volts_Ln_1", "RMS_Volts_Ln_2", "RMS_Volts_Ln_3", "Amps_Ln_1", "Amps_Ln_2", "Amps_Ln_3", "Power_Factor_Ln_1", "Power_Factor_Ln_2", "Power_Factor_Ln_3", "RMS_Watts_Ln_1", "RMS_Watts_Ln_2", "RMS_Watts_Ln_3", "RMS_Watts_Tot", "RMS_Watts_Max_Demand", "Max_Demand_Period", "Meter_Status_Code", "CT_Ratio", "PERRNR", "PERRRXP","version");
		}else{
		//Version 4 meter fields/column key listing
			$this->whitelist = array("meter", "Good", "Date", "Time", "Time_Stamp_UTC_ms", "Firmware", "Model", "kWh_Tot", "kWh_Tariff_1", "kWh_Tariff_2", "Rev_kWh_Tot", "Rev_kWh_Tariff_1", "Rev_kWh_Tariff_2", "RMS_Volts_Ln_1", "RMS_Volts_Ln_2", "Amps_Ln_1", "Amps_Ln_2", "RMS_Watts_Ln_1", "RMS_Watts_Ln_2", "RMS_Watts_Tot", "Power_Factor_Ln_1", "Power_Factor_Ln_2", "Power_Factor_Ln_3", "RMS_Watts_Max_Demand", "Max_Demand_Period", "CT_Ratio", "Pulse_Ratio_1", "Pulse_Ratio_2", "Pulse_Ratio_3", "Reactive_Energy_Tot", "kWh_Rst", "Rev_kWh_Rst", "Reactive_Pwr_Ln_1", "Reactive_Pwr_Ln_2", "Reactive_Pwr_Tot", "Line_Freq", "State_Watts_Dir", "State_Out", "kWh_Scale", "kWh_Ln_1", "kWh_Ln_2", "kWh_Ln_3", "Rev_kWh_Ln_1", "Rev_kWh_Ln_2", "Rev_kWh_Ln_3", "CF_Ratio", "Net_Calc_Watts_Ln_1", "Net_Calc_Watts_Ln_2", "Net_Calc_Watts_Tot", "PERRNR", "PERRRXP","version");
		}
	}

	        //stdClass as array
    private function objectToArray($d){
        if (is_object($d)) {
                // Gets the properties of the given object
                // with get_object_vars function
                $d = get_object_vars($d);
        }

        if (is_array($d)) {
                /*
                * Return array converted to object
                * Using __FUNCTION__ (Magic constant)
                * for recursive call
                */
                return array_map(__METHOD__, $d);
        }else {
                // Return array
                return $d;
        }
    }
}