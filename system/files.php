<?
	class FLS {

		public function randomName() { //Генерирует рандомное имя файла
			$chars = "qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP"; 
			$max = 15; 
			$size = StrLen($chars)-1; 
			$password = null; 
			while ($max--) $password.=$chars[rand(0,$size)]; 
			return $password;
		}
		
		public function uploadFile($src,$type,$compress=false) { //Загрузить файл
		    if ($compress) { // Если нужна компрессия и это изображение
		        if (in_array($type,Array('image/png','image/jpeg','image/jpeg','image/jpeg','image/gif','image/bmp','image/vnd.microsoft.icon','image/tiff','image/tiff','image/svg+xml'))) {
    		        
    		        $finf = getimagesize($src); //Собираем инфу о файле и генерируем новое имя
			        $path = $this->getUploadFold($type);
			        $ext = $this->mime2ext($type);
			        $name = $this->randomName().".".$ext;
			        while (is_file($_SERVER["DOCUMENT_ROOT"].$path."compressed/".$name)) {
			        	$name = $this->randomName().".".$ext;
			        };

    		       $this->imgСonvert($src, $_SERVER["DOCUMENT_ROOT"].$path."compressed/".$name, $finf[0], $finf[1], $rgb=0xFFFFFF, $quality=100); // Переносим файл на сервер
    		       return $path."compressed/".$name;
		        } else {
		        	return false;
		        };
		    };

		    // Без сжатия
	        $path = $this->getUploadFold($type);
	        $ext = $this->mime2ext($type);
	        $name = $this->randomName().".".$ext;
	        while (is_file($_SERVER["DOCUMENT_ROOT"].$path.$name)) {
	        	$name = $this->randomName().".".$ext;
	        };

	        if (move_uploaded_file($src, $_SERVER["DOCUMENT_ROOT"].$path.$name)) {
	        	return $path.$name;
	        } else {
	        	return false;
	        };
		}

		public function imgСonvert($src, $dest, $width, $height, $rgb=0xFFFFFF, $quality=100) { //Конвертирует изображение 
			if (!file_exists($src)) return false;
			
			$size = getimagesize($src);
			 
			if ($size === false) return false;
			 
			// Определяем исходный формат по MIME-информации, предоставленной
			// функцией getimagesize, и выбираем соответствующую формату
			// imagecreatefrom-функцию.
			$format = strtolower(substr($size['mime'], strpos($size['mime'], '/')+1));
			$icfunc = "imagecreatefrom" . $format;
			if (!function_exists($icfunc)) return false;
			 
			$x_ratio = $width / $size[0];
			$y_ratio = $height / $size[1];
			 
			$ratio = min($x_ratio, $y_ratio);
			$use_x_ratio = ($x_ratio == $ratio);
			 
			$new_width = $use_x_ratio  ? $width  : floor($size[0] * $ratio);
			$new_height = !$use_x_ratio ? $height : floor($size[1] * $ratio);
			$new_left = $use_x_ratio  ? 0 : floor(($width - $new_width) / 2);
			$new_top = !$use_x_ratio ? 0 : floor(($height - $new_height) / 2);
			 
			$isrc = $icfunc($src);
			$idest = imagecreatetruecolor($width, $height);
			 
			imagefill($idest, 0, 0, $rgb);
			imagecopyresampled($idest, $isrc, $new_left, $new_top, 0, 0,
			$new_width, $new_height, $size[0], $size[1]);
			 
			imagejpeg($idest, $dest, $quality);
			 
			imagedestroy($isrc);
			imagedestroy($idest);
			 
			return true;
		}

		function getUploadFold($mime) { // Получить папку для загрузки
			$fold = "/addict/files/other/";
			if (in_array($mime,Array(
				'image/bmp',
		        'image/x-bmp',
		        'image/x-bitmap',
		        'image/x-xbitmap',
		        'image/x-win-bitmap',
		        'image/x-windows-bmp',
		        'image/ms-bmp',
		        'image/x-ms-bmp',
		        'image/cdr',
		        'image/x-cdr',
		        'image/gif',
		        'image/x-icon',
		        'image/x-ico',
		        'image/vnd.microsoft.icon',
		        'image/jp2',
		        'image/jpx',
		        'image/jpm',
		        'image/jpeg',
		        'image/pjpeg',
		        'image/png',
		        'image/x-png',
		        'image/vnd.adobe.photoshop',
		        'image/svg+xml',
		        'image/tiff'
			))) { $fold = "/addict/files/image/"; };
			
			if (in_array($mime,Array(
				'video/3gpp2',
		        'video/3gp',
		        'video/3gpp',
		        'video/x-msvideo',
		        'video/msvideo',
		        'video/avi',
		        'video/x-f4v',
		        'video/x-flv',
		        'video/mj2',
		        'video/quicktime',
		        'video/x-sgi-movie',
		        'video/mp4',
		        'video/mpeg',
		        'video/ogg',
				'video/webm',
		        'video/x-ms-wmv',
		        'video/x-ms-asf'
			))) { $fold = "/addict/files/video/"; };

			if (in_array($mime,Array(
		        'audio/x-acc',
		        'audio/ac3',
		        'audio/x-aiff',
		        'audio/aiff',
		        'audio/x-au',
		        'audio/x-flac',
		        'audio/x-m4a',
		        'audio/midi',
		        'audio/mpeg',
		        'audio/mpg',
		        'audio/mpeg3',
		        'audio/mp3',
		        'audio/ogg',
		        'audio/x-realaudio',
		        'audio/x-pn-realaudio',
		        'audio/x-pn-realaudio-plugin',
		        'audio/x-wav',
		        'audio/wave',
		        'audio/wav',
		        'audio/x-ms-wma',
			))) { $fold = "/addict/files/audio/"; };

			if (in_array($mime,Array(
		        'application/x-compressed',
		        'application/postscript',
		        'application/x-troff-msvideo',
		        'application/macbinary',
		        'application/mac-binary',
		        'application/x-binary',
		        'application/x-macbinary',
		        'application/bmp',
		        'application/x-bmp',
		        'application/x-win-bitmap',
		        'application/cdr',
		        'application/coreldraw',
		        'application/x-cdr',
		        'application/x-coreldraw',
		        'zz-application/zz-winassoc-cdr',
		        'application/mac-compactpro',
		        'application/pkix-crl',
		        'application/pkcs-crl',
		        'application/x-x509-ca-cert',
		        'application/pkix-cert',
		        'application/vnd.msexcel',
		        'application/x-director' ,
		        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		        'application/x-dvi',
		        'application/x-msdownload',
		        'application/gpg-keys',
		        'application/x-gtar',
		        'application/x-gzip',
		        'application/mac-binhex40',
		        'application/mac-binhex',
		        'application/x-binhex40',
		        'application/x-mac-binhex40',
		        'application/java-archive',
		        'application/x-java-application',
		        'application/x-jar',
		        'application/x-javascript',
		        'application/json',
		        'application/vnd.google-earth.kml+xml',
		        'application/vnd.google-earth.kmz',
		        'application/vnd.mpegurl',
		        'application/vnd.mif',
		        'application/oda',
		        'application/ogg',
		        'application/x-pkcs10',
		        'application/pkcs10',
		        'application/x-pkcs12',
		        'application/x-pkcs7-signature',
		        'application/pkcs7-mime',
		        'application/x-pkcs7-mime',
		        'application/x-pkcs7-certreqresp',
		        'application/pkcs7-signature',
		        'application/pdf',
		        'application/octet-stream',
		        'application/x-x509-user-cert',
		        'application/x-pem-file',
		        'application/pgp',
		        'application/x-httpd-php',
		        'application/php',
		        'application/x-php',
		        'application/x-httpd-php-source',
		        'application/powerpoint',
		        'application/vnd.ms-powerpoint',
		        'application/vnd.ms-office',
		        'application/msword',
		        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		        'application/x-photoshop',
		        'application/x-rar',
		        'application/rar',
		        'application/x-rar-compressed',
		        'application/x-pkcs7',
		        'application/x-stuffit',
		        'application/smil',
		        'application/x-shockwave-flash',
		        'application/x-tar',
		        'application/x-gzip-compressed',
		        'application/videolan',
		        'application/wbxml',
		        'application/wmlc',
		        'application/xhtml+xml',
		        'application/excel',
		        'application/msexcel',
		        'application/x-msexcel',
		        'application/x-ms-excel',
		        'application/x-excel',
		        'application/x-dos_ms_excel',
		        'application/xls',
		        'application/x-xls',
		        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		        'application/vnd.ms-excel',
		        'application/xml',
		        'application/xspf+xml',
		        'application/x-compress',
		        'application/x-zip',
		        'application/zip',
		        'application/x-zip-compressed',
		        'application/s-compressed'
			))) { $fold = "/addict/files/docs/"; };

			if (in_array($mime,Array(
		        'text/css',
		        'text/x-comma-separated-values',
		        'text/comma-separated-values',
		        'text/html',
		        'text/calendar',
		        'text/json',
		        'text/x-log',
		        'text/rtf',
		        'text/richtext',
		        'text/srt',
		        'text/plain',
		        'text/x-vcard',
		        'text/vtt',
		        'text/xml',
		        'text/xsl',
		        'text/x-scriptzsh'
			))) { $fold = "/addict/files/text/"; };

			return $fold;
		}

		function mime2ext($mime) { //Получить расширение файла
		    $mime_map = [
		        'video/3gpp2'                                                               => '3g2',
		        'video/3gp'                                                                 => '3gp',
		        'video/3gpp'                                                                => '3gp',
		        'application/x-compressed'                                                  => '7zip',
		        'audio/x-acc'                                                               => 'aac',
		        'audio/ac3'                                                                 => 'ac3',
		        'application/postscript'                                                    => 'ai',
		        'audio/x-aiff'                                                              => 'aif',
		        'audio/aiff'                                                                => 'aif',
		        'audio/x-au'                                                                => 'au',
		        'video/x-msvideo'                                                           => 'avi',
		        'video/msvideo'                                                             => 'avi',
		        'video/avi'                                                                 => 'avi',
		        'application/x-troff-msvideo'                                               => 'avi',
		        'application/macbinary'                                                     => 'bin',
		        'application/mac-binary'                                                    => 'bin',
		        'application/x-binary'                                                      => 'bin',
		        'application/x-macbinary'                                                   => 'bin',
		        'image/bmp'                                                                 => 'bmp',
		        'image/x-bmp'                                                               => 'bmp',
		        'image/x-bitmap'                                                            => 'bmp',
		        'image/x-xbitmap'                                                           => 'bmp',
		        'image/x-win-bitmap'                                                        => 'bmp',
		        'image/x-windows-bmp'                                                       => 'bmp',
		        'image/ms-bmp'                                                              => 'bmp',
		        'image/x-ms-bmp'                                                            => 'bmp',
		        'application/bmp'                                                           => 'bmp',
		        'application/x-bmp'                                                         => 'bmp',
		        'application/x-win-bitmap'                                                  => 'bmp',
		        'application/cdr'                                                           => 'cdr',
		        'application/coreldraw'                                                     => 'cdr',
		        'application/x-cdr'                                                         => 'cdr',
		        'application/x-coreldraw'                                                   => 'cdr',
		        'image/cdr'                                                                 => 'cdr',
		        'image/x-cdr'                                                               => 'cdr',
		        'zz-application/zz-winassoc-cdr'                                            => 'cdr',
		        'application/mac-compactpro'                                                => 'cpt',
		        'application/pkix-crl'                                                      => 'crl',
		        'application/pkcs-crl'                                                      => 'crl',
		        'application/x-x509-ca-cert'                                                => 'crt',
		        'application/pkix-cert'                                                     => 'crt',
		        'text/css'                                                                  => 'css',
		        'text/x-comma-separated-values'                                             => 'csv',
		        'text/comma-separated-values'                                               => 'csv',
		        'application/vnd.msexcel'                                                   => 'csv',
		        'application/x-director'                                                    => 'dcr',
		        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'docx',
		        'application/x-dvi'                                                         => 'dvi',
		        'message/rfc822'                                                            => 'eml',
		        'application/x-msdownload'                                                  => 'exe',
		        'video/x-f4v'                                                               => 'f4v',
		        'audio/x-flac'                                                              => 'flac',
		        'video/x-flv'                                                               => 'flv',
		        'image/gif'                                                                 => 'gif',
		        'application/gpg-keys'                                                      => 'gpg',
		        'application/x-gtar'                                                        => 'gtar',
		        'application/x-gzip'                                                        => 'gzip',
		        'application/mac-binhex40'                                                  => 'hqx',
		        'application/mac-binhex'                                                    => 'hqx',
		        'application/x-binhex40'                                                    => 'hqx',
		        'application/x-mac-binhex40'                                                => 'hqx',
		        'text/html'                                                                 => 'html',
		        'image/x-icon'                                                              => 'ico',
		        'image/x-ico'                                                               => 'ico',
		        'image/vnd.microsoft.icon'                                                  => 'ico',
		        'text/calendar'                                                             => 'ics',
		        'application/java-archive'                                                  => 'jar',
		        'application/x-java-application'                                            => 'jar',
		        'application/x-jar'                                                         => 'jar',
		        'image/jp2'                                                                 => 'jp2',
		        'video/mj2'                                                                 => 'jp2',
		        'image/jpx'                                                                 => 'jp2',
		        'image/jpm'                                                                 => 'jp2',
		        'image/jpeg'                                                                => 'jpeg',
		        'image/pjpeg'                                                               => 'jpeg',
		        'application/x-javascript'                                                  => 'js',
		        'application/json'                                                          => 'json',
		        'text/json'                                                                 => 'json',
		        'application/vnd.google-earth.kml+xml'                                      => 'kml',
		        'application/vnd.google-earth.kmz'                                          => 'kmz',
		        'text/x-log'                                                                => 'log',
		        'audio/x-m4a'                                                               => 'm4a',
		        'application/vnd.mpegurl'                                                   => 'm4u',
		        'audio/midi'                                                                => 'mid',
		        'application/vnd.mif'                                                       => 'mif',
		        'video/quicktime'                                                           => 'mov',
		        'video/x-sgi-movie'                                                         => 'movie',
		        'audio/mpeg'                                                                => 'mp3',
		        'audio/mpg'                                                                 => 'mp3',
		        'audio/mpeg3'                                                               => 'mp3',
		        'audio/mp3'                                                                 => 'mp3',
		        'video/mp4'                                                                 => 'mp4',
		        'video/mpeg'                                                                => 'mpeg',
		        'application/oda'                                                           => 'oda',
		        'audio/ogg'                                                                 => 'ogg',
		        'video/ogg'                                                                 => 'ogg',
		        'application/ogg'                                                           => 'ogg',
		        'application/x-pkcs10'                                                      => 'p10',
		        'application/pkcs10'                                                        => 'p10',
		        'application/x-pkcs12'                                                      => 'p12',
		        'application/x-pkcs7-signature'                                             => 'p7a',
		        'application/pkcs7-mime'                                                    => 'p7c',
		        'application/x-pkcs7-mime'                                                  => 'p7c',
		        'application/x-pkcs7-certreqresp'                                           => 'p7r',
		        'application/pkcs7-signature'                                               => 'p7s',
		        'application/pdf'                                                           => 'pdf',
		        'application/octet-stream'                                                  => 'pdf',
		        'application/x-x509-user-cert'                                              => 'pem',
		        'application/x-pem-file'                                                    => 'pem',
		        'application/pgp'                                                           => 'pgp',
		        'application/x-httpd-php'                                                   => 'php',
		        'application/php'                                                           => 'php',
		        'application/x-php'                                                         => 'php',
		        'text/php'                                                                  => 'php',
		        'text/x-php'                                                                => 'php',
		        'application/x-httpd-php-source'                                            => 'php',
		        'image/png'                                                                 => 'png',
		        'image/x-png'                                                               => 'png',
		        'application/powerpoint'                                                    => 'ppt',
		        'application/vnd.ms-powerpoint'                                             => 'ppt',
		        'application/vnd.ms-office'                                                 => 'ppt',
		        'application/msword'                                                        => 'ppt',
		        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
		        'application/x-photoshop'                                                   => 'psd',
		        'image/vnd.adobe.photoshop'                                                 => 'psd',
		        'audio/x-realaudio'                                                         => 'ra',
		        'audio/x-pn-realaudio'                                                      => 'ram',
		        'application/x-rar'                                                         => 'rar',
		        'application/rar'                                                           => 'rar',
		        'application/x-rar-compressed'                                              => 'rar',
		        'audio/x-pn-realaudio-plugin'                                               => 'rpm',
		        'application/x-pkcs7'                                                       => 'rsa',
		        'text/rtf'                                                                  => 'rtf',
		        'text/richtext'                                                             => 'rtx',
		        'video/vnd.rn-realvideo'                                                    => 'rv',
		        'application/x-stuffit'                                                     => 'sit',
		        'application/smil'                                                          => 'smil',
		        'text/srt'                                                                  => 'srt',
		        'image/svg+xml'                                                             => 'svg',
		        'application/x-shockwave-flash'                                             => 'swf',
		        'application/x-tar'                                                         => 'tar',
		        'application/x-gzip-compressed'                                             => 'tgz',
		        'image/tiff'                                                                => 'tiff',
		        'text/plain'                                                                => 'txt',
		        'text/x-vcard'                                                              => 'vcf',
		        'application/videolan'                                                      => 'vlc',
		        'text/vtt'                                                                  => 'vtt',
		        'audio/x-wav'                                                               => 'wav',
		        'audio/wave'                                                                => 'wav',
		        'audio/wav'                                                                 => 'wav',
		        'application/wbxml'                                                         => 'wbxml',
		        'video/webm'                                                                => 'webm',
		        'audio/x-ms-wma'                                                            => 'wma',
		        'application/wmlc'                                                          => 'wmlc',
		        'video/x-ms-wmv'                                                            => 'wmv',
		        'video/x-ms-asf'                                                            => 'wmv',
		        'application/xhtml+xml'                                                     => 'xhtml',
		        'application/excel'                                                         => 'xl',
		        'application/msexcel'                                                       => 'xls',
		        'application/x-msexcel'                                                     => 'xls',
		        'application/x-ms-excel'                                                    => 'xls',
		        'application/x-excel'                                                       => 'xls',
		        'application/x-dos_ms_excel'                                                => 'xls',
		        'application/xls'                                                           => 'xls',
		        'application/x-xls'                                                         => 'xls',
		        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'         => 'xlsx',
		        'application/vnd.ms-excel'                                                  => 'xlsx',
		        'application/xml'                                                           => 'xml',
		        'text/xml'                                                                  => 'xml',
		        'text/xsl'                                                                  => 'xsl',
		        'application/xspf+xml'                                                      => 'xspf',
		        'application/x-compress'                                                    => 'z',
		        'application/x-zip'                                                         => 'zip',
		        'application/zip'                                                           => 'zip',
		        'application/x-zip-compressed'                                              => 'zip',
		        'application/s-compressed'                                                  => 'zip',
		        'multipart/x-zip'                                                           => 'zip',
		        'text/x-scriptzsh'                                                          => 'zsh',
		    ];

		    return isset($mime_map[$mime]) ? $mime_map[$mime] : false;
		}

	};

	$FLS = new FLS; //Внешний
?>