 
var jsonhe = (function(){
  var json = null;
  $.ajax({
    'async':false,
    'global':false,
      'url':'../js/translation/registration/add-child/lang/he.json',
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
      'url':'../js/translation/registration/add-child/lang/en.json',
    'dataType':"json",
    'success':function(data){
        json=data;
    }
    
  });
  return json; 
})(); 
 

 
    $(document).ready(function () {
        i18next.init({

            lng: (localStorage.getItem("locallang"))?localStorage.getItem("locallang"):'en',


            debug: true,

            resources: {

                en: {

                    translation: jsonen,
                },

                he: {

                    translation: jsonhe
                }

            }

        }).then(function (t) { 
            // initialized and ready to go!

            translate();
            trasPlaceholder();

        });
        
         

        $('.translate').click(function() {
            var lang = $(this).attr('id');
            localStorage.setItem("locallang", lang);
            
            i18next.changeLanguage(lang);

            trasPlaceholder();
            translate();      
        })

 

    })


function trasPlaceholder() {

    $("input.form-control").each(function () {

        const key = $(this).attr('data-key')
        
        $(this).attr('placeholder', i18next.t(key))

    });

}

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
            var path = window.location.pathname;
            var pageName = path.split("/").pop();
            console.log(pageName);

            if (pageName != "childHE.html")
                window.location = "/mobile/registration/childHE.html";





        }


} //

