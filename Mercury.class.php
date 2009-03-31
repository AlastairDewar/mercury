<?php
/**
 * @author alastair
 *
 */
class Mercury {
	
	/*
	 * 	Twitter related variables 
	 * 
	*/
	private $twitter = null;
	private $twitter_username = null;
	private $twitter_password = null;
	private $twitter_placeholder = null;
	private $twitter_data = null;
	
	function Mercury()
	{
		//$this->notify_user("Twitter","Alastair Dewar has just sent you a tweet.");
	}
	
	public function listen()
	{
		$this->check_twitter();	
	}
	
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