var app = {};
var snd = new Audio("/media/beep.wav");
app.init = function () {
	$(window).resize(app.resize);
	app.resize();
}
app.resize = function () {
	$('.inherit_height,.inherit_height2').css({
		height:'1px',
	})
	$('#outdiv,video').css({
		height:'1px',
		width:'1px',
		'max-height':'1px'
	});
	$('.inherit_height').each(function () {
		$(this).css({
			height : $(this).parent().height()
		});
	});
	$('.inherit_height2').each(function () {
		$(this).css({
			height : $(this).parent().height()*1-42
		});
	});
	$('#outdiv,video').each(function () {
		$(this).css({
			width : $('#outframe').width(),
			height : 'auto',
			'max-height' : $('#outframe').height()
		});
	});
	$('.scalefont').css({
		'font-size' : $('.scalefontmarker').width()/15-5,
		'margin-top' : -($('.scalefontmarker').width()/10)+45
	});
	var frame = $('#outframe').height();
	var out = $('#outdiv').height();
	var dif = frame - out;
	$('#outdiv').css({
		'padding-top' : dif > 0 ? dif/2 : 0
	});
};
app.load_cam = function () {
	load(800, 600);
};
$(document).ready(function () {
	app.init();
});
window.onload = function () {
	app.resize();
	window.setTimeout(function () {
		app.load_cam();
	}, 200);
	window.setInterval(function () {
		app.resize();
	}, 1500);
}

var gCtx = null;
var gCanvas = null;
var c=0;
var stype=0;
var gUM=false;
var webkit=false;
var moz=false;
var objVideo=null;
var workerCount = 0;
var resultArray = [];

var imghtml='<div id="qrfile"><canvas id="out-canvas" width="40" height="40"></canvas></div>';

var vidhtml = '<video id="v" autoplay></video>';
var DecodeWorker = new Worker("/js/mobile/DecoderWorker.js");
DecodeWorker.onmessage = receiveMessage;

function initCanvas(w,h)
{
    gCanvas = document.getElementById("qr-canvas");
    gCanvas.style.width = w + "px";
    gCanvas.style.height = h + "px";
    gCanvas.width = w;
    gCanvas.height = h;
    gCtx = gCanvas.getContext("2d");
    gCtx.clearRect(0, 0, w, h);
}

function receiveMessage(e) {
	if(e.data.success === "log") {
		console.log(e.data.result);
		return;
	}
	if(e.data.finished) {
		workerCount--;
		if(workerCount) {
			if(resultArray.length == 0) {
				DecodeWorker.postMessage({ImageData: gCtx.getImageData(0,0,gCanvas.width,gCanvas.height).data, Width: gCanvas.width, Height: gCanvas.height, cmd: "flip"});
			} else {
				workerCount--;
			}
		}
	}
	if(e.data.success){
		var tempArray = e.data.result;
		for(var i = 0; i < tempArray.length; i++) {
			if(resultArray.indexOf(tempArray[i]) == -1) {
				var strResult = tempArray[i];
				strResult = strResult.replace(/^.+?: /, '');
				if (strResult.length>8)
					resultArray.push(strResult);
			}
		};
		if (resultArray.length) {
			console.log(resultArray[0]);
			read(resultArray[0]);
		}
	}else{
		if(resultArray.length === 0 && workerCount === 0) {
			//console.log('DecodeWorker: Decoding failed');
		}
	}
}

function captureToCanvas() {
    if(stype!=1)
        return;
    if(gUM)
    {
        try{
            gCtx.drawImage(objVideo,0,0);

			workerCount = 2;
			resultArray = [];
			DecodeWorker.postMessage({ImageData: gCtx.getImageData(0,0,gCanvas.width,gCanvas.height).data, Width: gCanvas.width, Height: gCanvas.height, cmd: "normal"});


            try{
                qrcode.decode();
            }
            catch(e){
                console.log(e);
                setTimeout(captureToCanvas, 500);
            };
        }
        catch(e){
                console.log(e);
                setTimeout(captureToCanvas, 500);
        };
    }
}

function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function read(a)
{
	$('#audio')[0].play();
	$('#barcode').val(a);
	$('form').submit();
}

function isCanvasSupported(){
  var elem = document.createElement('canvas');
  return !!(elem.getContext && elem.getContext('2d'));
}
function success(stream) {
    if(webkit)
        objVideo.src = window.webkitURL.createObjectURL(stream);
    else
    if(moz)
    {
        objVideo.mozSrcObject = stream;
        objVideo.play();
    }
    else
        objVideo.src = stream;
    gUM=true;
    setTimeout(captureToCanvas, 500);
}

function error(error) {
    gUM=false;
    return;
}

function load(w,h)
{
	if(isCanvasSupported() && window.File && window.FileReader)
	{
		initCanvas(w, h);
		qrcode.callback = read;
        setwebcam();
	}
	else
	{
		document.getElementById("outdiv").innerHTML='<p id="mp1">QR code scanner for HTML5 capable browsers</p><br>'+
        '<br><p id="mp2">sorry your browser is not supported</p><br><br>'+
        '<p id="mp1">try <a href="http://www.mozilla.com/firefox"><img src="firefox.png"/></a> or <a href="http://chrome.google.com"><img src="chrome_logo.gif"/></a> or <a href="http://www.opera.com"><img src="Opera-logo.png"/></a></p>';
	}
}

function setwebcam()
{
    if(stype==1)
    {
        setTimeout(captureToCanvas, 500);
        return;
    }
    var n=navigator;
    document.getElementById("outdiv").innerHTML = vidhtml;
    objVideo=document.getElementById("v");
    if(n.getUserMedia)
        n.getUserMedia({video: true, audio: false}, success, error);
    else
    if(n.webkitGetUserMedia)
    {
        webkit=true;
        n.webkitGetUserMedia({video: true, audio: false}, success, error);
    }
    else
    if(n.mozGetUserMedia)
    {
        moz=true;
        n.mozGetUserMedia({video: true, audio: false}, success, error);
    }

    //document.getElementById("qrimg").src="qrimg2.png";
    //document.getElementById("webcamimg").src="webcam.png";
    //document.getElementById("qrimg").style.opacity=0.2;
    //document.getElementById("webcamimg").style.opacity=1.0;

    stype=1;
    setTimeout(captureToCanvas, 500);
	app.resize();
}