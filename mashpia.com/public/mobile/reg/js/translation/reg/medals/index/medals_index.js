 
var jsonhe = (function(){
  var json = null;
  $.ajax({
    'async':false,
    'global':false,
    'url':'../../js/translation/reg/medals/index/lang/he.json',
    'dataType':"json",
    'success':function(data){
        json=data;
    }
    
  });
  return json; 
})(); 

var jsonen = (function(){
  var json = null;
  $.ajax({
    'async':false,
    'global':false,
    'url':'../../js/translation/reg/medals/index/lang/en.json',
    'dataType':"json",
    'success':function(data){
        json=data;
    }
    
  });
  return json; 
})(); 

var he = jsonhe;
var en = jsonen;    

 
    $(function () {

        /// example number 1 - static translation 
        i18next.init({

            lng: (localStorage.getItem("locallang"))?localStorage.getItem("locallang"):'en',

            debug: true,

            resources: {

                en: {

                    translation: en,
                },

                he: {

                    translation: he
                }

            }

        }).then(function (t) { 
            // initialized and ready to go!

            translate()

        });
        
         

        $('.translate').click(function() {
            var lang = $(this).attr('id');
            
            localStorage.setItem("locallang", lang);

            i18next.changeLanguage(lang);
            translate();      
        })

    

       
    })



    function translate() {

        $(".i18n").each(function () {

            const key = $(this).data('key')

            $(this).text(i18next.t(key))
            

        })

        if (localStorage.getItem("locallang") == "he") {
            $(".i18n").addClass("hebrew");

            var url_string = window.location.href;
            var url = new URL(url_string);
            var idParam = url.searchParams.get("id");
            //   var subjectParam = url.searchParams.get("subject");

            var path = window.location.pathname;
            var pageName = path.split("/").pop();
            // console.log(pageName);

            if (pageName != "indexHE.html")
                    window.location = "/mobile/reg/medals/indexHE.html?id=" + idParam;





            

           
         

        }

} 

function swapElement(a, b) {
    // create a temporary marker div
    var aNext = $('<div>').insertAfter(a);
    a.insertAfter(b);
    b.insertBefore(aNext);
    // remove marker div
    aNext.remove();
}