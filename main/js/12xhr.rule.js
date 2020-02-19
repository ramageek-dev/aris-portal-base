var xhr_content = {
   '.xhr': function(element) {
      element.onclick = function() {
         /**
         load URl and replace content of and element
         Customization classname:
            dest_elementname: element elementname will be replaced with url response
            typ_customtyp: override current typ in URL
         */
         var dest = /\sdest_(.*?)\s/.exec(' '+this.className+' '); ///FIXME: always spare a space to class name!!
         var url = this.getAttribute('href');
         /*hack for disable url @the page*/
         if(!url){
				
				var keyUrl = /\saction_(.*?)\s/.exec(' '+this.className+' ');
				
				//data = objListUrl.getUrlList();
				
	//         for(var a in data){
	//        	 alert(a);
	//        	 alert(data[a]);
	//         }
				
				url=objListUrl.getUrlById("action_"+keyUrl[1]);	
			}
         var custom_type = /\s+typ_(.*?)\s+/.exec(' '+this.className+' '); ///FIXME: always spare a space to class name!!


         var regexp = new RegExp('/typ=' + custom_type + '/');
         if (!regexp.test(url)) {
            // replace typ
            var scriptString = url.replace(/(.*)\?.*/, "$1");
            var parameterString = url.replace(/.*\?(.*)/, "$1");
            var parameterTokens = parameterString.split("&");
            var parameterList = new Array();

            for (var j = 0; j < parameterTokens.length; j++) {
               if (/typ=.*/.test(parameterTokens[j])) {
                  if (custom_type)
                     parameterTokens[j] = 'typ=' + custom_type[1];
               }
            }

            url = scriptString + '?' + parameterTokens.join('&');
         }
         //alert(url);alert(dest);alert(this.className);
         if (dest && (url != '#')) {
            GtfwAjax.replaceContentWithUrl(dest[1], url + '&ascomponent=1');
         } else
         if (!dest && (url != '#')) {
            GtfwAjax.loadUrl(url + '&ascomponent=1');
         }

         return false; // stop here
      }
   },
   '.xhr_callback': function(element) {
      element.onclick = function() {
         /**
         load URl and call callback if any
         Customization classname:
            call_callbackfunc: call callbackfunc after loading
            typ_customtyp: override current typ in URL
         */
         // client side callback
         var callback = /\scall_(.*?)\s/.exec(' '+this.className+' '); ///FIXME: always spare a space to class name!!
         var custom_type = /\styp_(.*?)\s/.exec(' '+this.className+' '); ///FIXME: always spare a space to class name!!
         var url = this.getAttribute('href');
         if(!url){
				var keyUrl = /\saction_(.*?)\s/.exec(' '+this.className+' ');
				
				url=objListUrl.getUrlById("action_"+keyUrl[1]);
			}
         var json_mode = 'html';

         var regexp = new RegExp('/typ=' + custom_type + '/');
         if (!regexp.test(url)) {
            // replace typ
            var scriptString = url.replace(/(.*)\?.*/, "$1");
            var parameterString = url.replace(/.*\?(.*)/, "$1");
            var parameterTokens = parameterString.split("&");
            var parameterList = new Array();

            for (var j = 0; j < parameterTokens.length; j++) {
               if (/typ=.*/.test(parameterTokens[j])) {
                  if (custom_type)
                     parameterTokens[j] = 'typ=' + custom_type;
                  else {
                     parameterTokens[j] = 'typ=json';
                     json_mode = 'json';
                  }
                  break;
               }
            }

            url = scriptString + '?' + parameterTokens.join('&');
         }

         if (callback) {
            GtfwAjax.loadUrl(url, callback[1], json_mode);
         } else {
            GtfwAjax.loadUrl(url, false, json_mode);
         }

         return false; // stop here
      }
   },
   '.xhr_simple_form': function(element) {
      element.onsubmit = function() {
         /**
         post form and replace content of and element
         Customization classname:
            dest_elementname: element elementname will be replaced with url response
         */
         var dest = /\sdest_(.*?)\s/.exec(' '+this.className+' '); ///FIXME: always spare a space to class name!!
         var url = this.getAttribute('action');
         var keyUrl = /\saction_(.*?)\s/.exec(' '+this.className+' ');
         if(keyUrl){
				url=objListUrl.getUrlById("action_"+keyUrl[1]);
			}
         // check wetehr this form has id, which is mandatory
         if (!this.id) {
            if (this.name)
               this.setAttribute('id', this.name + '_' + String(Math.random() * 1000).replace('.', '_'));
            else {
               alert('This form doesn\'t have name nor id, please fix your template or disable javascript');

               return false;
            }
         }

         if (dest) {
            this.action = url + '&ascomponent=1';

            // be aware of FormHelper existence
            var submittable = true;
            if (this.canSubmit)
               submittable = this.canSubmit();

            if (submittable)
               GtfwAjax.postFormReplace(this.id, dest[1]);
         }

         return false; // stop here
      }
   },
   '.xhr_form': function(element) {
      element.onsubmit = function() {
    	  
         /**
         post form and call callback if any
         Customization classname:
            call_callbackfunc: call callbackfunc after loading
            typ_customtyp: override current typ in URL
         */
         // client side callback
         var callback = /\scall_(.*?)\s/.exec(' '+this.className+' '); ///FIXME: always spare a space to class name!!
         var custom_type = /\styp_(.*?)\s/.exec(' '+this.className+' '); ///FIXME: always spare a space to class name!!
         var url = this.action;
         
         var keyUrl = /\saction_(.*?)\s/.exec(' '+this.className+' ');
         if(keyUrl){
				url=objListUrl.getUrlById("action_"+keyUrl[1]);
		 }
         
         var json_mode = false;

         if (custom_type) {
            var regexp = new RegExp('/typ=' + custom_type[1] + '/');
            if (!regexp.test(url)) {
               // replace typ
               var scriptString = url.replace(/(.*)\?.*/, "$1");
               var parameterString = url.replace(/.*\?(.*)/, "$1");
               var parameterTokens = parameterString.split("&");
               var parameterList = new Array();

               for (var j = 0; j < parameterTokens.length; j++) {
                  if (/typ=.*/.test(parameterTokens[j])) {
                     if (custom_type)
                        parameterTokens[j] = 'typ=' + custom_type[1];
                     else {
                        //parameterTokens[j] = 'typ=json';
                        json_mode = true;
                     }
                     break;
                  }
               }

               this.action = scriptString + '?' + parameterTokens.join('&');
            }
         } else {
            json_mode = true;
         }

         // be aware of FormHelper existence
         var submittable = true;

         if (this.canSubmit) {
            submittable = this.canSubmit();
         }

         // check wetehr this form has id, which is mandatory
         if (!this.id) {
            if (this.name)
               this.setAttribute('id', this.name + '_' + String(Math.random() * 1000).replace('.', '_'));
            else {
               alert('This form doesn\'t have name nor id, please fix your template or disable JavaScript');
               return false;
            }
         }

         if (submittable) {
            this.action=url;
            var uh = new UploadHelper(this.id);
            if (uh.requireUploadingFile()) {
               //this.uploadHelper = null;
               upload_helper_pool[this.id] = null;

               if (callback){
                  callback = callback[1];
               } else {
                  callback = '';
               }

               if (json_mode) {
                  document.cookie = 'GtfwModuleType=json;';
               }
               this.target = uh.createHiddenIframe(callback); // make iframe as target
               //this.uploadHelper = uh;
               upload_helper_pool[this.id] = uh; // preserve upload helper, waiting for a callback

               // loading status: on
               if (XhrStatus) {
                  XhrStatus.start('loading-box-active', 50);
               }

               return true; // submit the form, to prevents missing submitted value
            } else {
               var type = null;
               if (json_mode) {
                  type = 'json';
               }
               
               if(custom_type!=null){
            	   type = custom_type[1];
               }
               
               if (callback) {
                  GtfwAjax.postForm(this.id, callback[1], type);
               } else {
                  GtfwAjax.postForm(this.id, false, type);
               }
               return false; // no need to submit stop here
            }
         }

      }
   }
}

Behaviour.register(xhr_content);
