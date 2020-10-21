 
var jsonhe = (function(){
  var json = null;
  $.ajax({
    'async':false,
    'global':false,
    'url':'../js/translation/forgot/lang/he.json',
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
    'url':'../js/translation/forgot/lang/en.json',
    'dataType':"json",
    'success':function(data){
        json=data;
    }
    
  });
  return json; 
})(); 
 
    $(function () {

        /// example number 1 - static translation 
        i18next.init({

            lng: (sessionStorage.getItem("locallang"))?sessionStorage.getItem("locallang"):'en',

            debug: true,

            resources: {

                en: {

                    translation: jsonen
                },

                he: {

                    translation: jsonhe
                }

            }

        }).then(function (t) { 
            // initialized and ready to go!
            trasPlaceholder();
            translate()

        }); 

        $('.translate').click(function() {
            var lang = $(this).attr('id');
            sessionStorage.setItem("locallang", lang);

            i18next.changeLanguage(lang);
            trasPlaceholder();
            translate();      
        })

  
        

    })


    function trasPlaceholder(){

        $("input.form-control").each(function() {

            const key = $(this).attr('data-key')
            
            $(this).attr('placeholder',i18next.t(key)) 

        });           
        
    }

    function translate() {

        $(".i18n").each(function () {

            const key = $(this).data('key')

            $(this).text(i18next.t(key))
            

        })

    } 