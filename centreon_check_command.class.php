<?php

/*
 * Copyright 2005-2020 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <htcommand://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : command@centreon.com
 *
 */


require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once __DIR__ . "/centreon_configuration_objects.class.php";

class CentreonCheckCommand extends CentreonConfigurationObjects
{
    /**
     * CentreonConfigurationCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return array
     * @throws Exception
     * @throws RestBadRequestException
     */
 
    public function postgetCommand()
    {

        $queryValues = array();
	$AllMacros = array();
	$Macros_from_conf = array();
	// Get the parameters from json
	$json = file_get_contents('php://input');
	$obj = json_decode($json);
	foreach ($obj as $key => $json) { 
	     foreach($json as $key => $value) {
		     if ($key == "macros") {
			foreach($value as $key_macro=>$value_macro)
			{
				array_push($Macros_from_conf, array("macro_name" => '$_SERVICE'.$key_macro."$", "macro_value" => $value_macro));	
				array_push($AllMacros, $Macros_from_conf);
			}
		     } else {
		     	$queryValues[$key] = $value;
		     } 
  	     }
	}
	// get command line from sql
	if (isset($queryValues["serviceCommandName"])) {
		error_log("function getCommandFromCommandName with " .$queryValues["serviceCommandName"]);
		
		        $queryCommand = 'SELECT command_line ' .
       			                'FROM command ' .
            				'WHERE command_activate = "1" AND command_name = :commandName ' .
          				'LIMIT 1';

		        $bindParam = ':commandName';
			$bindValue = $queryValues["serviceCommandName"];
	} else {
		error_log("function getCommandFromServiceTemplateName with " .$queryValues["serviceTemplateName"]);
 			$queryCommand = 'SELECT command_line ' .
                         	         'FROM command ' .
                               		 'WHERE command_id ' .
				         'IN (SELECT command_command_id from service WHERE service_description = :serviceTemplateName)';
                        $bindParam = ':serviceTemplateName';
			$bindValue = $queryValues["serviceTemplateName"];
	}
			
       $stmt = $this->pearDB->prepare($queryCommand);
       $stmt->bindParam($bindParam,$bindValue, PDO::PARAM_STR);
       $stmt->execute();	
       $commandWithPlaceholders = $stmt->fetch();
		if(empty($commandWithPlaceholders)) {
                        $queryCommand7 = 'SELECT command_line ' .
                                'FROM command ' .
                                'WHERE command_id ' .
                                'IN (SELECT command_command_id FROM service WHERE service_id ' .
                                'IN (SELECT service_template_model_stm_id from service WHERE service_description = :serviceTemplateName))';
                        $stmt7 = $this->pearDB->prepare($queryCommand7);
                        $stmt7->bindParam(':serviceTemplateName', $queryValues["serviceTemplateName"], PDO::PARAM_STR);
                        $stmt7->execute();
                        $commandWithPlaceholders = $stmt7->fetch();
                }

        // get macros from host from template
	//select host_macro_name,host_macro_value from on_demand_macro_host where host_host_id in (select host_tpl_id from host_template_relation where host_host_id in (select host_id from host where host_name = "65786_host") UNION ALL select host_tpl_id from host_template_relation where host_host_id in (select host_id from host where host_name = "65786_host" UNION ALL select host_tpl_id from host_template_relation where host_host_id in (select host_id from host where host_name = "65786_host")));
	$queryCommand = 'SELECT host_macro_name,host_macro_value ' .
		'FROM on_demand_macro_host ' .
		'WHERE host_host_id ' .
		'IN (SELECT host_tpl_id FROM host_template_relation WHERE host_host_id ' .
		'IN (SELECT host_id FROM host WHERE host_name = :host_name) ' .
		'UNION ALL SELECT host_tpl_id FROM host_template_relation WHERE host_host_id ' .
		'IN (SELECT host_id FROM host WHERE host_name = :host_name ' .
		'UNION ALL SELECT host_tpl_id FROM host_template_relation WHERE host_host_id ' .
		'IN (SELECT host_id FROM host WHERE host_name = :host_name)))';
        $stmt = $this->pearDB->prepare($queryCommand);
        $stmt->bindParam(':host_name', $queryValues["host_name"], PDO::PARAM_STR);
        $stmt->execute();
	error_log(print_r($stmt, true));
        $macrosFromHostTemplates = $stmt->fetchall();
	error_log(print_r($macrosFromHostTemplates, true));
      // get macros from host
      	
        $queryCommand = 'SELECT host_macro_name,host_macro_value ' .
                'FROM on_demand_macro_host ' .
                'WHERE host_host_id ' .
                'IN (SELECT host_id from host where host_name = :host_name)';

        $stmt = $this->pearDB->prepare($queryCommand);
        $stmt->bindParam(':host_name', $queryValues["host_name"], PDO::PARAM_STR);
        $stmt->execute();
        $macrosFromHost = $stmt->fetchall();
	$queryCommand = 'SELECT host_address, host_snmp_version, host_snmp_community ' .
        	         'FROM host ' .
               		 'WHERE host_id ' .
               		 'IN (SELECT host_id FROM host WHERE host_name = :host_name)';

        $stmt = $this->pearDB->prepare($queryCommand);
        $stmt->bindParam(':host_name', $queryValues["host_name"], PDO::PARAM_STR);
        $stmt->execute();
        $infosFromHost = $stmt->fetch();
		foreach($infosFromHost as $key => $value){
			if ($key == "host_address") {
			$new_key = "$".str_replace("_","",strtoupper($key))."$";	   
			} else {
			$new_key = "\$_".str_replace("_","",strtoupper($key))."$";	   
			}
 			unset($infosFromHost[$key]);
			array_push($infosFromHost, array("macro_name_host" => $new_key, "macro_value_host" => $value));
		}

      // get macros from nagios
        $queryCommand = 'SELECT resource_name, resource_line FROM cfg_resource';
	$stmt = $this->pearDB->prepare($queryCommand);
	$stmt->execute();
	$macrosFromNagios = $stmt->fetchall();

      // replace all macros from commandWithPlaceholders
	array_push($AllMacros,$macrosFromHost,$macrosFromHostTemplates,$macrosFromNagios,$infosFromHost); 	

	foreach( $AllMacros as $macros ) { 
		foreach( $macros as $macro ) {
			$macro_name = $macro[array_key_first($macro)];
			$macro_value =  $macro[array_key_last($macro)];
			$commandWithPlaceholders = str_replace($macro_name,$macro_value,$commandWithPlaceholders);		
		}
	}
	// replace all empty macros 
	$patterns = array();
	$patterns[0] = '/\$_[A-Z\][0-9]+\$/';
	$patterns[1] = '/\$[A-Z\][0-9]+\$/';
	$replacements = array();
	$replacements[2] = '';
	$replacements[1] = '';
	$command = preg_replace($patterns,$replacements,$commandWithPlaceholders);

	return $command;
    }
    public function postexecuteCommand()
	{
	$json = file_get_contents('php://input');
	// Converts it into a PHP object 
	$json_data = json_decode($json, true);
	$command = print_r($json_data[0]['command'], true);
	$host_name = print_r($json_data[1]['host_name'], true);
	//select nagios_server_id from ns_host_relation where host_host_id in (select host_id from host where host_name = "65786_host");
        $queryCommand6 = 'SELECT nagios_server_id ' .
                'FROM ns_host_relation ' .
                'WHERE host_host_id ' .
                'IN (SELECT host_id from host where host_name = :host_name)';

        $stmt6 = $this->pearDB->prepare($queryCommand6);
        $stmt6->bindParam(':host_name', $host_name, PDO::PARAM_STR);
        $stmt6->execute();
        $host_poller_id = $stmt6->fetch();
	$url = "http://localhost:8085/api/nodes/".$host_poller_id['nagios_server_id']."/core/action/command";
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$headers = array(
 		"Accept: application/json",
  	"Content-Type: application/json",
	);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

	$data = <<<DATA
		[{
		"command": "$command"
		}]
	DATA;
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	$json_curl_data = curl_exec($curl);
	curl_close($curl);
	$obj_resp = json_decode($json_curl_data);
	$obj_resp->poller_id=$host_poller_id['nagios_server_id'];
	$json_resp = json_encode($obj_resp);
	return $json_resp;
}

    public function getgetlogCommand(){
        $token = $this->arguments['token_id'];
        $poller_id = $this->arguments['poller_id'];
        $url = "http://localhost:8085/api/nodes/".$poller_id."/log/".$token;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $headers = array(
            "Accept: application/json"
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $resp = curl_exec($curl);
        curl_close($curl);
    return $resp;
    }

}
