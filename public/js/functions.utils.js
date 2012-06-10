
// shim layer with setTimeout fallback
// (Paul Irish method http://paulirish.com/2011/requestanimationframe-for-smart-animating/)
window.requestAnimFrame = (function(){
  return  window.requestAnimationFrame       || 
          window.webkitRequestAnimationFrame || 
          window.mozRequestAnimationFrame    || 
          window.oRequestAnimationFrame      || 
          window.msRequestAnimationFrame     || 
          function(/* function */ callback, /* DOMElement */ element){
            window.setTimeout(callback, 1000 / 60);
          };
})();


/**
 * Set a new cookie
 * @src http://www.w3schools.com/js/js_cookies.asp
 * @function
 * @param {String} c_name
 * @param {Mixed} value
 * @param {Number} exday
 */
function setCookie(c_name, value, exdays) {
	var exdate=new Date();
	exdate.setDate(exdate.getDate() + exdays);
	var c_value = escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
	document.cookie=c_name + "=" + c_value;	
}


/**
 * Get a cookie
 * @src http://www.w3schools.com/js/js_cookies.asp
 * @function
 * @param {String} c_name
 * @return 
 */
function getCookie(c_name) {
	var i,x,y,
		ARRcookies = document.cookie.split(";");
	
	for (i=0; i<ARRcookies.length;i++) {
		
		x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
		y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
		x=x.replace(/^\s+|\s+$/g,"");

		if (x==c_name) return unescape(y);
		
	}
}
