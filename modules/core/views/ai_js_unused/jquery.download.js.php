$.download = function(url, data, method, callback){
    var inputs = '';
    var iframeX;
    var downloadInterval;

    if(url && data){
        // remove old iframe if has
        if($("#iframeX")) $("#iframeX").remove();
        // creater new iframe
        iframeX= $('<iframe src="[removed]false;" name="iframeX" id="iframeX"></iframe>').appendTo('body').hide();

        if (iframeX.attachEvent){
            iframeX.attachEvent("load", function(){
                callback();
            });
        } else {
            iframeX.load(function() {
                callback();
            });
        }

//        iframeX.ready(function(){
//            callback();
//        });

//        if($.browser.msie){
//            downloadInterval = setInterval(function(){
//               // if loading then readyState is "loading" else readyState is "interactive"
//                if(iframeX && iframeX[0].readyState !=="loading"){
//                    callback();
//                    clearInterval(downloadInterval);
//                }
//            }, 23);
//        } else {
//            iframeX.load(function(){
//                callback();
//            });
//        }

        //split params into form inputs
        $.each(data, function(p, val){
            inputs+='<input type="hidden" name="'+ p +'" value="'+ val +'" />';
        });

        //create form to send request
        $('<form action="'+ url + '" method="'+ (method||'post') + '" target="iframeX">'+inputs+'</form>').appendTo('body').submit().remove();
    }
};