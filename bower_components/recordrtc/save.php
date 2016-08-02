<?php
// Extremos las variables que nos llegan por AJAX
$nombreUsuario = ( isset($_POST["nombreUsuario"]) ) ? $_POST["nombreUsuario"] : ' ' ;
$message       = ( isset($_POST['message']) ) ? $_POST['message'] : 'Vídeo subido por ' . $nombreUsuario;

// Configuración de variables por usuario para Youtube
$title       = '¡' . $nombreUsuario . ' ha compartido un vídeo con nosotros!';
$tags        = array("PHP", "Castilla la Mancha");
$category    = "22";
$privacy     = "public";//"private";


// Save video
foreach(array('video', 'audio') as $type) {
	if (isset($_FILES["${type}-blob"])) {

		$fileName 			 = $_POST["${type}-filename"];
		$uploadDirectory = dirname(__FILE__).'/uploads/' . $fileName;
    $nombreArchivo   = substr($fileName, 0, -5).'-subido';

		if (!move_uploaded_file($_FILES["${type}-blob"]["tmp_name"], $uploadDirectory)) {
			echo(" problem moving uploaded file");
		}// end if

		echo($uploadDirectory);
  }
}


/**
 * CONVERTER WEBM TO MP4
 *
 * ffmpeg -i SampleVideo_720x480_1mb.mp4 -ac 2 test.mp4
 *
 * Muaz Khan         - www.MuazKhan.com
 * MIT License       - www.WebRTC-Experiment.com/licence
 * Documentation     - github.com/muaz-khan/WebRTC-Experiment/tree/master/RecordRTC
 *   
 * make sure that you're using newest ffmpeg version!
 *
 * because we've different ffmpeg commands for windows & linux
 * that's why following script is used to fetch target OS
 */
$OSList = array
    (
    'Windows 3.11' => 'Win16',
    'Windows 95' => '(Windows 95)|(Win95)|(Windows_95)',
    'Windows 98' => '(Windows 98)|(Win98)',
    'Windows 2000' => '(Windows NT 5.0)|(Windows 2000)',
    'Windows XP' => '(Windows NT 5.1)|(Windows XP)',
    'Windows Server 2003' => '(Windows NT 5.2)',
    'Windows Vista' => '(Windows NT 6.0)',
    'Windows 7' => '(Windows NT 7.0)',
    'Windows NT 4.0' => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)',
    'Windows ME' => 'Windows ME',
    'Open BSD' => 'OpenBSD',
    'Sun OS' => 'SunOS',
    'Linux' => '(Linux)|(X11)',
    'Mac OS' => '(Mac_PowerPC)|(Macintosh)',
    'QNX' => 'QNX',
    'BeOS' => 'BeOS',
    'OS/2' => 'OS/2',
    'Search Bot'=>'(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp)|(MSNBot)|(Ask Jeeves/Teoma)|(ia_archiver)'
    );

// Loop through the array of user agents and matching operating systems
foreach($OSList as $CurrOS=>$Match) {
  // Find a match
  if (preg_match("/".$Match."/i", $_SERVER['HTTP_USER_AGENT'])) {
    // We found the correct match
    break;
  }
}// end foreach

// if it is audio-blob
if ( isset( $uploadDirectory ) ) {
  $uploadDirectory = $uploadDirectory;//dirname(__FILE__).'uploads/'.$_POST["filename"].'-merged.webm';

  $archivoSalida = dirname(__FILE__) . '/uploads/' . $nombreArchivo . '.mp4';

  // ffmpeg depends on yasm
  // libvpx depends on libvorbis
  // libvorbis depends on libogg
  // make sure that you're using newest ffmpeg version!

  if(!strrpos($CurrOS, "Windows")) {
    // $cmd = '-i '.$audioFile.' -i '.$videoFile.' -map 0:0 -map 1:0 '.$uploadDirectory;
    $cmd = '-i ' . $uploadDirectory . ' -c:v libx264 ' . $archivoSalida;
    // $cmd = 'ffmpeg -version';
  }
  else {
    $cmd = '-i '.$uploadDirectory.' -c:v mpeg4 -c:a vorbis -b:v 64k -b:a 12k -strict experimental '.$uploadDirectory;
  }

  exec('C:\\FFMPEG\\bin\\ffmpeg.exe '.$cmd.' 2>&1', $out, $ret);

  if ($ret){
    echo "<h3>Houston, tenemos un problema!</h3><br>";
    echo "Comando cmd ejecutado:<br>";
    print_r($cmd.'<br>');
    echo "<br>Salida:<br>";
    var_dump($out);

  } else {
    echo "Ffmpeg ha cambiado la extensión del vídeo!<br>";

    // Eliminamos el video almacenado    
    unlink($uploadDirectory);
  }
} else {
  die('No hay archivo que valga');
}


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
$file       = $archivoSalida; // salida del video convertido
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

// echo "INIT:<br>";
// var_dump($reply);

// APPEND data to the upload

$segment_id = 0;

while (! feof($fp)) {
  $chunk = fread($fp, 1048576); // 1MB per chunk for this sample

  $reply = $cb->media_upload([
    'command'       => 'APPEND',
    'media_type'    => 'video/mp4',
    'media_id'      => $media_id,
    'segment_index' => $segment_id,
    'media'         => $chunk
  ]);

  $segment_id++;
}
// echo "APPEND:<br>";
// var_dump($reply);

fclose($fp);

// FINALIZE the upload

$reply = $cb->media_upload([
  'command'   => 'FINALIZE',
  'media_type'=> 'video/mp4',
  'media_id'  => $media_id
]);

// echo "FINALIZE:<br>";
// var_dump($reply);

if ($reply->httpstatus < 200 || $reply->httpstatus > 299) {
  echo "Error de estado: " . $reply->httpstatus;
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
require_once 'Youtube/vendor/autoload.php';

session_start();

$OAUTH2_CLIENT_ID = '903819126407-9f1dqcjn0g2gu6lsojj5csuilhhbckvh.apps.googleusercontent.com';
$OAUTH2_CLIENT_SECRET = 'hIPObSOg2_kW1VlMrRydPVbU';

$client = new Google_Client();
$client->setClientId($OAUTH2_CLIENT_ID);
$client->setClientSecret($OAUTH2_CLIENT_SECRET);
$client->setScopes('https://www.googleapis.com/auth/youtube');
$redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
    FILTER_SANITIZE_URL);
$client->setRedirectUri($redirect);

// Define an object that will be used to make all API requests.
$youtube = new Google_Service_YouTube($client);

if (isset($_GET['code'])) {
  if (strval($_SESSION['state']) !== strval($_GET['state'])) {
    die('The session state did not match.');
  }

  $client->authenticate($_GET['code']);
  $_SESSION['token'] = $client->getAccessToken();
  header('Location: ' . $redirect);
}

if (isset($_SESSION['token'])) {
  $client->setAccessToken($_SESSION['token']);
}

// Check to ensure that the access token was successfully acquired.
if ($client->getAccessToken()) {
  try{
    // REPLACE this value with the path to the file you are uploading.
    $videoPath = $archivoSalida;

    // Create a snippet with title, message, tags and category ID
    // Create an asset resource and set its snippet metadata and type.
    // This example sets the video's title, message, keyword tags, and
    // video category.
    $snippet = new Google_Service_YouTube_VideoSnippet();
    $snippet->setTitle( $title );
    $snippet->setDescription( $message );
    $snippet->setTags( $tags );

    // Numeric video category. See
    // https://developers.google.com/youtube/v3/docs/videoCategories/list 
    $snippet->setCategoryId( $category );

    // Set the video's status to "public". Valid statuses are "public",
    // "private" and "unlisted".
    $status = new Google_Service_YouTube_VideoStatus();
    $status->privacyStatus = $privacy;

    // Associate the snippet and status objects with a new video resource.
    $video = new Google_Service_YouTube_Video();
    $video->setSnippet($snippet);
    $video->setStatus($status);

    // Specify the size of each chunk of data, in bytes. Set a higher value for
    // reliable connection as fewer chunks lead to faster uploads. Set a lower
    // value for better recovery on less reliable connections.
    $chunkSizeBytes = 1 * 1024 * 1024;

    // Setting the defer flag to true tells the client to return a request which can be called
    // with ->execute(); instead of making the API call immediately.
    $client->setDefer(true);

    // Create a request for the API's videos.insert method to create and upload the video.
    $insertRequest = $youtube->videos->insert("status,snippet", $video);

    // Create a MediaFileUpload object for resumable uploads.
    $media = new Google_Http_MediaFileUpload(
        $client,
        $insertRequest,
        'video/*',
        null,
        true,
        $chunkSizeBytes
    );
    $media->setFileSize(filesize($videoPath));


    // Read the media file and upload it chunk by chunk.
    $status = false;
    $handle = fopen($videoPath, "rb");
    while (!$status && !feof($handle)) {
      $chunk = fread($handle, $chunkSizeBytes);
      $status = $media->nextChunk($chunk);
    }

    fclose($handle);

    // If you want to make other calls after the file upload, set setDefer back to false
    $client->setDefer(false);


    $htmlBody .= "<h3>Video Uploaded</h3><ul>";
    $htmlBody .= sprintf('<li>%s (%s)</li>',
        $status['snippet']['title'],
        $status['id']);

    $htmlBody .= '</ul>';

  } catch (Google_Service_Exception $e) {
    $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
  } catch (Google_Exception $e) {
    $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
  }

  $_SESSION['token'] = $client->getAccessToken();
} else {
  // If the user hasn't authorized the app, initiate the OAuth flow
  $state = mt_rand();
  $client->setState($state);
  $_SESSION['state'] = $state;

  $authUrl = $client->createAuthUrl();
  $htmlBody = <<<END
  <h3>Authorization Required</h3>
  <p>You need to <a href="$authUrl">authorize access</a> before proceeding.<p>
END;
}
?>

<!doctype html>
<html>
  <head>
    <title>Video Uploaded</title>
  </head>
  <body>
    <?=$htmlBody?>
  </body>
</html>