<?php
// Creamos el mensaje asociado al tweet y al vídeo
if ( isset( $_POST['message'] ) ) {
  $message = $_POST['message'] . " por " . $_POST["nombreUsuario"];
} else {
  $message     = 'Probando la subida de vídeos con #PHP';//'Por qué me gustan las tortas de Inés Rosales #tortas #MeriendasAuténticas #MerienndasDeAntaño';
  $nombreUsuario = $_POST["nombreUsuario"];
  // Definimos la variables de para compartir
  $tags        = array("tortas", "merienda auténtica");
  $title       = ( $nombreUsuario ) ? $nombreUsuario . ' con Inés Rosales' : 'Inés Rosales' ;
  $description = $message;
}

//
foreach(array('video', 'audio') as $type) {
	if (isset($_FILES["${type}-blob"])) {
		
		$fileName 			 = $_POST["${type}-filename"];
		$uploadDirectory = dirname(__FILE__).'/uploads/'.$fileName;

		if (!move_uploaded_file($_FILES["${type}-blob"]["tmp_name"], $uploadDirectory)) {
			echo(" problem moving uploaded file");
		}// end if

		echo($uploadDirectory);
  }
}

if ( isset($nombreUsuario) ) {
  echo "Nombre: " . $nombreUsuario . "<br>Mensaje:<br>" . $_POST['message'] . "<br>";
}

if ( !isset( $uploadDirectory ) ) {
  // die('No hay video adjunto');
  $uploadDirectory = dirname(__FILE__).'/uploads/1469706044089.mp4';
}



/**
 * CONVERTER WEBM TO MP4
 *
 * ffmpeg -i SampleVideo_720x480_1mb.mp4 -ac 2 test.mp4
 */
// require_once('FFMpeg/FFMpeg.php');
// require_once('FFMpeg/FFProbe.php');
// require_once('FFMpeg/FFMpegServiceProvider.php');

// /** Creamos el objeto para manipular los medias */
// $ffmpeg = FFMpeg\FFMpeg::create();

// /** Si queremos especificar los PATHs de los binarios ffmpeg y ffprobe lo hacemos aquí */
// // $ffmpeg = FFMpeg\FFMpeg::create(array(
// //     'ffmpeg.binaries'  => '/opt/local/ffmpeg/bin/ffmpeg',
// //     'ffprobe.binaries' => '/opt/local/ffmpeg/bin/ffprobe',
// //     'timeout'          => 3600, // The timeout for the underlying process
// //     'ffmpeg.threads'   => 12,   // The number of threads that FFMpeg should use
// // ), $logger);

// /** Archivo para la marca de agua en el video */
// $watermarkPath = dirname(__FILE__).'/uploads/watermark.png';

// * Abrimos el video y lo guardamos en la variable $video 
// $video = $ffmpeg->open( $uploadDirectory );
// /** Le aplicamos una "mosca" (watermark) */
// $video
//     ->filters()
//     ->watermark( $watermarkPath, array(
//         'position' => 'relative',
//         'bottom' => 50,
//         'right' => 50,
//     ))
//     // ->resize(new FFMpeg\Coordinate\Dimension(720, 340))
//     ->synchronize();

// /** Extraemos un frame en jpg (¿de portada?) */
// // $video
// //     ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(10))
// //     ->save('frame.jpg');

// /** Y finalmente le cambiamos el formato */
// $video
//     ->save(new FFMpeg\Format\Video\X264(), 'export-' . $fileName . '.mp4');
//     // ->save(new FFMpeg\Format\Video\WMV(), 'export-wmv.wmv')
//     // ->save(new FFMpeg\Format\Video\WebM(), 'export-webm.webm');




/**
 * TWITTER CONFIGURATION
 *
 * Uploading videos to Twitter (≤ 15MB, MP4) requires you to send them in chunks.
 * You need to perform at least 3 calls to obtain your media_id for the video:
 *
 *    Send an INIT event to get a media_id draft.
 *    Upload your chunks with APPEND events, each one up to 5MB in size.
 *    Send a FINALIZE event to convert the draft to a ready-to-tweet media_id.
 *    Post your tweet with video attached.
 */

require_once ('Twitter/codebird.php');
// Variables y tokens
$consumer_key     = 'IXqfzJU9V6IyM4GTvjQ42bOtM';
$consumer_secret  = 'KKw2IMKq4qMWhCastHDk0wnG3LMrTeWNTn4U8TtSIXPIz3Ur9C';
$token_key        = '847083294-OjBTeY4aMjfsCoE2ycKCJYrbTmBOaRu7rAWcEMs1';
$token_secret     = '1Bob7q8M0pdolh8sOrBbxhnSRG74Ki3crEZrzwKMoGIwB';

\Codebird\Codebird::setConsumerKey( $consumer_key, $consumer_secret ); // static, see README

$cb = \Codebird\Codebird::getInstance();

// Llamada a la API
$cb->setToken( $token_key, $token_secret );

/** 
 * Subir vídeo
 *
 *  Deben ser en mp4, 
 *
 */
$file       = $uploadDirectory; // salida del video convertido
$size_bytes = filesize($file);
$fp         = fopen($file, 'r');

// INIT the upload

$reply = $cb->media_upload([
  'command'         => 'INIT',
  'media_type'      => 'video/mp4',
  'media_category'  => 'tweet_video',
  'total_bytes'     => $size_bytes
]);

$media_id = $reply->media_id_string;

echo "INIT:<br>";
var_dump($reply);

// APPEND data to the upload

$segment_id = 0;

while (! feof($fp)) {
  $chunk = fread($fp, 100000); // 1MB per chunk for this sample

  $reply = $cb->media_upload([
    'command'       => 'APPEND',
    'media_id'      => $media_id,
    'segment_index' => $segment_id,
    'media'         => $chunk
  ]);

  $segment_id++;
}
echo "APPEND:<br>";
var_dump($reply);

fclose($fp);

// FINALIZE the upload

$reply = $cb->media_upload([
  'command'   => 'FINALIZE',
  'media_id'  => $media_id
]);

echo "FINALIZE:<br>";
var_dump($reply);

// STATUS the upload

$reply = $cb->media_upload([
  'command'   => 'STATUS',
  'media_id'  => $media_id
]);

echo "STATUS:<br>";
var_dump($reply);

if ($reply->httpstatus < 200 || $reply->httpstatus > 299) {
  die();
}

// Usamos el media_id y el $message para tweetear
$reply = $cb->statuses_update([
  'status'    => $message,
  'media_ids' => $media_id
]);





/**
 * YOUTUBE CONFIGURATION
 *
 * You can acquire an OAuth 2.0 client ID and client secret from the
 * Google Developers Console <https://console.developers.google.com/>
 * For more information about using OAuth 2.0 to access Google APIs, please see:
 * <https://developers.google.com/youtube/v3/guides/authentication>
 * Please ensure that you have enabled the YouTube Data API for your project.
 */
// Call set_include_path() as needed to point to your client library.
set_include_path( get_include_path() . PATH_SEPARATOR . '/google-api-php-client/src');
require_once 'Youtube/vendor/autoload.php';
// require_once 'Google/Client.php';
// require_once 'Google/Service/YouTube.php';


// Tokens 
// $OAUTH2_CLIENT_ID = '646883548654-enicolam5coh9qqmt5prj20bf17r3n71.apps.googleusercontent.com
// ';
// $OAUTH2_CLIENT_SECRET = 'lmSj1WM-rSLLJu8Bh6z_aCvI';


$client = new Google_Client();
$client->setAuthConfigFile('google-api-php-client/client_secret.json');
$client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
$client->setRedirectUri('http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php');

echo "<pre>";
echo "<h1>Objeto Client</h1>";
var_dump($client);die();
echo "</pre>";


if( $client->getAccessToken() ) {
    $snippet = new Google_VideoSnippet();
    $snippet->setTitle( $title );
    $snippet->setDescription( $description );
    $snippet->setTags( $tags );
    $snippet->setCategoryId("22");

    $status = new Google_VideoStatus();
    $status->privacyStatus = "private";

    $video = new Google_Video();
    $video->setSnippet($snippet);
    $video->setStatus($status);

    $error = true;
    $i = 0;

    try {
        $obj = $youTubeService->videos->insert("status,snippet", $video,
                                         array("data"=>file_get_contents( $uploadDirectory ), 
                                        "mimeType" => "video/mp4"));
    } catch(Google_ServiceException $e) {
        print "Caught Google service Exception ".$e->getCode(). " message is ".$e->getMessage(). " <br>";
        print "Stack trace is ".$e->getTraceAsString();
    }
} else {
  $authUrl = $client->createAuthUrl();
  print "<a href='$authUrl'>Connect Me!</a>";
}
