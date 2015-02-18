package com.test.fullscreen;

import android.annotation.TargetApi;
import android.os.Build;
import android.util.Log;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.nio.charset.Charset;
import javax.crypto.Cipher;
import javax.crypto.spec.SecretKeySpec;

/** 
 * AES encryption/decryption data in Android
 * 
 * @author Constantine Oupirum 
 */
@TargetApi(Build.VERSION_CODES.GINGERBREAD)
class AES_128 {
	private String key = "huitka";
	
	/** 
	 * @param key - 32-characters string
	 */
	public AES_128(String key) {
		this.key = key;
	}
	
	/** 
	 * Encryption bytes array
	 */
	public byte[] encryptBytes(byte[] sourceData) {
		try {
			byte[] raw = key.getBytes(Charset.forName("UTF-8"));
			SecretKeySpec skeySpec = new SecretKeySpec(raw, "AES");
			Cipher cipher = Cipher.getInstance("AES");
			cipher.init(Cipher.ENCRYPT_MODE, skeySpec);

			byte[] encrypted = cipher.doFinal(sourceData);

			return encrypted;
		} catch (Exception e) {
			e.printStackTrace();
			return null;
		}
	}
	
	/** 
	 * Decryption bytes array. 
	 * */
	public byte[] decryptBytes(byte[] encryptedData) {
		try {
			byte[] raw = key.getBytes(Charset.forName("UTF-8"));
			SecretKeySpec skeySpec = new SecretKeySpec(raw, "AES");
			Cipher cipher = Cipher.getInstance("AES");
			cipher.init(Cipher.DECRYPT_MODE, skeySpec);

			byte[] decrypted = cipher.doFinal(encryptedData);

			return decrypted;
		} catch (Exception e) {
			e.printStackTrace();
			return null;
		}
	}
	
	
	/** 
	 * Encryption file. 
	 * @param path - absolute or relative path to source file. 
	 * @return byte array on success, NULL on error. 
	 * */
	public byte[] encryptFile(String path) {
		return encryptF(path, false, null);
	}
	/** 
	 * Encryption file. 
	 * @param path - absolute or relative path to source source file. 
	 * @param rewrite - set true for saving encrypted data into same file. 
	 * @return byte array on success, NULL on error. 
	 * */
	public byte[] encryptFile(String path, boolean rewrite) {
		return encryptF(path, rewrite, null);
	}
	/** 
	 * Encryption file. 
	 * @param pathSource - absolute or relative path to source file. 
	 * @param pathTarget - absolute or relative path to target file. Encrypted data will be saved into this file. 
	 * @return byte array on success, NULL on error. 
	 * */
	public byte[] encryptFile(String pathSource, String pathTarget) {
		return encryptF(pathSource, false, pathTarget);
	}
	
	private byte[] encryptF(String pathSource, boolean rewrite, String pathTarget) {
		byte[] encrypted = null;
		
		byte[] data = getFileBytes(pathSource);
		if (data != null) {
			encrypted = encryptBytes(data);
			
			if (rewrite) {
				boolean saved = saveFileBytes(encrypted, pathSource);
				if (!saved) {
					encrypted = null;
				}
			}
			else if (pathTarget != null) {
				boolean saved = saveFileBytes(encrypted, pathTarget);
				if (!saved) {
					encrypted = null;
				}
			}
		}
		
		return encrypted;
	}
	
	/** 
	 * Decryption file. 
	 * @param path - absolute or relative path to source file. 
	 * @return byte array on success, NULL on error. 
	 * */
	public byte[] decryptFile(String path) {
		return decryptF(path, false, null);
	}
	/** 
	 * Decryption file. 
	 * @param path - absolute or relative path to source source file. 
	 * @param rewrite - set true for saving decrypted data into same file. 
	 * @return byte array on success, NULL on error. 
	 * */
	public byte[] decryptFile(String path, boolean rewrite) {
		return decryptF(path, rewrite, null);
	}
	/** 
	 * Decryption file. 
	 * @param pathSource - absolute or relative path to source file. 
	 * @param pathTarget - absolute or relative path to target file. Decrypted data will be saved into this file. 
	 * @return byte array on success, NULL on error. 
	 * */
	public byte[] decryptFile(String pathSource, String pathTarget) {
		return decryptF(pathSource, false, pathTarget);
	}
	
	private byte[] decryptF(String pathSource, boolean rewrite, String pathTarget) {
		byte[] decrypted = null;
		
		byte[] data = getFileBytes(pathSource);
		if (data != null) {
			decrypted = decryptBytes(data);
			
			if (rewrite) {
				boolean saved = saveFileBytes(decrypted, pathSource);
				if (!saved) {
					decrypted = null;
				}
			}
			else if (pathTarget != null) {
				boolean saved = saveFileBytes(decrypted, pathTarget);
				if (!saved) {
					decrypted = null;
				}
			}
		}
		
		return decrypted;
	}
	
	
	
	public static boolean saveFileBytes(byte[] bytes, String path) {
		boolean res = false;
		
		FileOutputStream outStream = null;
		try {
			outStream = new FileOutputStream(path);
			outStream.write(bytes);
			outStream.flush();
			
			if ((new File(path)).isFile()) {
				res = true;
			}
		} catch(Exception e) {
			Log.e("saveFileBytes", e.getMessage() + "");
		} finally {
			try {
				outStream.close();
			} catch (IOException e) {}
		}
		
		return res;
	}
	
	public static byte[] getFileBytes(String path) {
		byte[] content = null;
		
		File f = new File(path);
		
		if ((f.exists()) && f.isFile() && f.canRead()) {
			InputStream inputStream = null;
			try {
				int length = (int) f.length();
				inputStream = new FileInputStream(path);
				byte[] buffer = new byte[length];
				int d = inputStream.read(buffer);
				
				content = buffer;
			} catch(Exception e) {
				Log.e("getFileContent", e.getMessage() + "");
			} finally {
				try {
					inputStream.close();
				} catch (IOException e) {}
			}
		}
		
		return content;
	}
}


