<?php
$uid=$_GET['uid'];
?>
<!DOCTYPE html>
<html>
  <!--
    Copyright 2010 Google Inc.

    Licensed under the Apache License, Version 2.0 (the "License");
    you may not use this file except in compliance with the License.
    You may obtain a copy of the License at

         http://www.apache.org/licenses/LICENSE-2.0

    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS,
    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
    See the License for the specific language governing permissions and
    limitations under the License.
  -->
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="chrome=1">
    <title></title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.4/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="http://yui.yahooapis.com/2.8.1/build/reset/reset-min.css">
    <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.4/themes/base/jquery.ui.all.css">
    <!-- canvg used to overcome <img src=SVG> toDataURL security issues -->
    <!-- see code.google.com/p/chromium/issues/detail?id=54204 -->
    <script src="lib/canvg/rgbcolor.js"></script> 
    <script src="lib/canvg/canvg.js"></script>

    <link rel="stylesheet" href="lib/colorpicker/css/colorpicker.css">
    <script src="lib/colorpicker/js/colorpicker.js"></script>

    <!-- for .ZIP downloads -->
    <script src="http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script>
    <script src="lib/downloadify/js/downloadify.min.js"></script>
    <script src="lib/jszip/jszip.js"></script>

    <link rel="stylesheet" href="lib/jquery-ui-1.8.4.android-theme/theme.css">

    <link rel="stylesheet" href="css/studio.css">
    <script src="js/asset-studio.js"></script>

  </head>
  <body>
    <div id="main-container">

      <div id="page-header">
        <p id="page-intro">
          The <strong>launcher icon generator</strong> will create icons that you
          can use in your Android application, from a variety of source images.
          To begin, simply enter the input details below. Output will be shown
          below.
        </p>
      </div>
      <div id="inputs">
        <div id="inputs-form"></div>
        <input type="checkbox" id="output-show-guides">
        <label for="output-show-guides">Show Guides</label>
      </div>
      <div id="outputs">
        <h3>
          Output images
          <div style="text-align:left;margin-top:10px;">
          <input type="button" id="save-button" value="Upload &amp; Save"/><div id="save-res"></div>
          </div>
          <div id="save-canvas"></div>
          <div id="zip-button"></div>
        </h3>
      </div>
      <div id="footer">
        <p>See the source at the <a href="http://code.google.com/p/android-ui-utils">android-ui-utils</a> Google Code project.</p>
        <p>All generated art is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by/3.0/">Creative Commons Attribution 3.0 Unported License</a>.</p>
      </div>
    </div>

    <script>
		function IFrame(parentElement)
		{
		   // Create the iframe which will be returned
		   var iframe = document.createElement("iframe");
		 
		   // If no parent element is specified then use body as the parent element
		   if(parentElement == null)
		      parentElement = document.body;
		 
		   // This is necessary in order to initialize the document inside the iframe
		   parentElement.appendChild(iframe);
		 
		   // Initiate the iframe's document to null
		   iframe.doc = null;
		 
		   // Depending on browser platform get the iframe's document, this is only
		   // available if the iframe has already been appended to an element which
		   // has been added to the document
		   if(iframe.contentDocument)
		      // Firefox, Opera
		      iframe.doc = iframe.contentDocument;
		   else if(iframe.contentWindow)
		      // Internet Explorer
		      iframe.doc = iframe.contentWindow.document;
		   else if(iframe.document)
		      // Others?
		      iframe.doc = iframe.document;
		 
		   // If we did not succeed in finding the document then throw an exception
		   if(iframe.doc == null)
		      throw "Document not found, append the parent element to the DOM before creating the IFrame";
		 
		   // Create the script inside the iframe's document which will call the
		   iframe.doc.open();
		   iframe.doc.close();
		 
		   // Return the iframe, now with an extra property iframe.doc containing the
		   // iframe's document
		   return iframe;
		}    
    
            
            
                
      $(studio.checkBrowser);
      $('#output-show-guides').button().click(regenerate);
      $('#save-button').button().click(upload);
		
      
      // Create image output slots
      var group = studio.ui.createImageOutputGroup({
        container: $('#outputs')
      });
/*
      for (var density in {'xhdpi':1, 'hdpi':1, 'mdpi':1, 'ldpi':1, 'web':1})
        studio.ui.createImageOutputSlot({
          container: group,
          id: 'out-icon-' + density,
          label: (density == 'web') ? 'web, hi-res' : density
        });
*/
      for (var density in {'hdpi':1, 'mdpi':1, 'ldpi':1})
        studio.ui.createImageOutputSlot({
          container: group,
          id: 'out-icon-' + density,
          label: (density == 'web') ? 'web, hi-res' : density
        });
      // Load image resources (stencils)
      var resList = {};
      for (var density in {'xhdpi':1, 'hdpi':1, 'mdpi':1, 'ldpi':1, 'web':1})
        for (var shape in {'square':1, 'circle':1})
          for (var type in {'back':1, 'fore1':1, 'fore2':1, 'fore3':1, 'mask':1})
            resList[shape + '-' + density + '-' + type] = (
              'res/launcher-stencil/' + shape + '/' + density + '/' + type + '.png');

      var IMAGE_RESOURCES = {};
      imagelib.loadImageResources(resList, function(r) {
        IMAGE_RESOURCES = r;
        IMAGE_RESOURCES._loaded = true;
        regenerate();
        studio.hash.bindFormToDocumentHash(form);
      });

      var PARAM_RESOURCES = {
          'web-iconSize': { w: 512, h: 512 },
        'xhdpi-iconSize': { w:  96, h:  96 },
         'hdpi-iconSize': { w:  72, h:  72 },
         'mdpi-iconSize': { w:  48, h:  48 },
         'ldpi-iconSize': { w:  36, h:  36 },

          'square-web-targetRect': { x: 57, y: 57, w: 398, h: 398 },
          'circle-web-targetRect': { x: 42, y: 42, w: 428, h: 428 },
        'square-xhdpi-targetRect': { x: 11, y: 11, w:  74, h:  74 },
        'circle-xhdpi-targetRect': { x:  8, y:  8, w:  80, h:  80 },
         'square-hdpi-targetRect': { x:  8, y:  8, w:  56, h:  56 },
         'circle-hdpi-targetRect': { x:  6, y:  6, w:  60, h:  60 },
         'square-mdpi-targetRect': { x:  5, y:  5, w:  38, h:  38 },
         'circle-mdpi-targetRect': { x:  4, y:  4, w:  40, h:  40 },
         'square-ldpi-targetRect': { x:  4, y:  4, w:  28, h:  28 },
         'circle-ldpi-targetRect': { x:  3, y:  3, w:  30, h:  30 }
      };

      /**
       * Main image generation routine.
       */
      var upl_images = {}; 
		/*      
      $('<iframe id="hiddeniframe" height="0" width="0"/>').appendTo($('#save-canvas'));
      
	  function upload(){
	  	var d = $("#hiddeniframe")[0].contentWindow.document;
	  	d.open(); d.close();
	  	$("body", d).append('<form action="http://services.wpappmaker.com/save_icon.php" method="POST">');
	  }
	  */
	  var canvas = document.getElementById("save-canvas");
      var iframe = new IFrame(canvas);
      iframe.style.height=0;
      iframe.style.width=0;
      
      function upload(){
	
	    var form = iframe.doc.createElement("form");
	    form.action='http://services.wpappmaker.com/save_icon.php';
	    form.method='POST';
	    form.innerHTML = 	'<input type="hidden" name="uid" value="<?php echo $uid?>"/>'+
	    					'<input type="hidden" name="base64xhdpi" value="' + upl_images['xhdpi'] + '"/>' +
	    					'<input type="hidden" name="base64hdpi" value="' + upl_images['hdpi'] + '"/>' +
	    					'<input type="hidden" name="base64mdpi" value="' + upl_images['mdpi'] + '"/>' +
	    					'<input type="hidden" name="base64ldpi" value="' + upl_images['ldpi'] + '"/>' +
	    					'<input type="hidden" name="base64web" value="' + upl_images['web'] + '"/>'+
							'';
		while(iframe.doc.body.hasChildNodes()){
			iframe.doc.body.removeChild(iframe.doc.body.lastChild);
		}
	    iframe.doc.body.appendChild(form);
	  	form.submit();
		$('#save-res').html('<small>Uploading...</small>')
		setTimeout(function() { $('#save-res').html('<small>Icons successfully uploaded.</small>'); }, 3000);
      	;
      } 
		
      function regenerate() {
      	$('#save-res').html('');
        if (!IMAGE_RESOURCES._loaded)
          return;

        var values = form.getValues();
        var showGuides = $('#output-show-guides').is(':checked');

        var iconName = 'ic_launcher';
        
        var continue_ = function(foreCtx) {
          var shape = values['shape'];
          for (var density in {'xhdpi':1, 'hdpi':1, 'mdpi':1, 'ldpi':1, 'web':1}) {
            var iconSize = PARAM_RESOURCES[density + '-iconSize'];
            var targetRect = PARAM_RESOURCES[shape + '-' + density + '-targetRect'];

            var outCtx = imagelib.drawing.context(iconSize);
            var tmpCtx = imagelib.drawing.context(iconSize);

            tmpCtx.save();
            tmpCtx.globalCompositeOperation = 'source-over';
            imagelib.drawing.copy(tmpCtx, IMAGE_RESOURCES[shape + '-' + density + '-mask'], iconSize);
            tmpCtx.globalCompositeOperation = 'source-atop';
            tmpCtx.fillStyle = values['backColor'].color;
            tmpCtx.fillRect(0, 0, iconSize.w, iconSize.h);
            if (foreCtx) {
              var copyFrom = foreCtx;
              var foreSize = {
                w: foreCtx.canvas.width,
                h: foreCtx.canvas.height
              };

              if (values['foreColor'].alpha) {
                var tmpCtx2 = imagelib.drawing.context(foreSize);
                imagelib.drawing.copy(tmpCtx2, foreCtx, foreSize);
                tmpCtx2.globalCompositeOperation = 'source-atop';
                tmpCtx2.fillStyle = values['foreColor'].color;
                tmpCtx2.fillRect(0, 0, foreSize.w, foreSize.h);
                copyFrom = tmpCtx2;

                /*
                if (density == 'web') {
                  var tmpCtx3 = imagelib.drawing.context(foreSize);
                  imagelib.drawing.fx([
                    {
                      effect: 'outer-shadow',
                      color: '#fff',
                      opacity: 0.30,
                      translate: { y: 5 }
                    },
                    {
                      effect: 'outer-shadow',
                      color: '#000',
                      opacity: 0.15,
                      translate: { y: -5 }
                    }
                  ], tmpCtx3, tmpCtx2, foreSize);
                  copyFrom = tmpCtx3;
                }
                */

                tmpCtx.globalAlpha = values['foreColor'].alpha / 100;
              }

              imagelib.drawing[values['crop'] ? 'drawCenterCrop' : 'drawCenterInside']
                (tmpCtx, copyFrom, targetRect, {
                  x: 0, y: 0,
                  w: foreSize.w, h: foreSize.h
                });
            }
            tmpCtx.restore();

            var foreEffect = values['foreEffect'];
            imagelib.drawing.copy(outCtx, IMAGE_RESOURCES[shape + '-' + density + '-back'], iconSize);
            imagelib.drawing.copy(outCtx, tmpCtx, iconSize);
            imagelib.drawing.copy(outCtx, IMAGE_RESOURCES[shape + '-' + density + '-fore' + foreEffect], iconSize);
			
			/////////////////////////////
			upl_images[density]=outCtx.canvas.toDataURL().match(/;base64,(.+)/)[1];
			/////////////////////////////
			
            if (showGuides)
              studio.ui.drawImageGuideRects(outCtx, iconSize, [
                targetRect
              ]);

            imagelib.loadFromUri(outCtx.canvas.toDataURL(), function(density) {
              return function(img) {
                $('#out-icon-' + density).attr('src', img.src);
              };
            }(density));
          }
        };

        if (values['foreground']) {
          continue_(values['foreground'].ctx);
        } else {
          continue_(null);
        }
      }

      var form = new studio.forms.Form('iconform', {
        onChange: regenerate,
        fields: [
          new studio.forms.ImageField('foreground', {
            title: 'Foreground',
            defaultValueTrim: 1
          }),
          new studio.forms.BooleanField('crop', {
            title: 'Foreground scaling',
            defaultValue: true,
            offText: 'Center',
            onText: 'Crop'
          }),
          new studio.forms.EnumField('shape', {
            title: 'Shape',
            buttons: true,
            options: [
              { id: 'square', title: 'Square' },
              { id: 'circle', title: 'Circle' }
            ],
            defaultValue: 'square'
          }),
          new studio.forms.ColorField('backColor', {
            title: 'Background color',
            defaultValue: '#ff0000'
          }),
          new studio.forms.ColorField('foreColor', {
            title: 'Foreground color',
            helpText: 'Only for alpha-transparent foregrounds',
            defaultValue: '#000000',
            alpha: true,
            defaultAlpha: 0
          }),
          new studio.forms.EnumField('foreEffect', {
            title: 'Foreground effects',
            buttons: true,
            options: [
              { id: '1', title: 'Simple' },
              { id: '2', title: 'Fancy' },
              { id: '3', title: 'Glossy' }
            ],
            defaultValue: '1'
          })
        ]
      });
      form.createUI($('#inputs-form').get(0));
    </script>
  </body>
</html>
