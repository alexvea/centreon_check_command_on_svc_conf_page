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

Functionnalities : 

"Get check command" :
- get check command via http POST from selected check command or selected service template (if both are selected it will show the selected check command).
- inputs validation if host is not set AND (check command OR service template are not set) and prevent to execute the check command.
  ![image](https://github.com/alexvea/centreon_check_command_on_svc_conf_page/assets/35368807/bc92637f-8904-451c-b554-5fa78f691a60)
- default check command textarea is not editable, if you double click on it you can edit it, when click again on "get check command" button, it will reset the textarea behavior.
  ![image](https://github.com/alexvea/centreon_check_command_on_svc_conf_page/assets/35368807/2ae93b49-2f45-4258-8822-6d4bf35a48db)


"Execute check command" :
- Execute the command via http POST from the check command textarea content.
- Check the log via http GET from gorgone token.

Known issues : 


"Get check command" : not working to get check command for some services templates (ie : App-Monitoring-Centreon-Process-centengine)(template of a template of a template ?)
