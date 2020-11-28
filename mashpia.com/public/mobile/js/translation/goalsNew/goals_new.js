 
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


function initTranslation() {
   
    i18next.init({

        lng: (localStorage.getItem("locallang")) ? localStorage.getItem("locallang") : 'en',

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

        translate();
        trasPlaceholder();
    });
}
 
    $(function () {
       
        
        initTranslation();

        $('.translate').click(function() {
            var lang = $(this).attr('id');
            localStorage.setItem("locallang", lang);
            
            i18next.changeLanguage(lang);
            translate();      
        })

        
        $('#alert-now').click(function() {
            alert(i18next.t('message'));
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
        //
        if (localStorage.getItem("locallang") == "he") {


            $(".i18n").addClass("hebrew");

            $(".info").css("direction", "rtl");
         


            $(".info").attr('dir', "rtl");
            
            $("#back-link").css('float', "right");// Mark Missions
            $("#create-link").css('float', "left"); // add custom
            $("#customTaskModal").css("direction", "rtl");
            $("#ModalTitle").css("margin-right", "6px;");
            $("#Parshos").css("direction", "ltr");
            $("#toggleParshos").val("סימון הכל");
            

            //
        }
        

    } 