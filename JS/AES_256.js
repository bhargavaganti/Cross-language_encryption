
/**
 * AES-256 ECB encryption/decryption 
 * 
 * Other required libs: 
	- CryptoJS_AES.js
	- Files_1.2.js
	
 * How to use: 
	var keyStr = "b946744f6b5ede4bb021c0a7bea9b303";
	var file = "http://localhost:8081/elan_0/api_docs.php?getFile=88ecafc378f9343b7a4a0d4f56082232.jpg";
	
	var base64Key = Files.base64StringEncode(keyStr);
	
	Files.base64.fileUrl(file, function(base) {
			//console.debug(base64Key);
			//console.debug("base64Data", base);
			
			var Enc = new AES_256(base64Key);
			
			var encrypted = Enc.encrypt(base);
			//console.debug("encryptedBase64", "data:image/jpeg;base64," + encrypted);
			
			var decrypted = Enc.decrypt(encrypted);
			console.debug("decryptedBase64", "data:image/jpeg;base64," + decrypted);
			
		});

 */

function AES_256(keyBase64) {
	var This = this;
	
	This.key = CryptoJS.enc.Base64.parse(keyBase64);

	This.encrypt = function(dataBase64) {
		var encryptedData = CryptoJS.AES.encrypt(
				CryptoJS.enc.Base64.parse(dataBase64),
				This.key,
				{
					mode: CryptoJS.mode.ECB,
					padding: CryptoJS.pad.Pkcs7
				}
			);
		
		return encryptedData.toString();
	};
	
	This.decrypt = function(dataBase64) {
		var decryptedData = CryptoJS.AES.decrypt(
				dataBase64,
				This.key,
				{
					mode: CryptoJS.mode.ECB,
					padding: CryptoJS.pad.Pkcs7
				}
			);
		
		return decryptedData.toString(CryptoJS.enc.Base64);
	};
}


