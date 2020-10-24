 
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

            lng: (sessionStorage.getItem("locallang"))?sessionStorage.getItem("locallang"):'en',

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
            
            sessionStorage.setItem("locallang", lang);

            i18next.changeLanguage(lang);
            translate();      
        })

    

       
    })



    function translate() {

        $(".i18n").each(function () {

            const key = $(this).data('key')

            $(this).text(i18next.t(key))
            

        })

        if (sessionStorage.getItem("locallang") == "he") {
            //alert();


            $(".i18n").addClass("hebrew");


           // 

           //commanderdiv
            $("#serialdiv .title").remove().insertAfter($("#serialdiv .descr"));
            $("#commanderdiv .title").remove().insertAfter($("#commanderdiv .descr"));
            $("#platoondiv .title").remove().insertAfter($("#platoondiv .descr"));

            $(".descr").css("margin-right", "5px");

          
           // var topleftspace
            swapElement($("#topleftspace"), $("#toprightspace"));

            $("#topleftspace").removeClass("top-left-space");
            $("#topleftspace").addClass("top-right-space");

            $("#toprightspace").removeClass("top-right-space");
            $("#toprightspace").addClass("top-left-space");


           
          //  $("#topleftspace").css("background-color", "yellow");
            //$("#topleftspace").css("float", "left");

          //  $("#toprightspace").css("background-color", "yellow");

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