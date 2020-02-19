/*
@copyright 		: PT Gamatechno Indonesia
@author 		: Didi Zuliansyah <didi@gamatechno.com>
@Modified by  	: Dyan Galih <dyan.galih@gmail.com>
@start date 	: 18-07-2012
@modified date 	: 25-07-2012
@desciption		: Table Helper support multi library
@cara pakai		:
	var list = new GTFWTable('dhtmlx','gridbox'); 	//lib,id div
	list.header = "NO,Syarat Surat,Status,Aksi";	//header
	list.colWidth = "35,400,80,*";					//size header
	list.colAlign = "left,left,left,left";			//align kolom
	list.colTypes = "cntr,ro,ro,ro";				//type kolom
	list.pagingMaxRow = "20";						//maximum baris 1 page
	list.urlData = "http://sesuatu.com";			//load data ke grid dari URL tertentu
	list.createTable();								//exec pembuatan
*/

function GTFWTable(lib,idTable){
	var obj = new Object();
	
	// WAJIB DIISI
	
	obj.header = "Column1, Column2";
	obj.addHeader = null;
	obj.addFooter = null;
	obj.colWidth = "100,100";
	obj.colAlign = 'left,left';
	obj.colTypes = "ro,ro";
	obj.paging = true;
	obj.frameless = true;
	obj.pagingMaxRow = "20";
	obj.colSorting = null;
	obj.urlData = null;
	obj.bigDataRender = false;					//for BIG DATA
	obj.expandTree = false;
	obj.editTree = false;
	obj.groupBy = null;
	obj.groupByMask = null;
	obj.beforeCall = null; 						//callback function before data loaded (function must declared before)
    obj.afterCall = null;						//callback function after data loaded (function must declared before)
	obj.pagingCall = null;						//callback function after paging (function must declared before)
	obj.onMouseOver = null;
	obj.emptyLabel = 'Data Tidak Ditemukan'; 	//support html or img
	obj.loadingLabel = 'Loading Data...';		//support html or img
	obj.colspanEnable = false;
	obj.rowspanEnable = false;
	obj.debug = false;
	
	if (lib == 'dhtmlx'){
		var mygrid = new dhtmlXGridObject(idTable);
	}
	
	fixFramelessCss = function(){
		$('#'+idTable).css('height','auto');
		$('.objbox').css('height','auto');
		$('.ftr').css('position','static');
	}
	
	showEmptyNotif = function(){
		var countRow = mygrid.getRowsNum();
		if (countRow == 0) $("#empty_data_"+idTable).show();
		else $("#empty_data_"+idTable).hide();
	}
	
	obj.createTable = function(){
			mygrid.setImagePath("images/dhtmlxGrid/imgs/"); 		//path to images required by grid
			mygrid.setHeader(obj.header); 							//set column names
			if (obj.addHeader != null){					//set extra header
				if($.isArray(obj.addHeader)){
					var len = obj.addHeader.length;
					for(var i=0;i<len;i++){
						mygrid.attachHeader(obj.addHeader[i]);
					}
					}else{			
					mygrid.attachHeader(obj.addHeader);
				}
			}
			if (obj.addFooter != null){					//set extra footer
				if($.isArray(obj.addFooter)){
					var len = obj.addFooter.length;
					for(var i=0;i<len;i++){
						mygrid.attachFooter(obj.addFooter[i]);
					}
				}else{			
					mygrid.attachFooter(obj.addFooter);
				}
			}
			mygrid.setInitWidths(obj.colWidth); 					//set column width in px
			mygrid.setColAlign(obj.colAlign); 						//set column values align
			mygrid.setColTypes(obj.colTypes); 						//set column types
			if (obj.colSorting != null){							//set sorting
				mygrid.setColSorting(obj.colSorting);
			}
			mygrid.setSkin("dhx_sia_custom"); 						//set grid skin
			var pagingId = 'paging_'+idTable;
			if (document.getElementById('pagingArea')) {
				$('#pagingArea').attr('id',pagingId);
			}else{
				$('.pageBar').prepend('<div class="pageNav" style="margin: -8px 0 0 -10px;"><div id="'+pagingId+'"></div></div>');
			}
			mygrid.enablePaging(obj.paging, obj.pagingMaxRow, null, pagingId, true, "recinfoArea"); //set paging
			mygrid.enableMultiline(true);
			if (obj.frameless){
				mygrid.enableAutoWidth(true);
				mygrid.enableAutoHeight(true);
				mygrid.setSizes();
			}
			
			//GTFW paging
			mygrid._pgn_custom=function(page,start,end){
			  var total = mygrid.getRowsNum();
			  var lastNum = end;
			  if (total > 0)
				  var firstNum = start+1;
			  else
				  var firstNum = start;
			  var lastPage = total/obj.pagingMaxRow;
			  lastPage = Math.ceil(lastPage);
	          var html='';
			  
	          //View HTML
	          if (lastPage > 1){
	        	  if (page == 1){
	        		  html='<div>&lt;&lt;</div><div>&lt;</div><div class="pageInfo"><b>'+firstNum+'</b>-<b>'+lastNum+'</b> dari <b>'+total+'</b></div><a id="next_page">&gt;</a><a id="last_page">&gt;&gt;</a>';
	        	  }else{
	        		  if (page != lastPage)
	        			  html='<a id="first_page">&lt;&lt;</a><a id="prev_page">&lt;</a><div class="pageInfo"><b>'+firstNum+'</b>-<b>'+lastNum+'</b> dari <b>'+total+'</b></div><a id="next_page">&gt;</a><a id="last_page">&gt;&gt;</a>';
	        		  else
	        			  html='<a id="first_page">&lt;&lt;</a><a id="prev_page">&lt;</a><div class="pageInfo"><b>'+firstNum+'</b>-<b>'+lastNum+'</b> dari <b>'+total+'</b></div><div>&gt;</div><div>&gt;&gt;</div>';
	        	  }
	          }else{
	        	  html='<div>&lt;&lt;</div><div>&lt;</div><div class="pageInfo"><b>'+firstNum+'</b>-<b>'+lastNum+'</b> dari <b>'+total+'</b></div><div>&gt;</div><div>&gt;&gt;</div>';
	          }
	          //html = html + '<input type="hidden" name="page_no" id="page_no" value="'+page+'"><input type="hidden" name="page_start" id="page_start" value="'+firstNum+'">';
			  mygrid._pgn_parentObj.innerHTML=html;
			  
			  applyPage = function(){
				  if (obj.frameless) fixFramelessCss();
				  ba.removeButton();
				  Behaviour.apply();
			  };
			  
			  //Function
			  $('#first_page').click(function(){
				  mygrid.changePage(1);
				  applyPage();
				  if(obj.pagingCall) obj.pagingCall();
			  });
			  $('#prev_page').click(function(){
				  mygrid.changePageRelative(-1);
				  applyPage();
				  if(obj.pagingCall) obj.pagingCall();;
			  });
			  $('#next_page').click(function(){
				  mygrid.changePageRelative(1);
 				  applyPage();
				  if(obj.pagingCall) obj.pagingCall();;
			  });
			  $('#last_page').click(function(){
	        	  mygrid.changePage(lastPage);
				  applyPage();
				  if(obj.pagingCall) obj.pagingCall();
			  });
			}
			mygrid.setPagingSkin("custom");
			
			mygrid.attachEvent("onXLS", function() { 				//event saat proses parse data berlangsung
				//$("#loading-box-active").fadeTo(300,100);
				if (obj.frameless) fixFramelessCss();
				$("#empty_data_"+idTable).hide();
				$("#load_notif_"+idTable).show();
				if(obj.beforeCall) obj.beforeCall();
			});
			mygrid.attachEvent("onXLE", function() { 				//event setelah proses data selesai
				//$("#loading-box-active").fadeTo(300,0);
				$("#load_notif_"+idTable).hide();
				showEmptyNotif();
				if (obj.frameless) fixFramelessCss();
				if (obj.expandTree) mygrid.expandAll();
				mygrid.enableTreeCellEdit(obj.editTree);
				if(obj.afterCall) obj.afterCall();
				ba.removeButton();
				Behaviour.apply();
				if(!obj.debug){
					if(console.clear!=undefined) console.clear();
					if(window.clear!=undefined) window.clear();
				}
			});
			mygrid.attachEvent("onMouseOver", function(id,ind){			//agar tooltip show
				if(obj.onMouseOver) obj.onMouseOver(id,ind);
				return false;
			});
			mygrid.attachEvent("onKeyPress",onKeyPressed);
			var clip;
			function onKeyPressed(code,ctrl,shift){
				if(code==67&&ctrl){
					if($('.cellselected').length !== 0) clip = $('.cellselected').html();
				}
				if(code==86&&ctrl){
					alert(clip);
				}
				return true;
			}
			mygrid.submitOnlyChanged(false);						//submit All rows
			mygrid.enableCollSpan(obj.colspanEnable);				//colspan
			if(obj.rowspanEnable) mygrid.enableRowspan();				//rowspan (next dev)
			mygrid.entBox.onselectstart = function(){return true;};
			mygrid.init(); 											//initialize grid
			
			if(obj.bigDataRender) mygrid.enableSmartRendering(true);	//render BIG DATA
			
			applyPage = function(){
				if(!!obj.groupBy) mygrid.groupBy(obj.groupBy,obj.groupByMask);
				ba.removeButton();
				Behaviour.apply();
				if(obj.frameless) fixFramelessCss();
			};
			
			// create load notif and empty data notification
			var notifHtml = '<div id="load_notif_'+idTable+'" class="load_notif" style="display:none;color:red;padding:4px;">'+obj.loadingLabel+'</div>',
			emptyHtml = '<div id="empty_data_'+idTable+'" style="display:none;padding-top:5px;padding-bottom:5px;text-align:center;vertical-align:middle;border-right:1px solid #FDF1C1;border-bottom:1px solid #FDF1C1;" class="empty_notif"><em>--'+obj.emptyLabel+'--</em></div>';
			$('.obj','#'+idTable).after(notifHtml+emptyHtml);
			
			if (!!obj.urlData){
				mygrid.load(obj.urlData,applyPage,"json");
			}else{
				$('#empty_data_'+idTable).show();
				//if(obj.afterCall) obj.afterCall();
			}
			
	};
	
	obj.filterBy =function(column, value, preserve){				//column dan value bisa berupa array
		if (($.isArray(value))&&($.isArray(column))){
			var len = column.length;
			for(var i=0;i<len;i++){
				if (i == 0)
					mygrid.filterBy(column[i],value[i]);
				else
					mygrid.filterBy(column[i],value[i],true);
			}
		}else{
			mygrid.filterBy(column, value, preserve);
		}
		mygrid.resetCounter(0);
		if (obj.frameless) fixFramelessCss();
		showEmptyNotif();
		if(!!obj.groupBy) mygrid.groupBy(obj.groupBy,obj.groupByMask);
        ba.removeButton();
		Behaviour.apply();
	};
	
	obj.currentPage = function(){
		var page = new Array();
		var itemView = mygrid.rowsBufferOutSize;
		page[0] = mygrid.currentPage;
		page[1] = ((itemView*page[0])-itemView)+1;
		page[2] = itemView*page[0];
		page[3] = itemView;
		return page; //array : [0] current page,[1] start number, [2] end number ,[3] item view
	};
	
	obj.resetCounter = function(Column){
		mygrid.resetCounter(Column);
	};
	
	obj.parse = function(data,callback,format){
		mygrid.parse(data,callback,format);
	};
	
	obj.addRow = function(id,dataArr){
		if($("#empty_data_"+ idTable).length) $("#empty_data_"+ idTable).hide();
		mygrid.addRow(id,dataArr);
	};
	
	obj.deleteRow = function(id){
		mygrid.deleteRow(id);
		showEmptyNotif();
	};
	
	obj.clearAll = function(){
		mygrid.clearAll();
	};
	
	obj.searchTable = function(keyString){
			mygrid.clearAll();
			applyPage = function(){
				if(!!obj.groupBy) mygrid.groupBy(obj.groupBy,obj.groupByMask);
				ba.removeButton();
				Behaviour.apply();
				if(obj.frameless) fixFramelessCss();
			};
			mygrid.load(obj.urlData+'&cari='+keyString,applyPage, "json");			
	}
	
	obj.prepare = function (){
		mygrid.parentFormOnSubmit();
	}
	
	obj.getSubmitRowId = function(){
		return mygrid.getAllItemIds();
	}
	
	obj.makeSearch = function(id,column){
		mygrid.makeSearch(id, column);
	}
	
	obj.findCell = function(value,index,flag){
		mygrid.findCell(value,index,flag);
	}
	
	obj.countRow = function(){
		return mygrid.getRowsNum();
	}
	
	obj.hideColumn = function(columnIdx){
		if($.isArray(columnIdx)){
			var len = columnIdx.length;
			for(var i=0;i<len;i++){
				mygrid.setColumnHidden(columnIdx[i],true);
			}
		}else{
			mygrid.setColumnHidden(columnIdx,true);
		}
	}
	
	obj.setColspan = function(rowId,colId,colspan){
		mygrid.setColspan(rowId,colId,colspan);
	}

	obj.setRowspan = function(rowId,colId,rowspan){
		mygrid.setRowspan(rowId,colId,rowspan);
	}

	obj.forEachRow = function(custom_code){
		mygrid.forEachRow(custom_code);
		ba.removeButton();
		Behaviour.apply();
	}

    obj.setRowColor = function(rowId,color){
		mygrid.setRowColor(rowId,color);
	}
	
	obj.cellObjById = function(rId,colId){
		return mygrid.cellById(rId,colId);
	}

	obj.cellById = function(rowId,colIdx){
		return mygrid.cellById(rowId,colIdx).getValue();
	}

	obj.setCellValue = function(rowId,colIdx,value){
		mygrid.cellById(rowId,colIdx).setValue(value);
	}
	
	obj.getCellAttr = function(rowId,colIdx,attrName){
		return mygrid.cells(rowId,colIdx).getAttribute(attrName);
	}
	
	obj.setCellAttr = function(rowId,colIdx,attrName,value){
		mygrid.cells(rowId,colIdx).setAttribute(attrName,value);
	}
	
	obj.setCellTitle = function(rowId,colIdx,title){
		mygrid.cells(rowId,colIdx).cell.title = title;
	}
	
	obj.enableTooltips = function(list){
		mygrid.enableTooltips(list);
	}

	obj.insertColumn = function(arrData,callback){
		if($.isArray(arrData)){
			var len = arrData.length;
			for(var i=0;i<len;i++){
				mygrid.insertColumn(arrData[i][0],arrData[i][1],arrData[i][2],arrData[i][3]);//ind,header,type,width			
			}		
			if(callback) callback();
		}
	}

	obj.changePage = function(pageNum){
		mygrid.changePage(pageNum);
	}
	
	/* ============ Tree Grid Function =============== */
	
	obj.expandAll = function(){
		mygrid.expandAll();
	}
	
	obj.filterTreeBy = function(column,value,preserve){
		mygrid.filterTreeBy(column,value,preserve);
	}
	
	obj.setFilterLevel = function(level){
		mygrid.setFiltrationLevel(level);
	}
	
	obj.disableTreeEdit = function(){
		mygrid.enableTreeCellEdit(false);
	}
	
	/*obj.fieldName = function(mask){
		mygrid.setFieldName(mask);
	}
	
	obj.firstPage = function(){
		mygrid.changePage(0);
	}
	
	obj.lastPage = function(){
		var count = 20;
		var maxPage = (count/obj.pagingMaxRow);
		mygrid.changePage(maxPage);
	}
	*/
	
	return obj;
}

//REST helper
function showNotif(msg,css,id){
	id=(id!=undefined && id!='')?id:'msgbox';
	css=(css!=undefined && css!='')?css:'notebox-alert';
	var cls = $('#'+id).attr('class');
	if(cls!=undefined && id!=''){
		cls = cls.split(" ");
		for(var a in cls) if(cls[a].indexOf('notebox-')>-1) $('#'+id).removeClass(cls[a]);
	}
	$('#'+id).html(msg);
	$('#'+id).addClass(css).show();
}

//dataJSONRest = '';
function parseData(data,url,notif,id,idn){
	//dataJSONRest='';
	notif = (notif!=undefined)?notif:false;
	var idObject = (id!=undefined && id!='')?id:'subcontent-element';
	data = JSON.parse(data.responseText);
	if(data.gtfwResult){
		dataJson = data.gtfwResult;
		if(dataJson.status == '200'||dataJson.status == '201'||dataJson.status == '406'){ //old 201,419
			if(url==undefined||url==''){if(notif)showNotif(dataJson.message,dataJson.data,idn);return dataJson.data;} //return data
			//if(url==undefined || typeof url == 'function'){dataJSONRest = dataJson.data;if(typeof url == 'function') url(dataJSONRest);} //return data
			else GtfwAjax.replaceContentWithUrl(idObject,url+'&ascomponent=1');
		}else if(notif) showNotif(dataJson.message,dataJson.data,idn);
	}else if(notif)showNotif('Invalid Format','notebox-warning',idn);
}

function createComboRest(idObj,cbName,data,selectId,showAll,att){
	att = (att!=undefined)?att:'';
	var select = '<select id="'+cbName+'" name="'+cbName+'" '+att+'>';
	showAll = (showAll!=undefined && showAll!='')?showAll:false;
	var option = (showAll)?'<option value="all">-- SEMUA -- </option>':'<option value="">-- PILIH --</option>';
	if(data != undefined){
		//dataJson = parseData(data);
		dataJson = (data.responseText != undefined && data.responseText.indexOf('gtfwResult') > -1) ? parseData(data):data;
		for(var k in dataJson){
			selected = (selectId!=undefined && selectId!='' && dataJson[k].id == selectId)?'selected="selected"':'';
			option += '<option value="'+dataJson[k].id+'" '+selected+'>'+dataJson[k].name+'</option>';
		}
	}
	select += option+'</select>';
	$('#'+idObj).html(select);
}

function selectComboRest(id,val){
	var objSelect = document.getElementById(id);
	for (var i = 0; i < objSelect.options.length; i++) {
        if (objSelect.options[i].value == val) {
        	objSelect.options[i].selected = true;
            return;
        }
    }
}

function getUrlValues(url){
	var search = url.split('?');
	search = search[1];
	var values = {};
	if (search.length) {
	    var part, parts = search.split('&');
	    for (var i=0, iLen=parts.length; i<iLen; i++ ) {
	      part = parts[i].split('=');
	      values[part[0]] = window.decodeURIComponent(part[1]);
	    }
	}
	return values;
}

function confirmDelete(url,label,callback){
	
	createContent = function(){
		var params = getUrlValues(url);
		var emptyDataHtml = '<div class="dialogbox" id="dialog_delete" style="width:350px;"><img style="margin-top:15px;" src="images/alert.gif" alt="" /><p>Anda Harus Memilih Salah Satu Data Untuk Dihapus.<br/>Silakan Kembali.</p><ul class="taskdialog"><li><a onclick="popupClose();" title="Batal">Kembali</a><p>Batal menghapus dan menutup dialog ini.</p></li></ul></div>';
		var jsScript = document.createElement("script");
		if(callback=='' || callback==undefined){
			jsScript.type = 'text/javascript';
			jsScript.text = '$(document).ready(function(){resultRest = function(data){parseData(data,"",true);find();popupClose();}})';
			callback = 'resultRest';
		} 
		var htmlEl = new Array();
		htmlEl[0] = jsScript;
		
		if((params['id']!=undefined && params['id']!='') || $("input[name='id[]']").length){
			var deleteUrl = params['urlDelete'];
			var lab = (params['label']!=undefined)?params['label']:'';
			var msg = (params['message']!=undefined)?params['label']:'';
			var delUrl = deleteUrl.split('-');
			var newUrl = delUrl[0].split('|');
			var par='',val='',add='';
			if(delUrl[1]!='') par = delUrl[1].split('|');
			if(delUrl[2]!='') val = delUrl[2].split('|');
			if(par.length && val.length){for(var i=0;i<par.length;i++) add += '&'+par[i]+'='+val[i];}

			var urlDelete = url.split('?')[0]+'?mod='+newUrl[0]+'&sub='+newUrl[1]+'&act='+newUrl[2]+'&typ='+newUrl[3]+add;
			var addClass = (newUrl[3]=='rest')?'call_'+callback+' typ_rest':'';
			htmlEl[1] = '<div id="msgbox_pop" style="display:none" onclick="$(this).fadeOut();"></div>';
		
			htmlEl[1] += '<form name="form_distribusi" id="form_distribusi" action="'+urlDelete+'" method="POST" class="xhr_form std_form '+addClass+'"><div class="dialogbox" id="dialog_delete"><img src="images/alert.gif" alt="" /><h2>Menghapus '+lab+'</h2><p>Anda akan menghapus '+lab+' dengan nama / kode:<br />';
			if(params['id']!=undefined && params['id']!=''){
				htmlEl[1] += '<div style="margin-bottom:10px;"><input type="hidden" value="'+params['id']+'" id="idDelete" name="idDelete" /><b>'+params['dataName']+'</b></div>';
			}else if($("input[name='id[]']").length){
				var no=0;
				htmlEl[1] += '<div id="multiple" style="margin-bottom:10px;">';
				$("input[name='id[]']").each(function(){
					if(this.checked){
						var id = this.value; var dataName = $("input[name='name["+id+"]']").val();
						htmlEl[1] += '<input type="hidden" value="'+id+'" id="idDelete" name="idDelete[]"/><b>'+(no+1)+'. '+dataName+'</b><br>';
						no++;
					}
				});
				htmlEl[1] += '</div>';
				if(no==0){ htmlEl[1]= emptyDataHtml;return htmlEl;}
			};
			htmlEl[1] += '<div style="padding:5px 0px 5px 35px; margin-bottom:10px; background:url(images/alert.gif) left center no-repeat; color:red; font-weight:bold;">Apakah anda yakin? '+msg+'</div></p><ul class="taskdialog"><li><input type="submit" name="btnDel" class="button" value="Hapus" /><p>Data yang sudah dihapus tidak dapat dikembalikan lagi.</p></li><li><a onclick="popupClose();" title="Batal">Batal</a><p>Batal menghapus dan menutup dialog ini.</p></li></ul></div></form>';
			return htmlEl;
		}else return emptyDataHtml;
	};

	showPopup('pop','Konfirmasi Hapus '+label,500,500);
	//el = $('.gtfw_multi_windows');
	var el = $("div[id*='popup-subcontent']");
	if(el.length > 0){
		var contents = createContent();
		if(el.length == 1 && el.html() == ''){
			var id = el.attr('id');
			document.getElementById(id).appendChild(contents[0]);
			el.append(contents[1]);
			ba.removeButton();Behaviour.apply();
		}else if(el.length > 1){
			el.each(function(){
				if(this.html() == ''){
					var id = el.attr('id');
					document.getElementById(id).appendChild(contents[0]);
					el.append(contents[1]);
					ba.removeButton();Behaviour.apply();
					return;
				}
			});
		}
	}
}

function logout(url){
	resultRest = function(data){
		data = parseData(data);
		window.location.href = data;
	};
	if(url!='' && url!=undefined){
		var params = getUrlValues(url);
		if(params['typ']=='rest') GtfwAjax.loadUrl(url,resultRest,"rest");
		else window.location.href = url;
	}
}