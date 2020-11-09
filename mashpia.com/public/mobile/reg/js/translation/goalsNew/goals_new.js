 
var jsonhe = (function(){
  var json = null;
  $.ajax({
    'async':false,
    'global':false,
    'url':'../js/translation/goalsNew/lang/he.json',
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
    'url':'../js/translation/goalsNew/lang/en.json',
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

        
        $('#alert-now').click(function() {
            alert(i18next.t('message'));
        })

        // get info from api to illustrate that we can make it work also with elements created and inserted dynamically

        // $.post("js/translation/forgot/en.json", function (res) {

        //     // todo : add all translations to page dynamicaly !!
        //     console.log(res,'sss')

        // })

    })



    function translate() {

        $(".i18n").each(function () {

            const key = $(this).data('key')

            $(this).text(i18next.t(key))
            

        })

    } 