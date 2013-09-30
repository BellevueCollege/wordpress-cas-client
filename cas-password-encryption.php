<?php
	define("HASH_FUNC",'sha512');
	define("MODE",'ctr');
	define("SEPERATOR",'$');
	if (!defined('SECURE_AUTH_KEY'))
		define('SECURE_AUTH_KEY',  '+WPe<=swh[IZ*xs16,f._`U}:A?}|AtX8tk,[kzte7_yQOTm~qVY>VNNL6qAUUj^');
	if (!defined('SECURE_AUTH_SALT'))
		define('SECURE_AUTH_SALT', 'ec$_OY62)n^^6=V8S|=+9U-((6zNQ{AP7Iw;-${DD1[C8A>-FGgql`<>%-i|_9|C');
	define('DEFAULT_CIPHER_KEY', 'none');
/*
	The order of the cipher is from most preferred to least preferred.
	User can add there most preferred cipher at the top of the array with a unique key.
*/

	$ciphers = array(
    
	'a' => 'twofish256',
	'b' => 'serpent-256',
	'c' =>'rijndael-256',
	'd' =>'cast-256',
	'e' =>'twofish192',
	'f' =>'serpent-192',
	'g' =>'rijndael-192',
	'h' =>'twofish',
	'i' =>'serpent-128',
	'j' =>'rijndael-128',
	'k' =>'cast-128'
  );

function wpcasclient_encrypt($data , $cipher_list, $key = SECURE_AUTH_KEY, $salt =SECURE_AUTH_SALT ) {
	//error_log("data value 1 $$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$:".$data);

	if(!empty($data))
	{
	    $output = $salt.$data;
	    $cipher_key = DEFAULT_CIPHER_KEY;

	    // Check to see if mcrypt is installed before trying to encrypt    
	    if (function_exists('mcrypt_module_open'))
	    {
	      //$count = 0;
	    	if(is_array($cipher_list))	
	    	{
		      foreach ($cipher_list as $k => $val)
		      {
		       // $count++;
		        /*
		          mcrypt_module_open raises a warning when it can not find a cipher and
		          mode match. Suppressing these warnings as the code expects to try
		          cipher and mode pairs that may not exist in the system.
		        */
		        $encrypt_descript = @mcrypt_module_open($val, '', MODE, '');
		        if ($encrypt_descript) {
		          $cipher_key = $k;
		          $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($encrypt_descript), MCRYPT_DEV_URANDOM);
		          $key = substr(hash(HASH_FUNC, $key), 0, mcrypt_enc_get_key_size($encrypt_descript));
		          mcrypt_generic_init($encrypt_descript, $key, $iv);
		          $output = mcrypt_generic($encrypt_descript, $output);
		          mcrypt_generic_deinit($encrypt_descript);
		          $output = $iv.$output;
		          break;
		        }
		      }
		    }
	     
	    }
	    $cipher_key = base64_encode($cipher_key);
	    return $cipher_key.SEPERATOR.base64_encode($output);
	}
	return $data;
  }

function wpcasclient_decrypt($data , $cipher_list, $key = SECURE_AUTH_KEY, $salt =SECURE_AUTH_SALT) {
	//error_log("++++++++++++++++++++++++++++++get type: ".is_string($data));
	$output = "";
	if( !empty($data) && is_string($data))
	{
	  $splitpoint = strrpos($data, SEPERATOR);
	    $cipher_key = substr($data, 0, $splitpoint);
	    $cipher_key = base64_decode($cipher_key);
	    $output = base64_decode(substr($data, $splitpoint)); //+1 drops the '$' char

	    if ($cipher_key != DEFAULT_CIPHER_KEY ) {
	      $encrypt_descript = mcrypt_module_open($cipher_list[$cipher_key], '', MODE, '');
	      $ivsize = mcrypt_enc_get_iv_size($encrypt_descript);
	      $iv = substr($output, 0, $ivsize);
	      $key = substr(hash(HASH_FUNC, $key), 0, mcrypt_enc_get_key_size($encrypt_descript));
	      $output = substr($output, $ivsize);
	      mcrypt_generic_init($encrypt_descript, $key, $iv);
	      $output = mdecrypt_generic($encrypt_descript, $output);
	      mcrypt_generic_deinit($encrypt_descript);
		}
	}

    return substr($output, strlen($salt));
  }
/*
  $password = "R2d2c3po";
  $output = wpcasclient_encrypt($ciphers, SECURE_AUTH_KEY, SECURE_AUTH_SALT , $password);
  $input = wpcasclient_decrypt($ciphers, SECURE_AUTH_KEY, SECURE_AUTH_SALT, $output);
  error_log('Encrypted String: '.$output."\n\n");
  error_log('Decrypted String: '.$input);
*/

?>