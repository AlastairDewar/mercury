<?php
/**
 * @author alastair
 *
 */
class Mercury {
	
	public $accounts = array();
	
	function Mercury()
	{
		$this->load_libraries();
	}
	
	private function load_libraries(){
		require("./libraries/twitter.php");
	}
	
	public function check_all_protocols()
	{
		#$this->notify_user("System","Checking ".count($this->accounts)." protocols");
		foreach($this->accounts as $account)
		{
			unserialize($account);
			$replies = $account->query();
			print_r($replies);
			foreach($replies as $reply)
			{
				$this->notify_user($account->protocol_name, $reply);
			}
		}				
	}
	
	public function setup_account($protocol, $username, $password)
	{
		// TODO check to see if the protocol is recognised
		// TODO check to see if it is a valid account.
		switch ($protocol)
		{
    	case "twitter":
    		$account = new Twitter();
        	$account->username = $username;
        	$account->password = $password;
			serialize($account);
			array_push($this->accounts, $account);
        	break;
		}

		return true;
	}
	
	public function notify_user($protocol, $message)
	{
		if(strcasecmp(exec('uname'),"Linux") != 0)
		{
			die("Error: Mercury currently only works with Linux based systems.");
		}
		else
		{
			if( ini_get('safe_mode') )
			{
				die("Error: PHP Safe mode is set.");
			}
			else
			{
				if(strcmp(exec("whoami"),"www-data") == 0)
				{
					die("Error: Mercury cannot be run from a normal apache web server installation.");
				}
				else
				{
					$notify = exec('which notify-send');
					if($notify != null)
					{
						echo exec('notify-send -u normal -t 3000 -i info "'.$protocol.'" "'.$message.'"');
					}
					else
					{
						die("Error: notify-send package isn't installed.");
					}
				}
			}
		}
	} 
}

class Protocol extends Mercury
{
	var $protocol_name = "null";
	var $latest = 0;
	
	function Protcol()
	{
		
	}
}
?>