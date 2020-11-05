 
var jsonhe = (function(){
  var json = null;
  $.ajax({
    'async':false,
    'global':false,
    'url':'../js/translation/parent-detail/lang/he.json',
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
    'url':'../js/translation/parent-detail/lang/en.json',
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

        });
        
         

        

 

    })



    
 

        if (localStorage.getItem("locallang") == "he") {
            //alert();


           $(".i18n").addClass("hebrew");


            $(".float_element").css("float", "right");

            
             $("table").css("direction", "rtl");
            
            //$("#forgot").addClass("hebrew");

            //   


        }


} //


function test() {
    alert();
}