 
var jsonhe = (function(){
  var json = null;
  $.ajax({
    'async':false,
    'global':false,
    'url':'../js/translation/store/index/lang/he.json',
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
    'url':'../js/translation/store/index/lang/en.json',
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

            //media-body

        })

        if (localStorage.getItem("locallang") == "he") {
            $(".i18n").addClass("hebrew");
           // $(".media-body").css("float", "right");
            $("media-body").css("direction", "rtl");
        }

    } 