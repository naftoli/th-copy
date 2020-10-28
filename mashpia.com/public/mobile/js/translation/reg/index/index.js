 var jsonhe = (function () {
     var json = null;
     $.ajax({
         'async': false,
         'global': false,
         'url': '../js/translation/reg/index/lang/he.json',
         'dataType': "json",
         'success': function (data) {
             json = data;
         }

     });
     return json;
 })();

 var jsonen = (function () {
     var json = null;
     $.ajax({
         'async': false,
         'global': false,
         'url': '../js/translation/reg/index//lang/en.json',
         'dataType': "json",
         'success': function (data) {
             json = data;
         }

     });
     return json;
 })();
 


 $(function () {

     i18next.init({

        lng: (sessionStorage.getItem("locallang"))?sessionStorage.getItem("locallang"):'en',


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

        trasPlaceholder();

        translate()
     });

     $('.translate').click(function () {
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

     if (sessionStorage.getItem("locallang") == "he") {
       

         $(".i18n").addClass("hebrew");

          
         $("#remembermediv").css("float", "right");
     

         $("#remember").remove().insertAfter($("#rememberme"));

         $("#rememberme").css("margin-right", "5px");

         $("#forgotdiv").remove().insertAfter($("#creatediv"));

         $("#forgotdiv").css("direction", "rtl");

         $("#error-block").css("direction", "rtl");

       


     }
     

 }