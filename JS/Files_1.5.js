
/**
 * Lib for manipulating blob and base64 & sending AJAX requests
 * version: 1.5 (08.12.14)
 * 
 * @autor: Constantine Oupirum
 * MIT license: https://googledrive.com/host/0B2JzwD3Qc8A8QkZHMktnaExiaTg
 */
var Files = {
	/**
	 * Get Blob data by URL
	 * @param url - file URL
	 * @param onSuccess - success callback. Will be invoked with Blob object argument
	 * @param onError - error callback. Will be invoked with error message argument
	 */
	getBlob: function(url, onSuccess, onError) {
		//var fileType = {type: Files.getFileType(url)};
		Files.httpReq(url, "GET", null, "arraybuffer", function(data) {
				var blob = new Blob([data]);
				onSuccess(blob);
			}, function(e) {
				if (onError) onError(e);
			});
	},
	
	/**
	 * Convert Blob to Base64 string
	 * @param blob - Blob data object
	 * @param onSuccess - success callback. Will be invoked with Base64 string argument
	 * @param onError - error callback. Will be invoked with error message argument
	 */
	blobToBase64: function(blob, onSuccess, onError) {
		try {
			var reader = new FileReader();
			reader.readAsDataURL(blob);
			reader.onloadend = function() {
				base64Data = reader.result;
				base64Data = Files.trimBase64(base64Data);
				onSuccess(base64Data);
			};
			reader.onerror = function(e) {
				if (onError) onError(e);
			};
		} catch(err) {
			if (onError) onError(err);
		}
	},
	
	/**
	 * Convert Base64 string to Blob
	 * @param base64Data - Base64 string
	 * @param mimeType - MIME type string of file, e.g. "text/plain"
	 * @returns Blob object
	 */
	base64ToBlob: function(base64Data, mimeType) {
		if (mimeType) {
			mimeType = { type: mimeType };
		}
		
		base64Data = Files.trimBase64(base64Data);
		var sliceSize = 1024;
		var byteCharacters = atob(base64Data);
		var bytesLength = byteCharacters.length;
		var slicesCount = Math.ceil(bytesLength / sliceSize);
		var byteArrays = new Array(slicesCount);
	
		for (var sliceIndex = 0; sliceIndex < slicesCount; ++sliceIndex) {
			var begin = sliceIndex * sliceSize;
			var end = Math.min(begin + sliceSize, bytesLength);
	
			var bytes = new Array(end - begin);
			for (var offset = begin, i = 0 ; offset < end; ++i, ++offset) {
				bytes[i] = byteCharacters[offset].charCodeAt(0);
			}
			byteArrays[sliceIndex] = new Uint8Array(bytes);
		}
		
		return new Blob(byteArrays, mimeType);
	},
	
	/**
	 * Get MIME type string
	 * @param url - file URL
	 * @returns MIME string
	 */
	getFileType: function(url) {
		var req = Files.getXmlHttp();
		req.open("GET", url, false);
		req.send();
		var type = req.getResponseHeader("content-type");
		return type;	
	},
	
	/**
	 * Start downloading file
	 * @param fileUrl - file URL
	 * @param fileName - file name for saving
	 */
	saveFile: function(fileUrl, fileName) {
		if (!window.ActiveXObject) {
			var save = document.createElement('a');
			save.href = fileUrl;
			save.target = '_blank';
			save.download = fileName || fileUrl;
			
			var event = document.createEvent('Event');
			event.initEvent('click', true, true);
			save.dispatchEvent(event);
			(URL || webkitURL).revokeObjectURL(save.href);
		}
		else if (!!window.ActiveXObject && document.execCommand)	 {
			var _window = open(fileUrl, '_blank');
			_window.document.close();
			_window.document.execCommand('SaveAs', true, fileName || fileUrl);
			_window.close();
		}
	},
	
	/**
	 * Encode UTF-8 string to Base64
	 * @param utf8String - string for encoding
	 * @returns Base64 string
	 */
	base64StringEncode: function(utf8String) {
		return btoa(unescape(encodeURIComponent(utf8String)));
	},
	/**
	 * Decode Base64 to UTF-8 string
	 * @param base64String - Base64
	 * @returns UTF-8 string
	 */
	base64StringDecode: function(base64String) {
		return decodeURIComponent(escape(atob(base64String)));
	},
	
	base64: {
		/**
		 * Get Base64 by file URL
		 * @param url - file URL
		 * @param onLoad - success callback. Will be invoked with Base64 string argument
		 * @param onError - error callback. Will be invoked with error message argument
		 */
		fileUrl: function(url, onLoad, onError) {
			Files.getBlob(url, function(blob) {
					Files.blobToBase64(blob, function(base64) {
							base64 = Files.trimBase64(base64);
							onLoad(base64);
						}, onError);
				}, onError);
		},
		/**
		 * Get Base64 from input element (type=file)
		 * @param fileInputElem - input DOM object
		 * @param onLoad - success callback. Will be invoked with Base64 string argument
		 * @param onError - error callback. Will be invoked with error message argument
		 */
		fileInput: function(fileInputElem, onLoad, onError) {
			var file = fileInputElem.files[0];
			Files.blobToBase64(file, onLoad, onError);	
		},
		/**
		 * Get html video frame as Base64
		 * @param videoElem - video element DOM object
		 * @param W - frame with
		 * @param H - frame height
		 * @returns Base64 string
		 */
		videoFrame: function(videoElem, W, H) {
			var canvas = document.createElement("canvas");
			canvas.width = W;
			canvas.height = H;
			var cs = canvas.getContext("2d");
			cs.drawImage(videoElem, 0, 0, W, H);
			var base64 = canvas.toDataURL('image/png');
			
			return base64;
		}
	},
	
	trimBase64: function(base64Str) {
		return base64Str.replace(/^(.*)[,]/, '');
	},
	
	/**
	 * Send HTTP request (AJAX)
	 * @param url - request URL (string)
	 * @param method - request method (POST or GET) (string)
	 * @param postData - POST data (for post request only). FormData or string
	 * @param responseType - request response type (text, blob, arraybuffer or document) (string)
	 * @param onSuccess - success callback. Will be invoked with two args: response data & response headers object
	 * @param onError - error callback. Will be invoked with error message argument (optional)
	 * @param onUploadProgress - callback for uploading progress change. Argument - percents float number. (optional)
	 * @param onLoadProgress - callback for loading progress change. Argument - precents float number. (optional)
	 * @return XMLHttpRequest object
	 */
	httpReq: function(url, method, postData, rt, onSuccess, onError, onUploadProgress, onLoadProgress) {
    	function _onupload(e) {
			if (e && e.lengthComputable && onUploadProgress) {
				onUploadProgress((e.loaded / e.total) * 100);
			}
		}
		function _onloadprogress(e) {
			if (e && onLoadProgress) {
				onLoadProgress((e.loaded / e.total) * 100);
			}
		}
		function _onload() {
			if (request.readyState == 4) {
				if (request.status == 200) {
					if (onSuccess) {
						var resp = request.response || request.responseText;
						onSuccess(resp, Files.getHeaders(request));
					}
				}
				else {
					request.status = request.status || 0;
					request.statusText = request.statusText || "Unknown";
					_onerror(request.status + " " + request.statusText);
				}
			}
		}
		function _onerror(e) {
			console.warn("httpReq error:", e);
			if (onError) {
				var status = request.status + (request.statusText ? ("; " + request.statusText) : "");
				onError(status);
			}
		}
		
		var request = Files.getXmlHttp();
		if (request != null) {
			method = (method.toUpperCase() == "POST") ? "POST" : "GET";
			request.open(method, url, true);
			
			request.responseType = (rt == "blob")
					? "blob" : (rt == "document")
							? "document" : (rt == "arraybuffer")
									? "arraybuffer" : "text";
			
			if (request.setRequestHeader) {
				request.setRequestHeader("Access-Control-Request-Method", "POST, GET");
				request.setRequestHeader("Access-Control-Request-Headers", "x-requested-with");
				
				if (method == "POST") {
					//request.setRequestHeader("X-Requested-With", "XMLHttpRequest");
					
					// FormData:
					if (typeof(postData) == "object") {
						//request.setRequestHeader("Content-type", "multipart/form-data");
					}
					// POST data as string (&var=value):
					else if (typeof(postData) == "string") {
						request.setRequestHeader("Content-type",
								"application/x-www-form-urlencoded");
					}
				}
			}
			
			if (method != "POST") {
				postData = null;
			}
			
			if ("onload" in request) {
				request.onload = _onload;
			}
			else {
				request.onreadystatechange = _onload;
			}
			
			if (("upload" in request) && ("onprogress" in request.upload)) {
				request.upload.onprogress = _onupload;
			}
			
			if ("onprogress" in request) {
				request.onprogress = _onloadprogress;
			}
			
			request.onerror = _onerror;
			
			request.send(postData);
		}
		else {
			request = { status: 0, statusText: "xmlHttpRequest is not supported" };
			_onerror("xmlHttpRequest is not supported");
		}
		
		return request;
	},
	
	getXmlHttp: function() {
		var xmlHttp = null;
		
		xmlHttp = ( ((typeof(XMLHttpRequest) != "undefined") && new XMLHttpRequest())
				|| ( (typeof(ActiveXObject) != "undefined")
						&& (new ActiveXObject('Msxml2.XMLHTTP')
								|| new ActiveXObject('Microsoft.XMLHTTP')) )
				|| null );
		
		// Как же заебал этот IE
		
		return xmlHttp;
	},
	
	getHeaders: function(request) {
		var headers = {};
		
		if (request && request.getAllResponseHeaders) {
			var hTxt = request.getAllResponseHeaders();
			var ls = /^\s*/;
			var ts = /\s*$/;
			
			var lines = hTxt.split("\n");
			for (var i = 0; i < lines.length; i++) {
				var l = lines[i];
				if (l.lenght < 3) 
					continue;
				
				var pos = l.indexOf(":");
				var name = l.substring(0, pos).replace(ls, "").replace(ts, "");
				var value = l.substring(pos+1).replace(ls, "").replace(ts, "");
				
				headers[name] = value;
			}
		}
		else {
			console.warn("Files.getHeaders() ", "could not get response headers");
		}
		
		return headers;
	}
};


