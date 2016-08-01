
<!--
> Muaz Khan     - www.MuazKhan.com
> MIT License   - www.WebRTC-Experiment.com/licence
> Documentation - github.com/muaz-khan/RecordRTC
> and           - RecordRTC.org
-->
<!DOCTYPE html>
<html lang="es">

<head>
    <title>Comparte tu video</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <link rel="author" type="text/html" href="https://plus.google.com/+MuazKhan">
    <meta name="author" content="Muaz Khan">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    
    <link rel="stylesheet" href="https://cdn.webrtc-experiment.com/style.css">

    <style>
        audio {
            vertical-align: bottom;
            width: 10em;
        }
        video {
            max-width: 100%;
            vertical-align: top;
        }
        input {
            border: 1px solid #d9d9d9;
            border-radius: 1px;
            font-size: 2em;
            margin: .2em;
            width: 30%;
        }
        p,
        .inner {
            padding: 1em;
        }
        li {
            border-bottom: 1px solid rgb(189, 189, 189);
            border-left: 1px solid rgb(189, 189, 189);
            padding: .5em;
        }
        label {
            display: inline-block;
            width: 8em;
        }
    </style>
    
    <style>
        .recordrtc button {
            font-size: inherit;
        }
        
        .recordrtc button, .recordrtc select {
            vertical-align: middle;
            line-height: 1;
            padding: 2px 5px;
            height: auto;
            font-size: inherit;
            margin: 0;
        }
        
        .recordrtc, .recordrtc .header {
            display: block;
            text-align: center;
            padding-top: 0;
        }
        
        .recordrtc video {
            width: 70%;
        }
        
        .recordrtc option[disabled] {
            display: none;
        }
        .hidden {
            display: none;
        }
    </style>
    
    <script src="RecordRTC.js"></script>
    <script src="libs/gif-recorder.js"></script>
    <script src="https://cdn.webrtc-experiment.com/getScreenId.js"></script>

    <!-- for Edige/FF/Chrome/Opera/etc. getUserMedia support -->
    <script src="https://cdn.webrtc-experiment.com/gumadapter.js"></script>
</head>

<body>
    <article>
        <header style="text-align: center;">
            <h1>¡Sube tu video!</h1>

            <p style="margin:0;margin-bottom:-30px;">
                Graba un pequeño video y compártelo en las redes sociales
            </p>
        </header>

        <div class="github-stargazers"></div>
        
        <section class="experiment recordrtc">
            <h2 class="header">
                <!-- Ocultamos las opciones -->
                <select class="recording-media hidden">
                    <option value="record-video">Video</option>
                    <option value="record-audio">Audio</option>
                    <option value="record-screen">Screen</option>
                </select>
                <!-- Ocultamos los formatos -->
                <select class="media-container-format hidden">
                    <option>WebM</option>
                    <option disabled>Mp4</option>
                    <option disabled>WAV</option>
                    <option disabled>Ogg</option>
                    <option>Gif</option>
                </select>
                
                <button>Comienza a grabar</button>
            </h2>
            
            <div style="text-align: center; display: none;">
                <!-- Ocultamos el botón de guardar en disco -->
                <button id="save-to-disk" class="hidden">Save To Disk</button>
                <button id="open-new-tab">Comprobar en otra ventana</button>
                <button id="upload-to-server">Compartir en redes</button>
            </div>
            
            <br>

            <video controls ></video>
        </section>

        <section class="experiment">
            <h2 class="header">
                El texto será:
            </h2>
            <?php $nombreusuario = htmlspecialchars( $_GET["nombre"] ); ?>
            <p>
                ¡<?php echo $nombreusuario; ?> ha compartido un vídeo con nosotros!
            </p>
        </section>
        
        <script>
            (function() {
                var params = {},
                    r = /([^&=]+)=?([^&]*)/g;

                function d(s) {
                    return decodeURIComponent(s.replace(/\+/g, ' '));
                }

                var match, search = window.location.search;
                while (match = r.exec(search.substring(1))) {
                    params[d(match[1])] = d(match[2]);

                    if(d(match[2]) === 'true' || d(match[2]) === 'false') {
                        params[d(match[1])] = d(match[2]) === 'true' ? true : false;
                    }
                }

                window.params = params;
            })();
        </script>

        <script type="text/javascript" charset="utf-8">
            // navigator.mediaDevices.getUserMedia({
            //     video: true
            // }).then(function(stream) {
            //     var recordRTC = RecordRTC(stream, {
            //         recorderType: MediaStreamRecorder
            //     });

            //     // auto stop recording after 5 seconds
            //     recordRTC.setRecordingDuration(5 * 1000).onRecordingStopped(function(url) {
            //         console.debug('setRecordingDuration', url);
            //         window.open(url);
            //     })

            //     recordRTC.startRecording();
            // }).catch(function(error) {
            //     console.error(error);
            // });
        </script>

        <script>
            function intallFirefoxScreenCapturingExtension() {
                InstallTrigger.install({
                    'Foo': {
                        // URL: 'https://addons.mozilla.org/en-US/firefox/addon/enable-screen-capturing/',
                        URL: 'https://addons.mozilla.org/firefox/downloads/file/355418/enable_screen_capturing_in_firefox-1.0.006-fx.xpi?src=cb-dl-hotness',
                        toString: function() {
                            return this.URL;
                        }
                    }
                });
            }

            var recordingDIV         = document.querySelector('.recordrtc');
            var recordingMedia       = recordingDIV.querySelector('.recording-media');
            var recordingPlayer      = recordingDIV.querySelector('video');
            var mediaContainerFormat = recordingDIV.querySelector('.media-container-format');
            
            window.onbeforeunload = function() {
                recordingDIV.querySelector('button').disabled = false;
                recordingMedia.disabled = false;
                mediaContainerFormat.disabled = false;
            };
            
            recordingDIV.querySelector('button').onclick = function() {
                var button = this;

                if(button.innerHTML === 'Stop Recording') {
                    button.disabled = true;
                    button.disableStateWaiting = true;
                    setTimeout(function() {
                        button.disabled = false;
                        button.disableStateWaiting = false;
                    }, 2 * 1000);
                    
                    button.innerHTML = 'Star Recording';

                    function stopStream() {
                        if(button.stream && button.stream.stop) {
                            button.stream.stop();
                            button.stream = null;
                        }
                    }
                    
                    if(button.recordRTC) {
                        if(button.recordRTC.length) {
                            button.recordRTC[0].stopRecording(function(url) {
                                if(!button.recordRTC[1]) {
                                    button.recordingEndedCallback(url);
                                    stopStream();

                                    saveToDiskOrOpenNewTab(button.recordRTC[0]);
                                    return;
                                }

                                button.recordRTC[1].stopRecording(function(url) {
                                    button.recordingEndedCallback(url);
                                    stopStream();
                                });
                            });
                        }
                        else {
                            button.recordRTC.stopRecording(function(url) {
                                button.recordingEndedCallback(url);
                                stopStream();

                                saveToDiskOrOpenNewTab(button.recordRTC);
                            });
                        }
                    }
                    
                    return;
                }
                
                button.disabled = true;
                
                var commonConfig = {
                    onMediaCaptured: function(stream) {
                        button.stream = stream;
                        if(button.mediaCapturedCallback) {
                            button.mediaCapturedCallback();
                        }

                        button.innerHTML = 'Stop Recording';
                        button.disabled = false;
                    },
                    onMediaStopped: function() {
                        button.innerHTML = 'Start Recording';
                        
                        if(!button.disableStateWaiting) {
                            button.disabled = false;
                        }
                    },
                    onMediaCapturingFailed: function(error) {
                        if(error.name === 'PermissionDeniedError' && !!navigator.mozGetUserMedia) {
                            intallFirefoxScreenCapturingExtension();
                        }
                        
                        commonConfig.onMediaStopped();
                    }
                };

                var mimeType = 'video/webm';
                if(mediaContainerFormat.value === 'Mp4') {
                    mimeType = 'video/mp4';
                }
                
                if(recordingMedia.value === 'record-video') {
                    captureVideo(commonConfig);
                    
                    button.mediaCapturedCallback = function() {
                        button.recordRTC = RecordRTC(button.stream, {
                            type: mediaContainerFormat.value === 'Gif' ? 'gif' : 'video',
                            mimeType: isChrome ? null: mimeType,
                            disableLogs: params.disableLogs || false,
                            canvas: {
                                width: params.canvas_width || 320,
                                height: params.canvas_height || 240
                            },
                            frameInterval: typeof params.frameInterval !== 'undefined' ? parseInt(params.frameInterval) : 20 // minimum time between pushing frames to Whammy (in milliseconds)
                        });
                        
                        button.recordingEndedCallback = function(url) {
                            recordingPlayer.src = null;

                            if(mediaContainerFormat.value === 'Gif') {
                                recordingPlayer.pause();
                                recordingPlayer.poster = url;

                                recordingPlayer.onended = function() {
                                    recordingPlayer.pause();
                                    recordingPlayer.poster = URL.createObjectURL(button.recordRTC.blob);
                                };
                                return;
                            }
                            
                            recordingPlayer.src = url;
                            recordingPlayer.play();

                            recordingPlayer.onended = function() {
                                recordingPlayer.pause();
                                recordingPlayer.src = URL.createObjectURL(button.recordRTC.blob);
                            };
                        };
                        
                        button.recordRTC.startRecording();
                    };
                }
                
                if(recordingMedia.value === 'record-audio') {
                    captureAudio(commonConfig);
                    
                    button.mediaCapturedCallback = function() {
                        var options = {
                            type: 'audio',
                            mimeType: mimeType,
                            bufferSize: typeof params.bufferSize == 'undefined' ? 0 : parseInt(params.bufferSize),
                            sampleRate: typeof params.sampleRate == 'undefined' ? 44100 : parseInt(params.sampleRate),
                            leftChannel: params.leftChannel || false,
                            disableLogs: params.disableLogs || false,
                            recorderType: webrtcDetectedBrowser === 'edge' ? StereoAudioRecorder : null
                        };

                        if(typeof params.sampleRate == 'undefined') {
                            delete options.sampleRate;
                        }

                        button.recordRTC = RecordRTC(button.stream, options);
                        
                        button.recordingEndedCallback = function(url) {
                            var audio = new Audio();
                            audio.src = url;
                            audio.controls = true;
                            recordingPlayer.parentNode.appendChild(document.createElement('hr'));
                            recordingPlayer.parentNode.appendChild(audio);

                            if(audio.paused) audio.play();

                            audio.onended = function() {
                                audio.pause();
                                audio.src = URL.createObjectURL(button.recordRTC.blob);
                            };
                        };
                        
                        button.recordRTC.startRecording();
                    };
                }

                if(recordingMedia.value === 'record-audio-plus-video') {
                    captureAudioPlusVideo(commonConfig);
                    
                    button.mediaCapturedCallback = function() {

                        if(typeof MediaRecorder === 'undefined') { // opera or chrome etc.
                            button.recordRTC = [];

                            if(!params.bufferSize) {
                                // it fixes audio issues whilst recording 720p
                                params.bufferSize = 16384;
                            }

                            var options = {
                                type: 'audio',
                                bufferSize: typeof params.bufferSize == 'undefined' ? 0 : parseInt(params.bufferSize),
                                sampleRate: typeof params.sampleRate == 'undefined' ? 44100 : parseInt(params.sampleRate),
                                leftChannel: params.leftChannel || false,
                                disableLogs: params.disableLogs || false,
                                recorderType: webrtcDetectedBrowser === 'edge' ? StereoAudioRecorder : null
                            };

                            if(typeof params.sampleRate == 'undefined') {
                                delete options.sampleRate;
                            }

                            var audioRecorder = RecordRTC(button.stream, options);

                            var videoRecorder = RecordRTC(button.stream, {
                                type: 'video',
                                disableLogs: params.disableLogs || false,
                                canvas: {
                                    width: params.canvas_width || 320,
                                    height: params.canvas_height || 240
                                },
                                frameInterval: typeof params.frameInterval !== 'undefined' ? parseInt(params.frameInterval) : 20 // minimum time between pushing frames to Whammy (in milliseconds)
                            });

                            // to sync audio/video playbacks in browser!
                            videoRecorder.initRecorder(function() {
                                audioRecorder.initRecorder(function() {
                                    audioRecorder.startRecording();
                                    videoRecorder.startRecording();
                                });
                            });

                            button.recordRTC.push(audioRecorder, videoRecorder);

                            button.recordingEndedCallback = function() {
                                var audio = new Audio();
                                audio.src = audioRecorder.toURL();
                                audio.controls = true;
                                audio.autoplay = true;

                                audio.onloadedmetadata = function() {
                                    recordingPlayer.src = videoRecorder.toURL();
                                    recordingPlayer.play();
                                };

                                recordingPlayer.parentNode.appendChild(document.createElement('hr'));
                                recordingPlayer.parentNode.appendChild(audio);

                                if(audio.paused) audio.play();
                            };
                            return;
                        }

                        button.recordRTC = RecordRTC(button.stream, {
                            type: 'video',
                            mimeType: mimeType,
                            disableLogs: params.disableLogs || false,
                            // bitsPerSecond: 25 * 8 * 1025 // 25 kbits/s
                            getNativeBlob: false // enable it for longer recordings
                        });
                        
                        button.recordingEndedCallback = function(url) {
                            recordingPlayer.muted = false;
                            recordingPlayer.removeAttribute('muted');
                            recordingPlayer.src = url;
                            recordingPlayer.play();

                            recordingPlayer.onended = function() {
                                recordingPlayer.pause();
                                recordingPlayer.src = URL.createObjectURL(button.recordRTC.blob);
                            };
                        };
                        
                        button.recordRTC.startRecording();
                    };
                }
                
                if(recordingMedia.value === 'record-screen') {
                    captureScreen(commonConfig);
                    
                    button.mediaCapturedCallback = function() {
                        button.recordRTC = RecordRTC(button.stream, {
                            type: mediaContainerFormat.value === 'Gif' ? 'gif' : 'video',
                            mimeType: mimeType,
                            disableLogs: params.disableLogs || false,
                            canvas: {
                                width: params.canvas_width || 320,
                                height: params.canvas_height || 240
                            }
                        });
                        
                        button.recordingEndedCallback = function(url) {
                            recordingPlayer.src = null;

                            if(mediaContainerFormat.value === 'Gif') {
                                recordingPlayer.pause();
                                recordingPlayer.poster = url;
                                recordingPlayer.onended = function() {
                                    recordingPlayer.pause();
                                    recordingPlayer.poster = URL.createObjectURL(button.recordRTC.blob);
                                };
                                return;
                            }
                            
                            recordingPlayer.src = url;
                            recordingPlayer.play();
                        };
                        
                        button.recordRTC.startRecording();
                    };
                }

                // note: audio+tab is supported in Chrome 50+
                // todo: add audio+tab recording
                if(recordingMedia.value === 'record-audio-plus-screen') {
                    captureAudioPlusScreen(commonConfig);
                    
                    button.mediaCapturedCallback = function() {
                        button.recordRTC = RecordRTC(button.stream, {
                            type: 'video',
                            mimeType: mimeType,
                            disableLogs: params.disableLogs || false,
                            // bitsPerSecond: 25 * 8 * 1025 // 25 kbits/s
                            getNativeBlob: false // enable it for longer recordings
                        });
                        
                        button.recordingEndedCallback = function(url) {
                            recordingPlayer.muted = false;
                            recordingPlayer.removeAttribute('muted');
                            recordingPlayer.src = url;
                            recordingPlayer.play();

                            recordingPlayer.onended = function() {
                                recordingPlayer.pause();
                                recordingPlayer.src = URL.createObjectURL(button.recordRTC.blob);
                            };
                        };
                        
                        button.recordRTC.startRecording();
                    };
                }
            };
            
            function captureVideo(config) {
                captureUserMedia({video: true}, function(videoStream) {
                    recordingPlayer.srcObject = videoStream;
                    recordingPlayer.play();
                    
                    config.onMediaCaptured(videoStream);
                    
                    videoStream.onended = function() {
                        config.onMediaStopped();
                    };
                }, function(error) {
                    config.onMediaCapturingFailed(error);
                });
            }
            
            function captureAudio(config) {
                captureUserMedia({audio: true}, function(audioStream) {
                    recordingPlayer.srcObject = audioStream;
                    recordingPlayer.play();
                    
                    config.onMediaCaptured(audioStream);
                    
                    audioStream.onended = function() {
                        config.onMediaStopped();
                    };
                }, function(error) {
                    config.onMediaCapturingFailed(error);
                });
            }

            function captureAudioPlusVideo(config) {
                captureUserMedia({video: true, audio: true}, function(audioVideoStream) {
                    recordingPlayer.srcObject = audioVideoStream;
                    recordingPlayer.play();
                    
                    config.onMediaCaptured(audioVideoStream);
                    
                    audioVideoStream.onended = function() {
                        config.onMediaStopped();
                    };
                }, function(error) {
                    config.onMediaCapturingFailed(error);
                });
            }
            
            function captureScreen(config) {
                getScreenId(function(error, sourceId, screenConstraints) {
                    if (error === 'not-installed') {
                        document.write('<h1><a target="_blank" href="https://chrome.google.com/webstore/detail/screen-capturing/ajhifddimkapgcifgcodmmfdlknahffk">Please install this chrome extension then reload the page.</a></h1>');
                    }

                    if (error === 'permission-denied') {
                        alert('Screen capturing permission is denied.');
                    }

                    if (error === 'installed-disabled') {
                        alert('Please enable chrome screen capturing extension.');
                    }
                    
                    if(error) {
                        config.onMediaCapturingFailed(error);
                        return;
                    }

                    delete screenConstraints.video.mozMediaSource;
                    captureUserMedia(screenConstraints, function(screenStream) {
                        recordingPlayer.srcObject = screenStream;
                        recordingPlayer.play();
                        
                        config.onMediaCaptured(screenStream);
                        
                        screenStream.onended = function() {
                            config.onMediaStopped();
                        };
                    }, function(error) {
                        config.onMediaCapturingFailed(error);
                    });
                });
            }

            function captureAudioPlusScreen(config) {
                getScreenId(function(error, sourceId, screenConstraints) {
                    if (error === 'not-installed') {
                        document.write('<h1><a target="_blank" href="https://chrome.google.com/webstore/detail/screen-capturing/ajhifddimkapgcifgcodmmfdlknahffk">Please install this chrome extension then reload the page.</a></h1>');
                    }

                    if (error === 'permission-denied') {
                        alert('Screen capturing permission is denied.');
                    }

                    if (error === 'installed-disabled') {
                        alert('Please enable chrome screen capturing extension.');
                    }
                    
                    if(error) {
                        config.onMediaCapturingFailed(error);
                        return;
                    }

                    screenConstraints.audio = true;

                    delete screenConstraints.video.mozMediaSource;
                    captureUserMedia(screenConstraints, function(screenStream) {
                        recordingPlayer.srcObject = screenStream;
                        recordingPlayer.play();
                        
                        config.onMediaCaptured(screenStream);
                        
                        screenStream.onended = function() {
                            config.onMediaStopped();
                        };
                    }, function(error) {
                        config.onMediaCapturingFailed(error);
                    });
                });
            }
            
            function captureUserMedia(mediaConstraints, successCallback, errorCallback) {
                var isBlackBerry = !!(/BB10|BlackBerry/i.test(navigator.userAgent || ''));
                if(isBlackBerry && !!(navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia)) {
                    navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia;
                    navigator.getUserMedia(mediaConstraints, successCallback, errorCallback);
                    return;
                }

                navigator.mediaDevices.getUserMedia(mediaConstraints).then(successCallback).catch(errorCallback);
            }
            
            function setMediaContainerFormat(arrayOfOptionsSupported) {
                var options = Array.prototype.slice.call(
                    mediaContainerFormat.querySelectorAll('option')
                );
                
                var selectedItem;
                options.forEach(function(option) {
                    option.disabled = true;
                    
                    if(arrayOfOptionsSupported.indexOf(option.value) !== -1) {
                        option.disabled = false;
                        
                        if(!selectedItem) {
                            option.selected = true;
                            selectedItem = option;
                        }
                    }
                });
            }
            
            recordingMedia.onchange = function() {
                var options = [];
                if(webrtcDetectedBrowser === 'firefox') {
                    if(this.value === 'record-audio') {
                        options.push('Ogg');
                    }
                    else {
                        options.push('WebM', 'Mp4');
                    }

                    setMediaContainerFormat(options);
                    return;
                }
                if(this.value === 'record-audio') {
                    setMediaContainerFormat(['WAV', 'Ogg']);
                    return;
                }
                setMediaContainerFormat(['WebM', 'Mp4', 'Ogg']);
            };

            if(webrtcDetectedBrowser === 'edge') {
                // webp isn't supported in Microsoft Edge
                // neither MediaRecorder API
                // so lets disable both video/screen recording options

                console.warn('Neither MediaRecorder API nor webp is supported in Microsoft Edge. You cam merely record audio.');

                recordingMedia.innerHTML = '<option value="record-audio">Audio</option>';
                setMediaContainerFormat(['WAV']);
            }

            if(webrtcDetectedBrowser === 'firefox') {
                // Firefox implemented both MediaRecorder API as well as WebAudio API
                // Their MediaRecorder implementation supports both audio/video recording in single container format
                // Remember, we can't currently pass bit-rates or frame-rates values over MediaRecorder API (their implementation lakes these features)

                recordingMedia.innerHTML = '<option value="record-audio-plus-video">Audio+Video</option>' 
                                            + '<option value="record-audio-plus-screen">Audio+Screen</option>' 
                                            + recordingMedia.innerHTML;

                setMediaContainerFormat(['WebM', 'Mp4']);
            }

            if(webrtcDetectedBrowser === 'chrome') {
                recordingMedia.innerHTML = '<option value="record-audio-plus-video">Audio+Video</option>' 
                                            + recordingMedia.innerHTML;

                if(typeof MediaRecorder === 'undefined') {
                    console.info('This RecordRTC demo merely tries to playback recorded audio/video sync inside the browser. It still generates two separate files (WAV/WebM).');
                }
            }

            var listOfFilesUploaded = [];

            function uploadToServer(recordRTC, callback) {
                var blob     = recordRTC instanceof Blob ? recordRTC : recordRTC.blob;
                var fileType = blob.type.split('/')[0] || 'audio';
                // var fileName = (Math.random() * 1000).toString().replace('.', '');
                var fileName = Date.now();
                // Get url parameter (nombre)
                var sPageURL   = decodeURIComponent(window.location.search.substring(1)),
                    sPageParam = sPageURL.split('=');
                    // i;

                // for (var i = 0; i < sPageParam.length; i++) {

                //     sPageParam[i] === undefined ? true : sPageParam[i];
                // };
                        

                var nombreUsuario = sPageParam[1];

                if (fileType === 'audio') {
                    fileName += '.' + (!!navigator.mozGetUserMedia ? 'ogg' : 'wav');
                } else {
                    fileName += '.webm';
                }

                // create FormData
                var formData = new FormData();
                formData.append(fileType + '-filename', fileName);
                formData.append(fileType + '-blob', blob);
                formData.append(nombreUsuario + ' ', nombreUsuario);

                callback('Uploading ' + fileType + ' recording to server.');

                function makeXMLHttpRequest(url, data, callback) {
                    var request = new XMLHttpRequest();
                    request.onreadystatechange = function() {
                        if (request.readyState == 4 && request.status == 200) {
                            callback('upload-ended');
                        }
                    };

                    request.upload.onloadstart = function() {
                        callback('Upload started...');
                    };

                    request.upload.onprogress = function(event) {
                        callback('Upload Progress ' + Math.round(event.loaded / event.total * 100) + "%");
                    };

                    request.upload.onload = function() {
                        callback('progress-about-to-end');
                    };

                    request.upload.onload = function() {
                        callback('progress-ended');
                    };

                    request.upload.onerror = function(error) {
                        callback('Failed to upload to server');
                        console.error('XMLHttpRequest failed', error);
                    };

                    request.upload.onabort = function(error) {
                        callback('Upload aborted.');
                        console.error('XMLHttpRequest aborted', error);
                    };

                    request.open('POST', url, true);
                    request.send(data);
//*******************************************************
                    console.log('valores de url: ');
                    console.dir(url);

                    console.log('valores de data: ');
                    console.dir(data);
                    var initialURL    = location.href.replace(location.href.split('/').pop(), '') + 'uploads/';
                    var newLocation   = '/';
                    var finalLocation = initialURL.replace(/(nombre=).*?(&)/,'$1' + newLocation + '$2');
                    console.log('Ruta archivo video: ' + finalLocation + fileName);
//*********************************************************              
                }

                makeXMLHttpRequest('save.php', formData, function(progress) {
                    if (progress !== 'upload-ended') {
                        callback(progress);
                        return;
                    }

                    var initialURL    = location.href.replace(location.href.split('/').pop(), '') + 'uploads/';
                    var newLocation   = '/';
                    var finalLocation = initialURL.replace(/(nombre=).*?(&)/,'$1' + newLocation + '$2');
                    callback('ended', initialURL + fileName);

                    // to make sure we can delete as soon as visitor leaves
                    //listOfFilesUploaded.push(initialURL + fileName);
                });

            }// end function uploadToServer


            function saveToDiskOrOpenNewTab(recordRTC) {
                recordingDIV.querySelector('#save-to-disk').parentNode.style.display = 'block';
                recordingDIV.querySelector('#save-to-disk').onclick = function() {
                    if(!recordRTC) return alert('No recording found.');
                    
                    recordRTC.save();
                };
                
                recordingDIV.querySelector('#open-new-tab').onclick = function() {
                    if(!recordRTC) return alert('No recording found.');
                    
                    window.open(recordRTC.toURL());
                };

                recordingDIV.querySelector('#upload-to-server').disabled = false;
                recordingDIV.querySelector('#upload-to-server').onclick = function() {
                    if(!recordRTC) return alert('No recording found.');
                    this.disabled = true;
                    
                    var button = this;
                    uploadToServer(recordRTC, function(progress, fileURL) {
                        if(progress === 'ended') {
                            button.disabled = false;
                            button.innerHTML = 'Click para descargar el vídeo';
                            button.onclick = function() {
                                window.open(fileURL);
                            };
                            return;
                        }
                        button.innerHTML = progress;
                    });
                };
            }// end function(saveToDiskOrOpenNewTab)

            window.onbeforeunload = function() {
                recordingDIV.querySelector('button').disabled = false;
                recordingMedia.disabled = false;
                mediaContainerFormat.disabled = false;

                if(!listOfFilesUploaded.length) return;

                listOfFilesUploaded.forEach(function(fileURL) {
                    var request = new XMLHttpRequest();
                    request.onreadystatechange = function() {
                        if (request.readyState == 4 && request.status == 200) {
                            if(this.responseText === ' problem deleting files.') {
                                alert('Failed to delete ' + fileURL + ' from the server.');
                                return;
                            }

                            listOfFilesUploaded = [];
                            alert('You can leave now. Your files are removed from the server.');
                        }
                    };
                    request.open('POST', 'delete.php');

                    var formData = new FormData();
                    formData.append('delete-file', fileURL.split('/').pop());
                    request.send(formData);
                });

                return 'Please wait few seconds before your recordings are deleted from the server.';
            };
        </script>

        <script>
            // todo: need to check exact chrome browser because opera also uses chromium framework
            var isChrome = !!navigator.webkitGetUserMedia;
            
            // DetectRTC.js - https://github.com/muaz-khan/WebRTC-Experiment/tree/master/DetectRTC
            // Below code is taken from RTCMultiConnection-v1.8.js (http://www.rtcmulticonnection.org/changes-log/#v1.8)
            var DetectRTC = {};

            (function () {
                
                var screenCallback;
                
                DetectRTC.screen = {
                    chromeMediaSource: 'screen',
                    getSourceId: function(callback) {
                        if(!callback) throw '"callback" parameter is mandatory.';
                        screenCallback = callback;
                        window.postMessage('get-sourceId', '*');
                    },
                    isChromeExtensionAvailable: function(callback) {
                        if(!callback) return;
                        
                        if(DetectRTC.screen.chromeMediaSource == 'desktop') return callback(true);
                        
                        // ask extension if it is available
                        window.postMessage('are-you-there', '*');
                        
                        setTimeout(function() {
                            if(DetectRTC.screen.chromeMediaSource == 'screen') {
                                callback(false);
                            }
                            else callback(true);
                        }, 2000);
                    },
                    onMessageCallback: function(data) {
                        if (!(typeof data == 'string' || !!data.sourceId)) return;
                        
                        console.log('chrome message', data);
                        
                        // "cancel" button is clicked
                        if(data == 'PermissionDeniedError') {
                            DetectRTC.screen.chromeMediaSource = 'PermissionDeniedError';
                            if(screenCallback) return screenCallback('PermissionDeniedError');
                            else throw new Error('PermissionDeniedError');
                        }
                        
                        // extension notified his presence
                        if(data == 'rtcmulticonnection-extension-loaded') {
                            if(document.getElementById('install-button')) {
                                document.getElementById('install-button').parentNode.innerHTML = '<strong>Great!</strong> <a href="https://chrome.google.com/webstore/detail/screen-capturing/ajhifddimkapgcifgcodmmfdlknahffk" target="_blank">Google chrome extension</a> is installed.';
                            }
                            DetectRTC.screen.chromeMediaSource = 'desktop';
                        }
                        
                        // extension shared temp sourceId
                        if(data.sourceId) {
                            DetectRTC.screen.sourceId = data.sourceId;
                            if(screenCallback) screenCallback( DetectRTC.screen.sourceId );
                        }
                    },
                    getChromeExtensionStatus: function (callback) {
                        if (!!navigator.mozGetUserMedia) return callback('not-chrome');
                        
                        var extensionid = 'ajhifddimkapgcifgcodmmfdlknahffk';

                        var image = document.createElement('img');
                        image.src = 'chrome-extension://' + extensionid + '/icon.png';
                        image.onload = function () {
                            DetectRTC.screen.chromeMediaSource = 'screen';
                            window.postMessage('are-you-there', '*');
                            setTimeout(function () {
                                if (!DetectRTC.screen.notInstalled) {
                                    callback('installed-enabled');
                                }
                            }, 2000);
                        };
                        image.onerror = function () {
                            DetectRTC.screen.notInstalled = true;
                            callback('not-installed');
                        };
                    }
                };
                
                // check if desktop-capture extension installed.
                if(window.postMessage && isChrome) {
                    DetectRTC.screen.isChromeExtensionAvailable();
                }
            })();
            
            DetectRTC.screen.getChromeExtensionStatus(function(status) {
                if(status == 'installed-enabled') {
                    if(document.getElementById('install-button')) {
                        document.getElementById('install-button').parentNode.innerHTML = '<strong>Great!</strong> <a href="https://chrome.google.com/webstore/detail/screen-capturing/ajhifddimkapgcifgcodmmfdlknahffk" target="_blank">Google chrome extension</a> is installed.';
                    }
                    DetectRTC.screen.chromeMediaSource = 'desktop';
                }
            });
            
            window.addEventListener('message', function (event) {
                if (event.origin != window.location.origin) {
                    return;
                }
                
                DetectRTC.screen.onMessageCallback(event.data);
            });
        </script>

</body>

</html>
