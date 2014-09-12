<?

require_once("./scripts/RSA/RSA.php");


/*
 * AES encryption/decryption string or file. 
 * Key len - 32 char. 
 * 
 * How to use: 
	$data = "123 data 123";
	$key = "12345123451234512345123451234512";
	
	$AES = new AES_128($key);
	
	$aesEncr = base64_encode($AES->encryptString($data));
	echo "\r\n encrypted & base64-encoded: ". $aesEncr;
	$aesDecr = $AES->decryptString(base64_decode($aesEncr));
	echo "\r\n decrypted: ". $aesDecr;
	
	$AES->encryptFile("./f/sign.gif", false, "./f/sign_0.gif");
	$AES->decryptFile("./f/sign_0.gif", true);
 */
class AES_128 {
	private $key = "huitka";
	
	function __construct($key) {
		$this->key = $key;
	}
	
	/** 
	 * Encrypt file content. 
	 * @param sourceFile - absolute or relative path to source file. 
	 * @param rewrite - if set true encrypted data will be saved into source file. 
	 * @param targetFile - path to target file. If is set targetFile encrypted data will be saved into this file. 
	 * @return encrypted file content on success, null on error. 
	 * */
	public function encryptFile($sourceFile, $rewrite = false, $targetFile = null) {
		$res = null;
		
		if (@is_file($sourceFile)) {
			@$fileContent = file_get_contents($sourceFile);
			if ($fileContent) {
				$encData = $this->encryptString($fileContent);
				
				if ($rewrite) {
					file_put_contents($sourceFile, $encData);
				}
				else if ($targetFile != null) {
					file_put_contents($targetFile, $encData);
				}
				
				$res = $encData;
			}
		}
		
		return $res;
	}
	
	/** 
	 * Decrypt file content. 
	 * @param sourceFile - absolute or relative path to source file. 
	 * @param rewrite - if set true decrypted data will be saved into source file. 
	 * @param targetFile - path to target file. If is set targetFile decrypted data will be saved into this file. 
	 * @return decrypted file content on success, null on error. 
	 * */
	public function decryptFile($sourceFile, $rewrite = false, $targetFile = null) {
		$res = null;
		
		if (@is_file($sourceFile)) {
			@$fileContent = file_get_contents($sourceFile);
			if ($fileContent) {
				$decData = $this->decryptString($fileContent);
				
				if ($rewrite) {
					file_put_contents($sourceFile, $decData);
				}
				else if ($targetFile != null) {
					file_put_contents($targetFile, $decData);
				}
				
				$res = $decData;
			}
		}
		
		return $res;
	}
	
	
	/** 
	 * Encrypt string data. 
	 * @param str - string data to encrypt. 
	 * @return encrypted string data. 
	 */
	public function encryptString($str) {
		$block = mcrypt_get_block_size('rijndael_128', 'ecb');
		$pad = $block - (strlen($str) % $block);
		$str .= str_repeat(chr($pad), $pad);
		
		$res = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $str, MCRYPT_MODE_ECB);
		
		return $res;
	}
	
	/** 
	 * Decrypt string data. 
	 * @param str - string data to decrypt. 
	 * @return decrypted string data. 
	 */
	public function decryptString($str) {
		$str = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, $str, MCRYPT_MODE_ECB);
		
		$block = mcrypt_get_block_size('rijndael_128', 'ecb');
		$pad = ord($str[($len = strlen($str)) - 1]);
		$len = strlen($str);
		$pad = ord($str[$len-1]);
		
		return substr($str, 0, strlen($str) - $pad);
	}
}



/*
 * RSA encryption/decryption string data. 
 * 
 * Ho to use: 
	$data = "123 data 123";
	
	$RSA = new RSA();
	$privateKey = $RSA->getPEMPrivate();
	$encr = base64_encode($RSA->encrypt($data));
	echo "\r\n encrypted: ". $encr;
	
	//....
	
	$RSA = new RSA();
	$RSA->loadPEMPrivate($privateKey);
	$decr = $RSA->decrypt(base64_decode($encr));
	echo "\r\n decrypted: ". $decr;
 */
class RSA {
	private $RSA = null;
	
	private $publicKey = null;
	private $privateKey = null;
	
	function __construct() {
		$this->RSA = new Crypt_RSA();
		$this->RSA->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
		$this->generatePair();
	}
	
	/** 
	 * Encrypt string data. 
	 * @return encrypted string on success, NULL on error. 
	 * */
	public function encrypt($dataString) {
		$res = false;
		
		if ($this->publicKey != null) {
			$this->RSA->loadKey($this->publicKey);
			
			@$encr = $this->RSA->encrypt($dataString);
			
			if ($encr) {
				$res = $encr;
			}
		}
		
		return $res;
	}
	
	/**
	 * Decrypt string data.
	 * @return decrypted string on success, NULL on error.
	 * */
	public function decrypt($dataString) {
		$res = null;
		
		if ($this->privateKey != null) {
			$this->RSA->loadKey($this->privateKey);
			
			@$decr = $this->RSA->decrypt($dataString);
			
			if ($decr) {
				$res = $decr;
			}
		}
		
		return $res;
	}
	
	/**
	 * Load public key from PEM string. 
	 * */
	public function loadPEMPublic($keyString) {
		$this->publicKey = $keyString;
	}
	/**
	 * Get current public key. 
	 * @return PEM key string. 
	 * */
	public function getPEMPublic() {
		return $this->publicKey;
	}
	
	/** 
	 * Load private key from PEM string. 
	 * */
	public function loadPEMPrivate($keyString) {
		$this->privateKey = $keyString;
	}
	/**
	 * Get current private key. 
	 * @return PEM key string. 
	 * */
	public function getPEMPrivate() {
		return $this->privateKey;
	}
	
	/** 
	 * Genearate a new RSA keys pair. 
	 * @return array: { public: "public-key", private: "private-key" }
	 * */
	public function generatePair() {
		$keys = $this->RSA->createKey();
		
		$this->publicKey = $keys["publickey"];
		$this->privateKey = $keys["privatekey"];
		
		return array(
				"public" => $keys["publickey"],
				"private" => $keys["privatekey"]
			);
	}
}



?>