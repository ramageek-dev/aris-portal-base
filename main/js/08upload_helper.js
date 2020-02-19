/* NO concurrent process support!! I thought YES, it supports concurrent process. CMIIW. */
var upload_helper_pool = [];

function UploadHelper(form_id) {
   this.formId = form_id;
}

UploadHelper.prototype = {
   formId: null,
   uniqueId: null,
   hiddenDiv: null,
   formObject: function () {
      return document.getElementById(this.formId);
   },
   requireUploadingFile: function () {
      if (!this.formObject())
         return false;

      var upload_fields = 0;
      for(var i = 0; i < this.formObject().elements.length; i++) {
         if (this.formObject().elements[i].getAttribute('type')=='file')
            upload_fields++;
      }

      return (upload_fields > 0);
   },
   createHiddenIframe: function (callback_func) {
      if (!this.requireUploadingFile())
         return false;
         
      this.uniqueId = String(Math.random() * 1000).replace('.', '_');

      this.hiddenDiv = document.createElement('div');
      this.hiddenDiv.id = 'hidden_div_' + this.uniqueId;
      this.hiddenDiv.style.display = 'none';
      document.body.appendChild(this.hiddenDiv);

      var iframeHTML = '<iframe class="hidden_iframe_upload" name="hidden_iframe_' + this.uniqueId + '" ';
      iframeHTML += 'id="hidden_iframe_' + this.uniqueId + '" style="';
      iframeHTML += 'border-width: 1px;';
      iframeHTML += 'width: 100px;';
      iframeHTML += 'height: 100px;';
      iframeHTML += '" onload="javascript:upload_helper_pool[\'' + this.formId +
         '\'].callback(this, \'' + callback_func + '\');"></iframe>';
      //iframeHTML += '" onload="javascript:document.getElementById(\'' + this.formId +
      //   '\').uploadHelper.callback(this, \'' + callback_func + '\');"></iframe>';
      this.hiddenDiv.innerHTML = iframeHTML;
      
      return 'hidden_iframe_' + this.uniqueId;
   },
   defaultCallback: function(txt) {
      if (txt != ''){
         try{
            eval("result = " + txt + ";");
         }catch(e){
            alert(txt);
         }
      }

      if (result && result['exec']) {
         eval(result['exec'] + ";");
      }
   },
   callback: function(iframe, callback_func) {
      // loading status: off
      if (XhrStatus)
         XhrStatus.stop("loading-box-active", 100);

      if (iframe.contentDocument) {
         // For others (ns, ff, opera, etc)
         txt = iframe.contentDocument.body.childNodes[0].nodeValue;
      } else if (iframe.contentWindow) {
         // For IE5.5 and IE6
         txt = iframe.contentWindow.document.body.innerText;
      } else if (iframe.document) {
         // For IE5
         txt = iframe.document.body.innerText;
      } else {
         txt = '';
      }

      // delete hidden div
      window.setTimeout("$('.hidden_iframe_upload').parent().remove()", 500); // delay for ns, ff


      if (callback_func) {
         eval(callback_func + '(' + txt + ');');
      } else {
         this.defaultCallback(txt);
      }
   },
   deleteHiddenIframe: function() {
      document.body.removeChild(this.hiddenDiv);
   }
}
