/*
@copyright 		: PT Gamatechno Indonesia
@author 		: Didi Zuliansyah <didi@gamatechno.com>
@start date 	: 08-08-2012
@desciption		: Tab Helper support multi library
@Cara Pakai		:
	var testtab = new GTFWTab('dhtmlx','a_tabbar'); //lib,idMasterTab
	testtab.tabId = "a1,a2"; 						//id masing2 tab
	testtab.tabTitle = "Tab 1,Tab 2"; 				//Judul masing2 tab
	testtab.tabSize = "100,100";					//Ukuran masing2 tab
	testtab.tabContent = "divid1,divid2"; 			//id div html yang akan menjadi content
	testtab.tabDefault = "a1";						//id tab yang muncul pertama
	testtab.createTab();							//proses buat tab
*/

function GTFWTab(lib,idTab){
	var obj = new Object();
	
	// WAJIB DIISI
	
	obj.tabPosition = "top";
	obj.tabId = "a1,a2";
	obj.tabTitle = "Tab 1,Tab2";
	obj.tabSize = "100,100";
	obj.tabContent = null;
	obj.tabDefault = "a1";
	
	if (lib == 'dhtmlx'){
		var tabbar = new dhtmlXTabBar(idTab,obj.tabPosition);
	}
	
	obj.createTab = function(){
			tabbar.setSkin('silver');
			tabbar.setImagePath("css/dhtmlxTabbar/imgs/");
			var id = obj.tabId.split(',');
			var title = obj.tabTitle.split(',');
			var size = obj.tabSize.split(',');
			if ((id.length == title.length)&&(id.length == size.length)&&(title.length == size.length)){
				if (obj.tabContent != null) var content = obj.tabContent.split(',');
				else var content = [];
				for(var i=0;i<id.length;i++){
					tabbar.addTab(id[i],title[i],size[i]+'px');
					if (content.length == id.length)
						tabbar.setContent(id[i],content[i]);
				}
			}else
				alert('[ERROR] GTFWTab: Jumlah Array tabId,tabTitle, dan tabSize harus sama '+id.length+","+title.length+","+size.length);
			tabbar.setTabActive(obj.tabDefault);
	};
	
	return obj;
}
