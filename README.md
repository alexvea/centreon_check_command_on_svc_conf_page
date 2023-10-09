# centreon_check_command_on_svc_conf_page
<h2>!!!!only for tests at the moment!!!!</h2>

How to use : 
On service (not host) configuration page, click on "Get check command" button to get the current check command with all the MACROS values coming from host, host template, and current service.
![image](https://github.com/alexvea/centreon_check_command_on_svc_conf_page/assets/35368807/329ea2cb-fc20-4d30-b6d0-b25fee3ddc92)

Then click on "Execute check command" button to execute it via gorgone to the related poller. It will display the monitoring poller, the linux user and also the result.
![image](https://github.com/alexvea/centreon_check_command_on_svc_conf_page/assets/35368807/6e8ff728-99cb-4e25-b534-ed59788163e7)


How to install : 
1) <code>cd /tmp/</code>
2) <code>wget https://github.com/alexvea/centreon_check_command_on_svc_conf_page/archive/refs/heads/main.zip</code>
3) <code>unzip main.zip</code>
4) <code>cd centreon_check_command_on_svc_conf_page-main</code>
5) <code>cat show_install_cmd</code>
6) Execute the commands displayed.
7) Go to GUI, any service configuration page.
    

Known issues : 


"Get check command" : not working to get check command for some services templates (ie : App-Monitoring-Centreon-Process-centengine)(template of a template of a tempalte ?)


"Execute check command" : issue with options quotes examples : 
````
--critical-status='%{type} eq "output"'
````
````
/usr/lib/centreon/plugins//centreon_centreon_central.pl --plugin=apps::centreon::local::plugin --hostname='' --mode=broker-stats --broker-stats-file='/var/lib/centreon-broker/central-broker-master-stats.json' --broker-stats-file='/var/lib/centreon-broker/central-rrd-master-stats.json' --broker-stats-file='/var/lib/centreon-engine/central-module-master-stats.json' --filter-name='' --critical-status='%{type} eq "output" and %{queue_file_enabled} =~ \/true|yes\/i'
````
error :
````
UNKNOWN: Unsafe code evaluation: Bareword \\\"output\\\" not allowed while \\\"strict subs\\\" in use at (eval 19) line 1.
````

