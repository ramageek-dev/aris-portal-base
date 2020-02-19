<script language="JavaScript">
function dosubmit(form,url) {
   alert('tada!');
   if (url != "") {
      var hash;
      if (form.password.value) {
         hash = hex_md5(form.salt.value+hex_md5(form.password.value));
      } else {
         hash = "";
      }

      for (i=0; i<form.elements.length; i++) {
         if (form.elements[i].name == "login" ||
            form.elements[i].name.length <=0 ||
            form.elements[i].name == "salt") {
            continue;
         }
         url += "&";
         url += form.elements[i].name;
         url += "=";
         if (form.elements[i].name == "password") {
            url += hash;
         } else {
            url += escape(form.elements[i].value);
         }
      }
      // indicate the password is hashed.
      url += "&hash=1";
      location.href = url;
      // prevent from running this again. Allow the server response to submit the form directly
      form.onsubmit = null;
      form.salt.value = null;
      //alert(url);

      // abort normal form submission
      return false;
   }
   // allow normal form submission
   return true;
}
</script>