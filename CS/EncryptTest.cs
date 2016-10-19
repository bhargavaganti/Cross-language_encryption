using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace test_upload_0 {
	class EncryptTest {
		public EncryptTest() {
			/*
			 * AES
			 */
			Console.WriteLine("test AES");
			string data = "123 wert путин ест детей";
			AES_256 aes = new AES_256("ABC12345123451234512345123451234");
			byte[] encr = aes.encrypt(Encoding.UTF8.GetBytes(data));
			Console.WriteLine("encrypted base64: " + Convert.ToBase64String(encr));

			byte[] decr = aes.decrypt(encr);
			Console.WriteLine("decrypted: " + Encoding.UTF8.GetString(decr));


			/*
			 * RSA
			 */
			Console.WriteLine("test RSA");
			string publicKey = "-----BEGIN PUBLIC KEY-----MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCCSJhJ0Uws0gpESsZY4IuQbg9Xp1wks09cExmp3QEwsJNefF6c9j65n7iwGy3PRt8RwVo5kbaqliQMB6k5DLPbUD2wa+XKGlId8YrX6MHEADKy2WIVjAg0WI9ie0EG3OUJDtFqOxsD7AWTWwsF5XYKvw30cqe5JlPxpSRnXuM5kwIDAQAB-----END PUBLIC KEY-----";
			string privateKey = "-----BEGIN RSA PRIVATE KEY-----MIICWwIBAAKBgQCCSJhJ0Uws0gpESsZY4IuQbg9Xp1wks09cExmp3QEwsJNefF6c9j65n7iwGy3PRt8RwVo5kbaqliQMB6k5DLPbUD2wa+XKGlId8YrX6MHEADKy2WIVjAg0WI9ie0EG3OUJDtFqOxsD7AWTWwsF5XYKvw30cqe5JlPxpSRnXuM5kwIDAQABAoGABtGR1tszZ20eyHA5bVFjPI3mE6pYsjsIPkNppnBArbGwJNPRh9mDcuefHOhvP1fwONerxzOPIeJ1xINqIeg+SXsAzw4ElZgLci1a6l5FbmRQvAyc7+6X5G3GpyC5UPzV3uKwNWW9SpQeVbCVIbAahu8ZVj8LbhawcH/dj7tRR5ECQQCpzLOYK7KjT92pbfrNjaPdlrPhfzxXucCY65RyUyJl98fe8tmdnjsAJoFv3TLh94RDBs8ryfMZ4Ym4ukDvi+alAkEAxGxbGs6SHmnUJkvBZwxest7XnALAddl/HjJwyXS0r7wqTomfi9JxV+AhE2vAaW4tZcQr9y3/Neek89+oFgBh1wJAdlYtS/4YT2zXxL7bLepqq4Hd92ffPBw+t9Rm7o41yO64ow6Izyp5YA914eo9DfKcgMH8HD5waDcg7lcP7mKH6QJAVDRbbgeGTnFx2CT7uTBtXGL5rVDkruDZhNl8znAwkXGp9Vc8RVWm71QO+eNkbg4keg76BhH66WHvrfiAd0YcqwJAO3/zvLOlh7qDCpGctxYEdKSrl7w9eSnCisWCL5v8mrxiFV9aK0h8N+AZayDHjYNJL4D+GqVZ7TvgHQWlJXoraw==-----END RSA PRIVATE KEY-----";

			data = "123 wert путин ест детей";

			byte[] dataEncr = RSA.encrypt(publicKey, Encoding.UTF8.GetBytes(data));
			Console.WriteLine("dataEncr base64: " + Convert.ToBase64String(dataEncr));

			byte[] dataDecr = RSA.decrypt(privateKey, dataEncr);
			Console.WriteLine("dataDecr: " + Encoding.UTF8.GetString(dataDecr));
		}
	}
}
