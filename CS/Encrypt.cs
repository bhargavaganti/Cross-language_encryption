using System;
using System.Collections.Generic;
using System.IO;
using System.Security.Cryptography;
using System.Text;


/*
 * AES encryption/decryption byte data. 
 * Key len - 32 char. 
 
 * How to use: 
	string data = "123 wert 123";
	AES_128 encrypt = new AES_128("ABC12345123451234512345123451234");
	byte[] encr = encrypt.encrypt(Encoding.UTF8.GetBytes(data));
	Console.WriteLine("encrypted base64: " + Convert.ToBase64String(encr));
	
	byte[] decr = encrypt.decrypt(encr);
	Console.WriteLine("decrypted: " + Encoding.UTF8.GetString(decr));
 */
class AES_128 {
	private string key;
	
	public AES_128(string key) {
		this.key = key;
	}
	
	public byte[] encrypt(byte[] data) {
		byte[] res = null;
		
		try {
			res = this.encryptBytesAES128(data);
		} catch(Exception e) {
			Console.WriteLine("Error AES encryption: " + e.Message);
		}
		
		return res;
	}
	
	public byte[] decrypt(byte[] encryptedData) {
		byte[] res = null;
		
		try {
			res = this.decryptBytesAES128(encryptedData);
		} catch(Exception e) {
			Console.WriteLine("Error AES decryption: " + e.Message);
		}
		
		return res;
	}
	
	private RijndaelManaged configAES128() {
		byte[] keyBytes = Encoding.UTF8.GetBytes(this.key);
		
		RijndaelManaged rjnd = new RijndaelManaged();
		rjnd.Key = keyBytes;
		rjnd.Mode = CipherMode.ECB;
		rjnd.Padding = PaddingMode.PKCS7;
		
		return rjnd;
	}
	
	private byte[] encryptBytesAES128(byte[] data) {
		RijndaelManaged rjnd = configAES128();
		ICryptoTransform transform = rjnd.CreateEncryptor();
		
		byte[] resultBytes = transform.TransformFinalBlock(data, 0, data.Length);
		
		return resultBytes;
	}
	
	private byte[] decryptBytesAES128(byte[] encryptedData) {
		RijndaelManaged rjnd = configAES128();
		
		ICryptoTransform transform = rjnd.CreateDecryptor();
		byte[] resultBytes = transform.TransformFinalBlock(encryptedData, 0, encryptedData.Length);
		
		return resultBytes;
	}
}


/*
 * RSA encryption/decryption byte data. 
 
 * How to use: 
	string publicKey = "-----BEGIN PUBLIC KEY-----MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCCSJhJ0Uws0gpESsZY4IuQbg9Xp1wks09cExmp3QEwsJNefF6c9j65n7iwGy3PRt8RwVo5kbaqliQMB6k5DLPbUD2wa+XKGlId8YrX6MHEADKy2WIVjAg0WI9ie0EG3OUJDtFqOxsD7AWTWwsF5XYKvw30cqe5JlPxpSRnXuM5kwIDAQAB-----END PUBLIC KEY-----";
	string privateKey = "-----BEGIN RSA PRIVATE KEY-----MIICWwIBAAKBgQCCSJhJ0Uws0gpESsZY4IuQbg9Xp1wks09cExmp3QEwsJNefF6c9j65n7iwGy3PRt8RwVo5kbaqliQMB6k5DLPbUD2wa+XKGlId8YrX6MHEADKy2WIVjAg0WI9ie0EG3OUJDtFqOxsD7AWTWwsF5XYKvw30cqe5JlPxpSRnXuM5kwIDAQABAoGABtGR1tszZ20eyHA5bVFjPI3mE6pYsjsIPkNppnBArbGwJNPRh9mDcuefHOhvP1fwONerxzOPIeJ1xINqIeg+SXsAzw4ElZgLci1a6l5FbmRQvAyc7+6X5G3GpyC5UPzV3uKwNWW9SpQeVbCVIbAahu8ZVj8LbhawcH/dj7tRR5ECQQCpzLOYK7KjT92pbfrNjaPdlrPhfzxXucCY65RyUyJl98fe8tmdnjsAJoFv3TLh94RDBs8ryfMZ4Ym4ukDvi+alAkEAxGxbGs6SHmnUJkvBZwxest7XnALAddl/HjJwyXS0r7wqTomfi9JxV+AhE2vAaW4tZcQr9y3/Neek89+oFgBh1wJAdlYtS/4YT2zXxL7bLepqq4Hd92ffPBw+t9Rm7o41yO64ow6Izyp5YA914eo9DfKcgMH8HD5waDcg7lcP7mKH6QJAVDRbbgeGTnFx2CT7uTBtXGL5rVDkruDZhNl8znAwkXGp9Vc8RVWm71QO+eNkbg4keg76BhH66WHvrfiAd0YcqwJAO3/zvLOlh7qDCpGctxYEdKSrl7w9eSnCisWCL5v8mrxiFV9aK0h8N+AZayDHjYNJL4D+GqVZ7TvgHQWlJXoraw==-----END RSA PRIVATE KEY-----";
	
	string data = "123 data 123";
	
	byte[] dataEncr = RSA.encrypt(publicKey, Encoding.UTF8.GetBytes(data));
	Console.WriteLine("dataEncr base64: " + Convert.ToBase64String(dataEncr));
	
	byte[] dataDecr = RSA.decrypt(privateKey, dataEncr);
	Console.WriteLine("dataDecr: " + Encoding.UTF8.GetString(dataDecr));
 */
class RSA {
	public static byte[] encrypt(string PEMPublicKey, byte[] data) {
		byte[] encrData = null;
		
		if ((data != null) && (PEMPublicKey != null)) {
			RSACryptoServiceProvider rsa = new RSACryptoServiceProvider();
			
			string publicKeyXml = opensslkey.DecodePEMKey(PEMPublicKey);
			if (publicKeyXml != null) {
				rsa.FromXmlString(publicKeyXml);
				
				encrData = rsa.Encrypt(data, false);
			}
			else {
				Console.WriteLine("incorrect PEM key. " + PEMPublicKey);
			}
		}
		
		return encrData;
	}
	
	public static byte[] decrypt(string PEMPrivateKey, byte[] data) {
		byte[] decrData = null;
		
		if ((data != null) && (PEMPrivateKey != null)) {
			RSACryptoServiceProvider rsa = new RSACryptoServiceProvider();
			
			string privateKeyXml = opensslkey.DecodePEMKey(PEMPrivateKey);
			if (privateKeyXml != null) {
				rsa.FromXmlString(privateKeyXml);
				
				decrData = rsa.Decrypt(data, false);
			}
			else {
				Console.WriteLine("incorrect PEM key. " + PEMPrivateKey);
			}
		}
		
		return decrData;
	}
}


class Hash {
	public static string GenRandKey() {
		Random rand = new Random((int)DateTime.Now.Ticks);
		string rnd = Convert.ToString(rand.Next(1, 1000000000));
		
		return Hash.GetStringMD5(rnd);
	}
	
	public static string GetFileMD5(string path) {
		using (var md5Provider = MD5.Create()) {
			using (var stream = File.OpenRead(path)) {
				string md5 = BitConverter.ToString(md5Provider.ComputeHash(stream));
				md5 = Hash.hashNormilize(md5);
				
				return md5;
			}
		}
	}
	
	public static string GetStringMD5(string data) {
		using (var md5Provider = MD5.Create()) {
			byte[] bytes = Encoding.UTF8.GetBytes(data);
			string md5 = BitConverter.ToString(md5Provider.ComputeHash(bytes));
			md5 = Hash.hashNormilize(md5);
			
			return md5;
		}
	}
	
	public static string GetFileSHA1(string path) {
		using (var shaProvider = SHA1CryptoServiceProvider.Create()) {
			using (var stream = File.OpenRead(path)) {
				string sha = BitConverter.ToString(shaProvider.ComputeHash(stream));
				sha = Hash.hashNormilize(sha);
				
				return sha;
			}
		}
	}
	
	public static string GetStringSHA1(string data) {
		using (var shaProvider = SHA1CryptoServiceProvider.Create()) {
			byte[] bytes = Encoding.UTF8.GetBytes(data);
			string sha = BitConverter.ToString(shaProvider.ComputeHash(bytes));
			sha = Hash.hashNormilize(sha);
			
			return sha;
		}
	}
	
	private static string hashNormilize(string hash) {
		return hash.Replace("-", "").ToLower();
	}
}

