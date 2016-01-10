<?php
/*
 * @package		Template
 * @subpackage	Template Libraries
 * @category	Template Libraries
 * @copyright   Copyright (c) Toni Leigh Sharpe (2012)
 *
*/
?>
<?php
    if (strlen($login_form)>0)
    {
		echo "<div id='page_login'>";
        echo $login_form;
		echo "</div>";
    }
    else
    {
		$redirect_signin=$this->config->item('redirect_signin');

		if (is_array($redirect_signin) &&
			isset($redirect_signin[$user['user_type']]))
		{
			$reload_url=$redirect_signin[$user['user_type']];
		}
		else
		{
			$reload_url=$redirect_signin;
		}

        echo "you are already logged in <a href='".$reload_url."'>click here for back stage</a>";
    }
?>
