//---------images preloader jquery script----------//
/**
 * code source: http://engineeredweb.com/blog/09/12/preloading-images-jquery-and-javascript
 * @author Matt Farina (http://www.mattfarina.com/)
 */
(function(jQuery) {
  var cache = [];
  jQuery.preLoadImages = function(){
    var args_len = arguments.length;
    for (var i = args_len; i--;) {
      var cacheImage = document.createElement('img');
      cacheImage.src = arguments[i];
      cache.push(cacheImage);
    }
  }
})(jQuery);
//---------images preloader jquery script----------//