
var jsonhe = (function () {
    var json = null;
    $.ajax({
        'async': false,
        'global': false,
        'url': 'js/translation/missionsNew/lang/he.json',
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
        'url': 'js/translation/missionsNew/lang/en.json',
        'dataType': "json",
        'success': function (data) {
            json = data;
        }

    });
    return json;
})();

$(function () {

    /// example number 1 - static translation 
    i18next.init({

        lng: (localStorage.getItem("locallang")) ? localStorage.getItem("locallang") : 'en',

        debug: true,
        interpolation: {
            escapeValue: false,
        },
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
        translate();

    });

    $('.translate').click(function () {
        var lang = $(this).attr('id');
        localStorage.setItem("locallang", lang);

        i18next.changeLanguage(lang);
        trasPlaceholder();
        translate();
    })


})


function trasPlaceholder() {

    $("input.form-control").each(function () {

        const key = $(this).data('key')

        $(this).attr('placeholder', i18next.t(key))

    });

   

}

function translate() {
    //alert();
    $(".i18n").each(function () {

        const key = $(this).data('key')
       
        $(this).text(i18next.t(key))


    })

    
    $(".checkAll, .checkAllDaily").each(function () {

        const key = $(this).data('key');
       // alert(key);
        $(this).val(i18next.t(key));
       
    });

    if (localStorage.getItem("locallang") == "he") {



        $(".i18n").addClass("hebrew");
        
        $("#myModal").css("direction", "rtl");

        $("#checkAll").val("סמן הכל");

        $("#tSymbol").css("margin-right", "-20px");
        $("#Missionislater").css("margin-right", "30px");

        $(".btn").css("font-size", "14px");
        
       // tSymbol






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