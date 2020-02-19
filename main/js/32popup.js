/*
@author     galih@gmail.com
@modify		didi@gamatechno.net
@version    0.2
@copyright  2009
@modified	2012
@lib : popup js require jquery.js, ui.core.js, ui.draggable.js, ui.resizable.js
@penggunaannya: showPopup(<url>,[<title>[,<widht>[,<height>[,<offsetLeft>[,<offsetTop>]]]]]);
@showPopup('http://localhost/gtFinansi/index.php', 'gtWindow Popup', 400, 300, 10, 20)
@perubahan	: penambahan lib agar support multi library dan penambahan dhtmlxwindows;
*/
var popupx;
var lib='dhtmlx';
var popCount = 1;
var dhxWins;

function popupClose(){
	if (lib == 'gtWindow'){
      var prnt = document.getElementById('body-application');
      var chld = document.getElementById('popup-container');
      $("#popup-container").fadeOut(300,function(){prnt.removeChild(chld);});
      $("#mainModal").hide();
	}
	
	if (lib == 'dhtmlx'){
//	  var p = 0;
	  popupx.close();
//	  dhxWins.forEachWindow(function(){p++;});
//	  if(p == 0){
	  $("#mainModal").hide();
//	  }
//	  dhxWins.forEachWindow(function(win){
//		  alert(win.idd);
//	  });
	  //FIXME : error when multi windows
	}
      
}

function createNewWindow(title, objwidth, objheight, xpos, ypos)
{
   if (document.getElementById('popup-container') != undefined) return true;
   var a = new jElement();
   
   a.theElement = 'div';
   a.setElementOn = 'body-application';
   a.style="width: "+objwidth+"px; height: "+objheight+"px; position: absolute; border: 1px solid #F9D54B; padding: 0px 0px 10px 0px; left:"+xpos+"px; top:"+ypos+"px; background: #FFFFEE;z-index:9999999";
   a.attribute = new Array('id|popup-container');
   a.createElement();
   
   a.theElement = 'div';
   a.setElementOn = 'popup-container';
   a.style="height: 24px; width: "+objwidth+"px; background: url(images/popup/popup_header.gif) repeat-x; float: left; cursor:move;";
   a.attribute = new Array('id|popup-header');
   a.createElement();
   
   
   a.theElement = 'div';
   a.setElementOn = 'popup-header';
   a.style="height: 24px; width: "+objwidth+"px; float: left;";
   a.attribute = new Array('id|popup-title');
   a.createElement();
   
   a.theElement = 'div';
   a.setElementOn = 'popup-title';
   a.style="float: left; width: 100%; font-size:12px; font-family: Arial, Helvetica, sans-serif; font-weight: bolder; padding: 5px 0px 5px 10px; margin: 0px 0px 0px 5px;";
   a.attribute = new Array('id|popup-title-text');
   a.createElement();
   a.theElement.innerHTML = title;
   
   a.theElement = 'img';
   a.setElementOn = 'popup-title';
   a.style="height: 19px; width: 19px; border-style: none; position: absolute; cursor: pointer; left:"+(objwidth-25)+"px; top:2px";
   a.attribute = new Array('id|popup-close','src|images/popup/icon-close.gif');
   a.createElement();
   a.theElement.onclick = function(e)
   {
      popupClose('gtWindow');
   }
   
   /*a.theElement = 'div';
   a.setElementOn = 'popup-header';
   a.style="background: url(images/popup/icon-alert.gif) no-repeat ; position:absolute; left:5px; top:27px; height: 30px; width: 30px;";
   a.attribute = new Array('id|popup-icon');
   a.createElement();
   a.theElement.style.bgImage = 'icon-alert.gif';
   */
   a.theElement = 'div';
   a.setElementOn = 'popup-header';
   a.style="float: right; height: 30px; width: 30px;";
   a.attribute = new Array('id|popup-close');
   a.createElement();
   
   a.theElement = 'div';
   a.setElementOn = 'popup-container';
   a.style="width: "+(objwidth)+"px; height: "+(objheight-20)+"px; padding:0px; font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #000000; float: right; overflow:auto;";
   a.attribute = new Array('id|popup-content');
   a.createElement();
   
   a.theElement = 'div';
   a.setElementOn = 'popup-content';
   a.style="width: "+(objwidth-28)+"px; padding:5px; font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #000000; float: left;";
   a.attribute = new Array('id|popup-subcontent');
   a.createElement();
   
   $("#popup-container").hide();
   $("#popup-container").fadeIn(300); 
   
   return true;
}

function showPopup(url, title, width, height, offsetleft, offsettop)
{	
	if(lib=='dhtmlx'){
		dhxWins = new dhtmlXWindows();
		dhxWins.setImagePath("css/dhtmlxWindows/imgs/");
		dhxWins.attachEvent("onContentLoaded", function() {
			Behaviour.apply;
		});
		dhxWins.attachEvent("onClose", function(){
//			var p = 0;
//			dhxWins.forEachWindow(function(){p++;});
//			if(p==0) 
				$("#mainModal").hide();
			return true;
		});
	}
	if (!url) return;
	if (!title) title="gtWindow Popup";
	   
	if (!parseInt(width)) width = 240;
	else width = parseInt(width);
	   
	if (!parseInt(height)) height = 400;
	else height = parseInt(height);
	   
	if (!parseInt(offsetleft)) offsetleft = 0;
	else offsetleft = parseInt(offsetleft);
	   
	if (!parseInt(offsettop)) offsettop = 0;
	else offsettop = parseInt(offsettop);
	   
	if (lib == 'gtWindow'){   
	   var scrollX = (document.documentElement) ? document.documentElement.scrollLeft : document.body.scrollLeft;
	   var scrollY = (document.documentElement) ? document.documentElement.scrollTop : document.body.scrollTop;
	   
	   var screenWidth = (document.documentElement) ? document.documentElement.clientWidth : document.body.clientWidth;
	   var screenHeight = (document.documentElement) ? document.documentElement.clientHeight : document.body.clientHeight;
	   
	   var xpos = scrollX + offsetleft + (screenWidth/2) - (width/2);
	   var ypos = scrollY + offsettop + (screenHeight/2) - (height/2);
	   
	   if(ypos<5) ypos = 5;
	   
	   var popup;
	   if (popup = document.getElementById("popup-container"))
	   {
	      $("#popup-title-text").html(title);
	      $("#popup-subcontent").html('');
	      $(popup).animate({left: xpos, top: ypos}, 300);
	   }
	   else
	   {
	      createNewWindow(title, width, height, xpos, ypos);
	      $("#popup-header").mousedown(function(){
	      $("#popup-container").draggable({opacity: 0.50});});
	      
	      $("#popup-header").mouseup(function(){
	      $("#popup-container").draggable( 'disable' );
	         if($('#popup-container').css('top').split('p')[0] < 0) $('#popup-container').css('top',0); 
	         if($('#popup-container').css('left').split('p')[0] < 0) $('#popup-container').css('left',0); 
	         if($('#popup-container').css('left').split('p')[0] > screenWidth-width) $('#popup-container').css('left',screenWidth-width-5);
	         
	      })
	   }	  
   }
   
   if (lib == 'dhtmlx'){
	var scrollX = (document.documentElement) ? document.documentElement.scrollLeft : document.body.scrollLeft;
	var scrollY = (document.documentElement) ? document.documentElement.scrollTop : document.body.scrollTop;
	   
	var screenWidth = (document.documentElement) ? document.documentElement.clientWidth : document.body.clientWidth;
	var screenHeight = (document.documentElement) ? document.documentElement.clientHeight : document.body.clientHeight;
	   
	var xpos = scrollX + offsetleft + (screenWidth/2) - (width/2);
	var ypos = scrollY + offsettop + (screenHeight/2) - (height/2);

	//var popupId = Math.floor(Math.random() * 1000);
	   
	if(ypos<5) ypos = 5;

	if (!dhxWins.isWindow('win1')) popupx = dhxWins.createWindow('win1', offsetleft, offsettop, width, height);
	popupx.setText(title);
	popupx.button('park').hide();
	popupx.button('minmax1').hide();
	popupx.button('minmax2').hide();
	//popupx.center();
	popupx.setPosition(xpos,ypos);
	popupx.attachHTMLString("<div id='popup-subcontent"+popCount+"' class='gtfw_multi_windows' style='padding:10px;'></div>");
	
//	dhxWins.createWindow(popCount, offsetleft, offsettop, width, height);
//	dhxWins.window(popCount).setText(title);
//	dhxWins.window(popCount).button('park').hide();
//	dhxWins.window(popCount).button('minmax1').hide();
//	dhxWins.window(popCount).button('minmax2').hide();
//	dhxWins.window(popCount).setPosition(xpos,ypos);
//	dhxWins.window(popCount).attachHTMLString("<div id='popup-subcontent"+popCount+"' class='gtfw_multi_windows' style='padding:10px;'></div>");
	
   }

   if($("#mainModal").length == 0){
	   var mbody = document.body,mhtml = document.documentElement;
	   var mheight = Math.max( mbody.scrollHeight, mbody.offsetHeight, mhtml.clientHeight, mhtml.scrollHeight, mhtml.offsetHeight );
	   var mWidth = Math.max( mbody.scrollWidth, mbody.offsetWidth, mhtml.clientWidth, mhtml.scrollWidth, mhtml.offsetWidth );
	   var zind;
	   $(document.body).append('<div class="mainModal" id="mainModal"></div>');
	   $('#mainModal').css('width',mWidth);
	   $('#mainModal').css('height',mheight);
	   $('#mainModal').css('background','#ffffff');
	   $('#mainModal').css('top','0');
	   $('#mainModal').css('left','0');
	   $('#mainModal').css('position','absolute');
	   $('#mainModal').css('opacity','0.4');
	   if($("#popup-container").length != 0) zind = $("popup-container").css("z-index");
	   else 
		   zind = popupx.zi;
//		   zind = dhxWins.window(popCount).zi;
	   $('#mainModal').css('z-index',zind-1);
   }else{
	   var mbody = document.body,mhtml = document.documentElement;
	   var mheight = Math.max( mbody.scrollHeight, mbody.offsetHeight, mhtml.clientHeight, mhtml.scrollHeight, mhtml.offsetHeight );
	   var mWidth = Math.max( mbody.scrollWidth, mbody.offsetWidth, mhtml.clientWidth, mhtml.scrollWidth, mhtml.offsetWidth );
	   $('#mainModal').css('width',mWidth);
	   $('#mainModal').css('height',mheight);
   	   $("#mainModal").show();
   }
   
   if(url!='pop'){
	   GtfwAjax.replaceContentWithUrl("popup-subcontent"+popCount,url+"&ascomponent=1");
	   popCount++;
   }
   
   
   return false;
}

function showConfirmation(text, url, title, width, height, offsetleft, offsettop)
{
   
   if (!title) title="gtWindow Popup";
   
   if (!parseInt(width)) width = 300;
   else width = parseInt(width);
   
   if (!parseInt(height)) height = 100;
   else height = parseInt(height);
   
   if (!parseInt(offsetleft)) offsetleft = 0;
   else offsetleft = parseInt(offsetleft);
   
   if (!parseInt(offsettop)) offsettop = 0;
   else offsettop = parseInt(offsettop);
   
   var scrollX = (document.documentElement) ? document.documentElement.scrollLeft : document.body.scrollLeft;
   var scrollY = (document.documentElement) ? document.documentElement.scrollTop : document.body.scrollTop;
   
   var screenWidth = (document.documentElement) ? document.documentElement.clientWidth : document.body.clientWidth;
   var screenHeight = (document.documentElement) ? document.documentElement.clientHeight : document.body.clientHeight;
   
   var xpos = scrollX + offsetleft + (screenWidth/2) - (width/2);
   var ypos = scrollY + offsettop + (screenHeight/2) - (height/2);
   
   if(ypos<5) ypos = 5;
   
   var popup;
   if (popup = document.getElementById("popup-container"))
   {
      $("#popup-subcontent").html('');
      $(popup).animate({left: xpos, top: ypos}, 300);
   }
   else
   {
      createNewWindow(title, width, height, xpos, ypos);
      $("#popup-header").mousedown(function(){
      $("#popup-container").draggable({opacity: 0.50});});
      
      $("#popup-header").mouseup(function(){
      $("#popup-container").draggable( 'disable' );
         if($('#popup-container').css('top').split('p')[0] < 0) $('#popup-container').css('top',0); 
         if($('#popup-container').css('left').split('p')[0] < 0) $('#popup-container').css('left',0); 
         if($('#popup-container').css('left').split('p')[0] > screenWidth-width) $('#popup-container').css('left',screenWidth-width-5);
         
      })
   }
   
   inHtml = text + '<div class="toolbar" align="right"><br />' + 
   	'<a class="xhr dest_subcontent-element" href="'+ url + 
   	'" id="confirm_yes"><img src="images/icon-done-16x16.png" alt="" >Yes</a><a id="confirm_no"><img src="images/icon-warning-16x16.gif" alt="" >No</a></div>';
   
   $("#popup-subcontent").html(inHtml);
   
   $('#confirm_no').click(function(){
	   popupClose();
   });
   
   $('#confirm_yes').click(function(){
	   popupClose();
	   GtfwAjax.replaceContentWithUrl("subcontent-element",url+"&ascomponent=1");
   });
   
   return false;
}

function showWarning(text, title, width, height, offsetleft, offsettop)
{
   if (!title) title="gtWarning";
   
   if (!parseInt(width)) width = 300;
   else width = parseInt(width);
   
   if (!parseInt(height)) height = 100;
   else height = parseInt(height);
   
   if (!parseInt(offsetleft)) offsetleft = 0;
   else offsetleft = parseInt(offsetleft);
   
   if (!parseInt(offsettop)) offsettop = 0;
   else offsettop = parseInt(offsettop);
   
   var scrollX = (document.documentElement) ? document.documentElement.scrollLeft : document.body.scrollLeft;
   var scrollY = (document.documentElement) ? document.documentElement.scrollTop : document.body.scrollTop;
   
   var screenWidth = (document.documentElement) ? document.documentElement.clientWidth : document.body.clientWidth;
   var screenHeight = (document.documentElement) ? document.documentElement.clientHeight : document.body.clientHeight;
   
   var xpos = scrollX + offsetleft + (screenWidth/2) - (width/2);
   var ypos = scrollY + offsettop + (screenHeight/2) - (height/2);
   
   if(ypos<5) ypos = 5;
   
   var popup;
   if (popup = document.getElementById("popup-container"))
   {
      $("#popup-subcontent").html('');
      $(popup).animate({left: xpos, top: ypos}, 300);
   }
   else
   {
      createNewWindow(title, width, height, xpos, ypos);
      $("#popup-header").mousedown(function(){
      $("#popup-container").draggable({opacity: 0.50});});
      
      $("#popup-header").mouseup(function(){
      $("#popup-container").draggable( 'disable' );
         if($('#popup-container').css('top').split('p')[0] < 0) $('#popup-container').css('top',0); 
         if($('#popup-container').css('left').split('p')[0] < 0) $('#popup-container').css('left',0); 
         if($('#popup-container').css('left').split('p')[0] > screenWidth-width) $('#popup-container').css('left',screenWidth-width-5);
         
      })
   }
   
   inHtml = '<font style="color:#FF0000; font-weight:bold;">'+ text + '</font> <div class="toolbar" style="width:'+(width/2)+'px"><br />' + 
   	'<a id="confirm_no" style="width:40px;"><img src="images/icon-warning-16x16.gif" alt="" >Ok</a></div>';
   
   $("#popup-subcontent").html(inHtml);
   $("#popup-subcontent").css('text-align','center');
   $("#popup-subcontent").css('margin-top',10);
   
   $('#confirm_no').click(function(){
	   popupClose();
   });
   
   return false;
}

function setBlurBackground(){
   var width = getWidth();
   var height = getHeight();
   
   var scrollX = (document.documentElement) ? document.documentElement.scrollLeft : document.body.scrollLeft;
   var scrollY = (document.documentElement) ? document.documentElement.scrollTop : document.body.scrollTop;
   
   var scrollWidth = (document.documentElement) ? document.documentElement.clientWidth : document.body.clientWidth;
   var scrollHeight = (document.documentElement) ? document.documentElement.clientHeight : document.body.clientHeight;
   
   var xpos = scrollX + (scrollWidth/2) - (150);
   var ypos = scrollY + (scrollHeight/2) - (85/2);
   
   a.theElement = 'div';
   a.setElementOn = 'body-application';
   a.attribute = new Array('id|bgApplication','name|bgApplication');
   a.style='background:#000000; width:100%;height:'+height+'px; position:absolute;top:0px;left:0px;z-index:999999';
   a.createElement();
}
