<?php
/**
 * @author alastair
 *
 */
class Mercury {
	
	/*
	 * 	Twitter related variables 
	 */
	private $twitter = null;
	private $twitter_username = null;
	private $twitter_password = null;
	private $twitter_placeholder = null;
	private $twitter_data = null;
	
	function Mercury()
	{
		
	}
	
	public function listen()
	{
		$this->query_twitter();
		$this->check_twitter();	
		$this->query_facebook();
		$this->check_facebook();
		$this->query_bebo();
		$this->check_bebo();
		$this->query_windows_live_messenger();
		$this->check_windows_live_messenger();
		$this->query_myspace();
		$this->check_myspace();
		$this->query_google_talk();
		$this->check_google_talk();					
	}
	
	private function check_facebook(){}
	private function query_facebook(){}
	public function setup_facebook($facebook_username, $facebook_password){}
	
	private function check_bebo(){}
	private function query_bebo(){}
	public function setup_bebo($bebo_username, $bebo_password){}
	
	private function check_windows_live_messenger(){}
	private function query_windows_live_messenger(){}
	public function setup_windows_live_messenger($wlm_username, $wlm_password){}
	
	private function check_myspace(){}
	private function query_myspace(){}
	public function setup_myspace($myspace_username, $myspace_password){}
	
	private function check_yahoo_messenger(){}
	private function query_yahoo_messenger(){}
	public function setup_yahoo_messenger($yahoo_username, $yahoo_password){}
	
	private function check_google_talk(){}
	private function query_google_talk(){}
	public function setup_google_talk($google_username, $google_password){}
	
	private function check_twitter()
	{
		if($this->twitter_placeholder == null)
		{
			$this->twitter_placeholder = md5($twitter_data[0]);
			$this->notify_user("Twitter","Account ".$this->twitter_username." successfully loaded.");
		}
		else
		{
			if(strcmp($twitter_data[0], $this->twitter_placeholder) == 0)
			{
				// No Twitter updates yet.
			}
			else
			{
				$found = false;
				foreach($twitter_data as &$reply)
				{
					if(strcmp(md5($reply), $this->twitter_placeholder) == 0)
					{
						$found = true;
					}
					if($found == true)
					{
						$reply = null;
					}
					else
					{
						$this->notify_user("Twitter", $reply['user']['name']." has just sent you a tweet.");
					}
				}
			}
		}
	}
	
	private function query_twitter()
	{
		require("libraries/twitter.php");	
		$twitter = new twitter();
		$twitter->username = $this->twitter_username;
		$twitter->password = $this->twitter_password;
		$this->twitter_data = $twitter->getReplies();
	}
	
	public function setup_twitter($twitter_username, $twitter_password)
	{
		$this->twitter_username = $twitter_username;
		$this->twitter_password = $twitter_password;
		$this->query_twitter();
	}
	
	private function notify_user($protocol, $message)
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
						echo exec('notify-send -u normal -t 5000 -i info "'.$protocol.'" "'.$message.'"');
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
?>