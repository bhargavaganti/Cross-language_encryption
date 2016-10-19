<?

require_once("./RSA/RSA.php");

/**
 * AES encryption/decryption string or file. Key len - 32 char. <br/>
 * 
 * How to use:
	$data = "123 data 123";
	$key = "12345123451234512345123451234512";
	
	$AES = new AES_256($key);
	
	$aesEncr = base64_encode($AES->encryptString($data));
	echo "\r\n encrypted & base64-encoded: ". $aesEncr;
	$aesDecr = $AES->decryptString(base64_decode($aesEncr));
	echo "\r\n decrypted: ". $aesDecr;
	
	$AES->encryptFile("./f/sign.gif", false, "./f/sign_0.gif");
	$AES->decryptFile("./f/sign_0.gif", true);
 */
class AES_256 {
	private $key = "huitka";
	
	function __construct($key) {
		$this->key = $key;
	}
	
	/**
	 * Encrypt file content
	 * @param string $sourceFile - absolute or relative path to source file
	 * @param bool $rewrite - if set true encrypted data will be saved into source file
	 * @param string $targetFile - path to target file. If is set, encrypted data will be saved into this file
	 * @return string - encrypted file content on success, null on error
	 */
	public function encryptFile($sourceFile, $rewrite = false, $targetFile = null) {
		$res = null;
		
		if (is_file($sourceFile)) {
			@$fileContent = file_get_contents($sourceFile);
			if ($fileContent) {
				$encData = $this->encryptString($fileContent);
				
				if ($rewrite) {
					@file_put_contents($sourceFile, $encData);
				}
				else if ($targetFile != null) {
					@file_put_contents($targetFile, $encData);
				}
				
				$res = $encData;
			}
		}
		
		return $res;
	}
	
	/**
	 * Decrypt file content
	 * @param string $sourceFile - absolute or relative path to source file
	 * @param bool rewrite - if set true decrypted data will be saved into source file
	 * @param string $targetFile - path to target file. If is set targetFile decrypted data will be saved into this file
	 * @return string - decrypted file content on success, null on error
	 */
	public function decryptFile($sourceFile, $rewrite = false, $targetFile = null) {
		$res = null;
		
		if (is_file($sourceFile)) {
			@$fileContent = file_get_contents($sourceFile);
			if ($fileContent) {
				$decData = $this->decryptString($fileContent);
				
				if ($rewrite) {
					@file_put_contents($sourceFile, $decData);
				}
				else if ($targetFile != null) {
					@file_put_contents($targetFile, $decData);
				}
				
				$res = $decData;
			}
		}
		
		return $res;
	}
	
	
	/**
	 * Encrypt string data
	 * @param string $str - string data to encrypt
	 * @return string - encrypted
	 */
	public function encryptString($str) {
		$block = mcrypt_get_block_size('rijndael_256', 'ecb');
		$pad = $block - (strlen($str) % $block);
		$str .= str_repeat(chr($pad), $pad);
		
		$res = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->key, $str, MCRYPT_MODE_ECB);
		
		return $res;
	}
	
	/**
	 * Decrypt string data
	 * @param string $str - string data to decrypt
	 * @return string - decrypted
	 */
	public function decryptString($str) {
		$str = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->key, $str, MCRYPT_MODE_ECB);
		
		$block = mcrypt_get_block_size('rijndael_256', 'ecb');
		$pad = ord($str[($len = strlen($str)) - 1]);
		$len = strlen($str);
		$pad = ord($str[$len-1]);
		
		return substr($str, 0, strlen($str) - $pad);
	}
}


/**
 * RSA encryption/decryption string data. <br/>
 * 
 * Ho to use: 
	$data = "123 data 123";
	
	$RSA = new RSA();
	$RSA->generatePair();
	$privateKey = $RSA->getPEMPrivate();
	$encr = base64_encode($RSA->encrypt($data));
	echo "\r\n encrypted: " . $encr;
	
	// ...
	
	$RSA = new RSA();
	$RSA->loadPEMPrivate($privateKey);
	$decr = $RSA->decrypt(base64_decode($encr));
	echo "\r\n decrypted: " . $decr;
 * 
 */
class RSA {
	private $RSA = null;
	
	private $publicKey = null;
	private $privateKey = null;
	
	function __construct() {
		$this->RSA = new Crypt_RSA();
		$this->RSA->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
	}
	
	/**
	 * Encrypt string data
	 * @param string $data - source data to encrypt
	 * @return string - encrypted on success, null on error
	 */
	public function encrypt($data) {
		$res = false;
		
		if ($this->publicKey != null) {
			@$pk = openssl_pkey_get_public($this->publicKey);
			
			@$res = openssl_public_encrypt($data, $encr, $pk);
			
			if ($res && $encr) {
				$res = $encr;
			}
		}
		
		return $res;
	}
	
	/**
	 * Decrypt string data
	 * @param string $data - encrypted data to decrypt
	 * @return string decrypted on success, null on error
	 */
	public function decrypt($data) {
		$res = null;
		
		if ($this->privateKey != null) {
			@$pk = openssl_pkey_get_private($this->privateKey);
			
			@$res = openssl_private_decrypt($data, $decr, $pk);
			
			if ($res && $decr) {
				$res = $decr;
			}
		}
		
		return $res;
	}
	
	/**
	 * Genearate a new RSA keys pair
	 * @return array like: <br/>
	 * {public: string, private: string}
	 */
	public function generatePair() {
		$keys = $this->RSA->createKey();
		
		$this->publicKey = $keys["publickey"];
		$this->privateKey = $keys["privatekey"];
		
		return array(
				"public" => $keys["publickey"],
				"private" => $keys["privatekey"]);
	}
	
	/**
	 * Load public key from PEM string
	 * @param string $keyString - PEM public key
	 */
	public function loadPEMPublic($keyString) {
		$this->publicKey = $keyString;
	}
	
	/**
	 * Get current public key
	 * @return string - PEM public key
	 */
	public function getPEMPublic() {
		return $this->publicKey;
	}
	
	/**
	 * Load private key from PEM string
	 * @param string $keyString - PEM private key
	 */
	public function loadPEMPrivate($keyString) {
		$this->privateKey = $keyString;
	}
	
	/**
	 * Get current private key
	 * @return string - PEM private key
	 */
	public function getPEMPrivate() {
		return $this->privateKey;
	}
}



?>
