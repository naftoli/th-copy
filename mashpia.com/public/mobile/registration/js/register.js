/**
 * JS file for /mobile/registration/
 */
// page setup
if (!checkDateInput()) {
  $('#dob, input[type="date"]').datepicker({format: "yyyy-mm-dd"});
}
$("#successModal").on('hidden.bs.modal', function (event) {
  location.href = "/mobile/reg/parent_detail.html"
});
// $("#errorModal").on('hidden.bs.modal', function () { $("#errorBody").empty() });
$("#error2Modal").on('hidden.bs.modal', function () {
  $("#error2Body").empty()
})
$('[data-toggle="popover"]').popover();
hebrew_keyboard.attach("#first_he, #last_he"); // use hebrew in the right places

// when clicking an element with a data-dismiss attribute
// dismiss the element referenced in the data-target attribute
// don't know why just these modals are not working without this
$("[data-dismiss]").click(function (event) {
  event.target.dataset.target && $(event.target.dataset.target).modal('hide')
})

function alertPersonalization(evt) {
  if (evt.checked) {
    // (On the following page you will see the option " +
    // "to pay this in up to 4 installments)
    alert(
      "If you would like your child to have their name on their prize, please input it below, and you will need to " +
      "pre-pay the registration fee now instead of at the end of the year.\n\nThis amount is NON-REFUNDABLE and will be " +
      "used towards their registration ONLY IF they pass Yediah or higher."
    )
  }
}

var myshliach = 61;
var anash_kinder = 269;
var showClasses = 0; // global var to determine if we need to show link to myshliach online classes
var selected_index;
var current_user;
var school_id;
var user_prizes = {}
var khk_fee = 18
var usa = [
  'U.S.A',
  'United States',
  'US',
  'USA'
]

var hachayolChosen = false
var chidonPayment = []
var checkForRegShipping = false
const HACHAYOL_FEE = 20

var state = {
  users: [], // the users we are registering
  registered: [], // registered users (for choosing hachayols)
  selected_users: [], // users selected in step-1
  cart: [], // items that the user is paying for
  shipping_type: 1 // 1 or 2 or 3
}

var non_th_schools = {}

var pic_uploaded = false

var registrationApp = function () {
  var api_url = '/api/registration/user_registration.php'; // API endpoint for this page

  var Msg1 = "Tzivos Hashem Enrollment for ";

  var Msg3 = "CKids Enrollment for ";
  var Msg4 = "Chidon Enrollment for ";
  var Msg5 = " (includes coordinator and study guide)";//
  var Msg6 = "Yahadus Book ";
  var Msg7 = " (Includes Shipping)";
  var Msg8 = "To Enroll for MyShliach's online weekly classes please click ";
  var Msg9 = "here";
  //var Msg10 = "";
  //var Msg11 = "";

  var Err1 = "You must select at least one type of registration.";
  var Err2 = "You must choose which book is being studied.";
  var Err3 = "You must indicate if you have already purchased a book or not.";
  var Err4 = "You have not selected where you bought the book.";
  var Err5 = "You must enter the store information for your book purchase.";
  var Err6 = "You must indicate whether you would like to purchase a book or not.";
  var Err7 = "You must indicate how you will be learning for chidon.";
  var Err8 = "You must indicate your acknowledgment of the Shabbaton fee.";
  var Err9 = "You must enter the serial number of the student who recruited you.";
  var Err10 = "You must enter the name of the school that you are attending.";
  var Err11 = "You must indicate your acceptance of participation in Tzivos Hashem Media.";
  var Err12 = "Could not update Profile Picture. We will try again when pressing 'Confirm'.";
  var Err13 = "You must choose how you prefer your name to be displayed!";
  var Err14 = "You must choose a yarmulka size!";
  var Err15 = "You must enter the hebrew plaque name as well as english and hebrew name for custom items.";
  var Err16 = "Could not confirm your registration. Ensure that the required fields are filled in or contact support";
  var Err17 = "You need to enter your English Name that you are known by";
  var Err18 = "You need to enter your Hebrew Name that you are known by";
  //var Err19 = "";
  //var Err20 = "";
  var picError = "You must upload a picture of your child!"

  if (localStorage.getItem("locallang") == "he") {
    Msg1 = "ההרשמה לצבאות ה' עבור ";

    Msg3 = "ההרשמה לסי קידס עבור ";
    Msg4 = "הרשמה לחידון עבור ";
    Msg5 = "(כולל מתאם ומדריך לימודי";//
    Msg6 = "ספר יהדות עבור ";
    Msg7 = "(דמי המשלוח כלולים)";
    Msg8 = "להרשם לשיעורים השבועיים של מיי שליח נא להקליק  ";
    Msg9 = "כאן";
    // Msg10 = "";
    // Msg11 = "";

    Err1 = "חובה לבחור לפחות הרשמה אחת .";
    Err2 = "חובה לבחור באיזה ספר למדתם.";
    Err3 = "חובה לציין האם רכשתם את הספר או לא.";
    Err4 = "לא בחרת היכן הספר נקנה";
    Err5 = "חובה להקליד את הכתובת של החנות בה נקנה הספר";
    Err6 = "חובה לציין האם אתם רוצים לקנות את הספר או לא .";
    Err7 = "חובה לציין כיצד אתם מתכוונים ללמוד לחידון.";
    Err8 = "חובה לציין שאתם מבינים את עלות השבתון";
    Err9 = "חובה לבחור מי צירף אתכם";
    Err10 = "חובה להכניס את שם בית הספר או תלמוד תורה בו אתם לומדים";
    Err11 = "חובה לציין את הסכמתכם להשתתפות במדיה של צבאות ה";
    Err12 = "לא ניתן לעדען את תמונת הפרופיל . נא לנסות שוב!";
    Err13 = "";
    Err14 = "";
    Err15 = "";
    Err16 = "לא ניתן היה לאשר את הרישום שלך. ודא כי השדות הנדרשים מלאים או צור קשר עם התמיכה";
    Err17 = "";
    Err18 = "";
    picError = ""
  }

  // navigation buttons
  $(".start-step-1").click(step1);
  $(".start-step-2").click(step2);
  $(".start-step-3").click(step3);

  $(".start-again").click(function () {
    const conf = confirm("You will lose all the information that you put in, are you sure you want to start again?")
    if (conf) {
      // clear all state
      state = {
        registered: [], // registered users (for choosing hachayols)
        selected_users: [], // users selected in step-1
        cart: [], // items that the user is paying for
        shipping_type: 1 // 1 or 2 or 3
      }
      hachayolChosen = false
      chidonPayment = []
      checkForRegShipping = false
      user_prizes = {}
      step1()
    }
  });

  // form handlers
  $("#step-2 form").submit(confirmUser);
  $("#step-3 form").submit(confirmShipping);
  $("#step-4 form").submit(registerUsersHandler);
  $("#step-4 #cc-number").keyup(validateCardInput);
  image_upload({}, onImageUploaded);
  // run the first step
  step1();

  /*********************** CODE TO GUIDE USER THROUGH STEPS ***********************/
  function noChildren() {
    showSection('no-children')
  }

  // select users
  function step1() {
    window.location.hash = 'step-1';
    showSection("step-1");
    toggleLoading("step-1", true);

    getUsers().then(function (users) {
      console.log(users);

      if (users.length === 0) return noChildren();

      // initialize chidon payment for each user
      for (let user of users) {
        chidonPayment[user.user_id] = false
      }

      if (users.length === 1) return step2();

      var html = '';
      state.users.forEach(function (user) {
        html += templates.child(user);
      });
      $("#children").html(html);

      toggleLoading("step-1", false);
    });
  }

  async function setupNonThSchoolList() {
    // setup non th school list
    const result = await $.post('api/getNonThSchools.php');
    const res = JSON.parse(result)
    non_th_schools = res
    const sorted = sortByVal(res)
    autocomplete(document.getElementById("non_th_school"), sorted)
    $("#non_th_school").blur(function () {
      if ($(this).val().trim() == '') {
        $("#non_th_school_id").val(0)
      } else {
        // find the school id from the sorted array
        let found = false
        for (let row of sorted) {
          let key = row[0]
          let value = row[1]
          if (value == $(this).val().trim()) {
            $("#non_th_school_id").val(key)
            found = true
            break
          }
        }
        if (!found) {
          $("#non_th_school_id").val(0)
        }
      }
    })
  }

  // sorts an object by value
  function sortByVal(obj) {
    return Object.entries(obj).sort((a, b) => a[1].localeCompare(b[1]));
  }

  function autocomplete(inp, arr) {
    /*the autocomplete function takes two arguments,
        the text field element and an array of possible autocompleted values:*/
    var currentFocus;
    /*execute a function when someone writes in the text field:*/
    inp.addEventListener("input", function (e) {
      var a, b, i, val = this.value;
      /*close any already open lists of autocompleted values*/
      closeAllLists();
      if (!val) {
        return false;
      }
      currentFocus = -1;
      /*create a div element that will contain the items (values):*/
      a = document.createElement("div");
      a.setAttribute("id", this.id + "-autocomplete-list");
      a.setAttribute("class", "autocomplete-items");
      /*append the div element as a child of the autocomplete container:*/
      this.parentNode.appendChild(a);
      /*for each item in the array...*/
      for (i = 0; i < arr.length; i++) {
        let school_id = arr[i][0]
        let school_info = arr[i][1].split(',')
        let school_name = school_info[0]
        let city = school_info[1]
        let state = school_info[2]
        let zip = school_info[3]
        let country = school_info[4]
        let full_name = school_name + ', ' + city + ', ' + state + ', ' + zip + ', ' + country
        let friendly = school_name
        if (city) friendly += ', ' + city
        if (state) friendly += ', ' + state
        if (zip) friendly += ', ' + zip
        if (country) friendly += ', ' + country
        /*check if the item starts with the same letters as the text field value:*/
        // if (value.substr(0, val.length).toUpperCase() == val.toUpperCase()) {
        // check if value contains the same letters as the text field value:
        if (school_name.toUpperCase().includes(val.toUpperCase())) {
          /*create a div element for each matching element:*/
          b = document.createElement("div");
          /*make the matching letters bold:*/
          // find out where the match is
          let index = full_name.toUpperCase().indexOf(val.toUpperCase())
          b.innerHTML = friendly.substr(0, index);
          b.innerHTML += "<strong>" + friendly.substr(index, val.length) + "</strong>";
          b.innerHTML += friendly.substr(index + val.length);
          /*insert an input field that will hold the current array item's value:*/
          b.innerHTML += "<input type='hidden' value='" + full_name + "'>";
          /*execute a function when someone clicks on the item value (div element):*/
          b.addEventListener("click", function (e) {
            /*insert the value for the autocomplete text field:*/
            let school_info = this.getElementsByTagName("input")[0].value.split(',')
            document.getElementById('non_th_school_id').value = school_id
            document.getElementById('non_th_school').value = school_info[0]
            document.getElementById('non-th-city').value = school_info[1]
            document.getElementById('non-th-state').value = school_info[2]
            document.getElementById('non-th-zip').value = school_info[3]
            document.getElementById('non-th-country').value = school_info[4]
            /*close the list of autocompleted values,
                        (or any other open lists of autocompleted values:*/
            closeAllLists();
          });
          a.appendChild(b);
        }
      }
    });
    /*execute a function presses a key on the keyboard:*/
    inp.addEventListener("keydown", function (e) {
      var x = document.getElementById(this.id + "autocomplete-list");
      if (x) x = x.getElementsByTagName("div");
      if (e.keyCode == 40) {
        /*If the arrow DOWN key is pressed,
                increase the currentFocus variable:*/
        currentFocus++;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 38) { //up
        /*If the arrow UP key is pressed,
                decrease the currentFocus variable:*/
        currentFocus--;
        /*and and make the current item more visible:*/
        addActive(x);
      } else if (e.keyCode == 13) {
        /*If the ENTER key is pressed, prevent the form from being submitted,*/
        e.preventDefault();
        if (currentFocus > -1) {
          /*and simulate a click on the "active" item:*/
          if (x) x[currentFocus].click();
        }
      }
    });

    function addActive(x) {
      /*a function to classify an item as "active":*/
      if (!x) return false;
      /*start by removing the "active" class on all items:*/
      removeActive(x);
      if (currentFocus >= x.length) currentFocus = 0;
      if (currentFocus < 0) currentFocus = (x.length - 1);
      /*add class "autocomplete-active":*/
      x[currentFocus].classList.add("autocomplete-active");
    }

    function removeActive(x) {
      /*a function to remove the "active" class from all autocomplete items:*/
      for (var i = 0; i < x.length; i++) {
        x[i].classList.remove("autocomplete-active");
      }
    }

    function closeAllLists(elmnt) {
      /*close all autocomplete lists in the document,
            except the one passed as an argument:*/
      var x = document.getElementsByClassName("autocomplete-items");
      for (var i = 0; i < x.length; i++) {
        if (elmnt != x[i] && elmnt != inp) {
          x[i].parentNode.removeChild(x[i]);
        }
      }
    }

    /*execute a function when someone clicks in the document:*/
    document.addEventListener("click", function (e) {
      closeAllLists(e.target);
    });
  }

  var pleaseSelectErr = "Please select at least one child";
  var pleaseSelectErr_he = "נא לבחור לפחות ילד אחד";

  // show selected users
  async function step2() {
    window.location.hash = 'step-2';
    state.selected_users = [];
    // state.cart = [];
    // make sure that we have at least one user
    if (state.users.length === 1) {
      state.selected_users.push(Object.assign(
        {confirmed: false}, state.users[0]
      ));
    } else if (state.users.length === 0 || $('#step-1 #children input:checked').length === 0) {
      if (localStorage.getItem("locallang") == "he")
        return showError(pleaseSelectErr_he);
      return showError(pleaseSelectErr);
    }
    // determine if the navigation should show
    if (state.users.length == 1) {
      $('#step-2 .navigation').hide();
    } else {
      $('#step-2 .navigation').show();
    }
    // get only the users that they checked
    $.each($('#step-1 #children input:checked'), function (index, checkbox) {
      var user = Object.assign({confirmed: false}, // set confirmed to false and get a new object
        state.users.find(function (user) { // filter the user options
          return user.user_id == checkbox.dataset.user_id // only return users with the correct ID
        })
      );
      state.selected_users.push(user);
    });

    // remove the user if we had him before...
    // var user_ids = state.selected_users.map( function( user ) { return user.user_id } );
    // state.cart = state.cart.filter( function(item) { return user_ids.includes( item.meta.user_id ) } );

    // show anash kinder text if anash kinder school
    if (school_id == anash_kinder) $("#anash_kinder_text").show();
    else $("#anash_kinder_text").hide();

    let txt = "I am aware that if my child qualifies for the Chidon Experience by getting a passing average on the 3 tests there will be a fee of $36 - $350 depending on the track they pass"
    if ([61, 269].includes(school_id)) {
      txt += " PLUS a shipping fee (depending on your address).";
      $("#myshliachTerms").show()
    } else {
      txt += "."
      $("#myshliachTerms").hide()
    }
    $("#terms-1").text(txt)

    await setupNonThSchoolList()

    // show the page
    templates.showUser(state.selected_users[0], 0);
    showSection('step-2');

    current_index = parseInt($("#current_index").val());
    selected_user = state.selected_users[current_index];
    school_id = selected_user.school.school_id;

    // yahadus registration
    var australian = [55, 66, 110, 112, 180, 256, 643, 690, 709, 713];
    $('.book-bought').click(function () {
      if ($(this).val() === '0') {
        console.log(school_id)
        if (!australian.includes(school_id)) {
          if ([61, 269].includes(school_id)) {
            $(".yahadus-myshliach").show()
            $('#yahadus-registration').hide();
            // $('#step-2 form #yahadus-registration-no').hide();
          } else {
            $(".yahadus-myshliach").hide()
            $('#yahadus-registration').show();
            // $('#step-2 form #yahadus-registration-no').show();
          }
        }
        // } else {
        //     $('#step-2 form #book-purchase').hide();
        //     $("#step-2 form .book-purchase-myshliach").hide()
        // }
        $('#book-purchase').hide()
      } else if ($(this).val() === '1') {
        $('#yahadus-registration input')[0].checked = false;
        $('#yahadus-registration').hide();
        // $( '#step-2 form #yahadus-registration-no input' )[0].checked = false;
        // $( '#step-2 form #yahadus-registration-no').hide();
        // if (selected_user.getChidonInfo) { // if we are editing chidon info
        //   $('#book-purchase').hide()
        //   $(".yahadus-myshliach").hide()
        // } else {
          $('#book-purchase').show();
          $(".yahadus-myshliach").hide()
          if ([61, 269].includes(school_id)) {
            $(".book-purchase-item").hide()
            $(".book-purchase-myshliach").show()
          } else {
            $(".book-purchase-item").show()
            $(".book-purchase-myshliach").hide()
          }
        // }
      }
    });

    $('.book-purchase').change(function () {
      var purchaseValue = $(this).val();
      if (purchaseValue == 'store') $("#step-2 form #book-purchase-details").show();
      else $("#step-2 form #book-purchase-details").hide();
    });

    // show study guides info for all non Australian schools
    // if ( !australian.includes( school_id ) && school_id != anash_kinder && school_id != myshliach ) $("#study-guides").show();

    var addressErr = "The address, city, state, zip and country fields cannot be left blank!";
    var shippingChargeMsg30 = "There is an extra shipping charge of <b>$30.</b><br />";
    var shippingChargeMsg15 = "There is an extra shipping charge of <b>$15.</b>"

    if (localStorage.getItem("locallang") == "he") {
      addressErr = "הכתובת , העיר והמיקוד הם שדות חובה ";
      shippingChargeMsg30 = "ישנו חיוב נוסף של דמי משלוח על סך  " + "<b>$30.</b><br />";
      shippingChargeMsg15 = "ישנו חיוב נוסף של דמי משלוח על סך  " + "<b>$15.</b><br />";

    }

    // show address modal if purchasing book and from anash kinder
    // $("#yahadus-registration input").click( function() {
    //     if ( $(this).val() == '1' && selected_user.school.school_id === anash_kinder ) {
    //         if (selected_user.parentAccount.admin_address1.length) $("#ship-address1").val( selected_user.parentAccount.admin_address1 )
    //         else $("#ship-address1").attr('placeholder', 'Address line 1')
    //
    //         if (selected_user.parentAccount.admin_address2.length) $("#ship-address2").val( selected_user.parentAccount.admin_address2 );
    //         else $("#ship-address2").attr('placeholder', 'Address line 2')
    //
    //         if (selected_user.parentAccount.admin_city.length) $("#ship-city").val( selected_user.parentAccount.admin_city );
    //         else $("#ship-city").attr('placeholder', 'city')
    //
    //         if (selected_user.parentAccount.admin_state.length) $("#ship-state").val( selected_user.parentAccount.admin_state );
    //         else $("#ship-state").attr('placeholder', 'state')
    //
    //         if (selected_user.parentAccount.admin_postal.length) $("#ship-zip").val( selected_user.parentAccount.admin_postal );
    //         else $("#ship-zip").attr('placeholder', 'zip')
    //
    //         if (selected_user.parentAccount.admin_country.length) $("#ship-country").val( selected_user.parentAccount.admin_country );
    //         else $("#ship-country").val('USA')
    //
    //         $("#shipping-modal").modal('show');
    //         $("#update-shipping").click( function() {
    //             var info = {};
    //             info.address1 = $("#ship-address1").val();
    //             info.address2 = $("#ship-address2").val();
    //             info.city = $("#ship-city").val();
    //             info.state = $("#ship-state").val();
    //             info.zip = $("#ship-zip").val();
    //             info.country = $("#ship-country").val();
    //             if (!(info.address1 && info.city && info.state && info.zip && info.country)) {
    //                 alert(addressErr);
    //                 return false;
    //             }
    //             var thisUser = selected_user; // needed for scope
    //             $.post("updateAddress.php", { info: info }, function( res ) {
    //                 if ( res.success ) {
    //                     alert( res.data );
    //                     thisUser.parentAccount.admin_country = info.country;
    //                     // if (thisUser.parentAccount.admin_country.toUpperCase() == 'USA') $("#yahadus-shipping").html(shippingChargeMsg15);
    //                     // else $("#yahadus-shipping").html(shippingChargeMsg30);
    //                     $("#yahadus-shipping").html(shippingChargeMsg15)
    //                     $("#shipping-modal").modal('hide');
    //                 } else {
    //                     alert( res.error );
    //                 }
    //             });
    //         });
    //     }
    // });

    $.post('api/tasks/getSchools.php', function (result) {
      if (result.success) {
        var schools = result.data;
        var html = '';
        for (var s in schools) {
          html += "<option value=" + schools[s] + ">" + s + "</option>";
        }
        $("#school").append(html);
      } else {
        alert(success.error - div);
      }
    });

    $("#school").change(function () {
      var gradeMsg = "Select Grade";
      if (localStorage.getItem("locallang") == "he") {
        gradeMsg = "בחירת כיתה";
      }
      $.post('api/tasks/getGrades.php', {school_id: $(this).val()}, function (result) {
        if (result.success) {
          var grades = result.data;
          var html = "<option value='0'>" + gradeMsg + "</option>";
          for (var g in grades) {
            html += "<option value=" + g + ">" + grades[g] + "</option>";
          }
          $("#grade").empty();
          $("#grade").append(html);
        } else {
          alert(result.error);
        }
      });
    });

    $("#grade").change(function () {
      var studentMsg = "Select Student";
      if (localStorage.getItem("locallang") == "he") {

        studentMsg = "בחירת תלמיד";
      }
      $.post('api/tasks/getStudents.php', {class_id: $(this).val()}, function (result) {
        if (result.success) {
          var users = result.data;
          var html = "<option value='0'>" + studentMsg + "</option>";
          for (var u in users) {
            html += "<option value=" + users[u] + ">" + u + "</option>";
          }
          $("#user").empty();
          $("#user").append(html);
        } else {
          alert(result.error);
        }
      });
    });
    current_user = selected_user.user_id // for using current_user in chidon prizes cart
  }

  // select shipping option
  function step3() {
    if (state.selected_users.length === 0) return step1();
    window.location.hash = 'step-3';
    toggleLoading('step-3', true);
    showSection('step-3');

    // find out shipping type
    switch (selected_user.parentAccount.admin_country.toUpperCase()) {
      case 'US':
      case 'USA':
        state.shipping_type = 1
        break
      case 'CANADA':
        state.shipping_type = 2
        break
      default:
        state.shipping_type = 3
        break
    }

    // show shipping rates for ak or myshliach
    let school_id = selected_user.school.school_id
    if (school_id == 61) $("#myshliach-shipping").show()
    if (school_id == 269) $("#anashKinder-shipping").show()

    // remove any shipping items from the cart
    chayolei_user_ids = state.cart.map(function (item) {
      // skip for any non chayolei registration or chayolei lite registration
      return item.meta.registration_type !== 'chayolei' || item.meta.lite_version != undefined || item.meta.user_id
    });

    let users = state.selected_users.filter(function (user) {
      return user.registrationStatus.chayolei === false && chayolei_user_ids.includes(user.user_id);
      // and just get their school ids
    }).map(function (user) {
      return user.school.school_id;
    })

    getShipping(users)
      .then(function (response) {
        if (!response) return step4(); // skip straight to step 4
        // if we have a response then we need to show the shipping options
        $("#shipping-cost").val(response)
        toggleLoading('step-3', false);
      });
  }

  // cart / payment information
  function step4() {
    if (state.selected_users.length === 0) return step1();
    window.location.hash = 'step-4';
    showSection("step-4");

    console.log(state.cart)
    templates.renderCheckout(state.cart);

    var total = state.cart.reduce(function (total, item) {
      return parseInt(total) + parseInt(item.price)
    }, 0)
    for (i = 0; i < state.cart.length; i++) {
      if (state.cart[i].meta.discount) total -= parseFloat(state.cart[i].meta.discount)
    }
    if (total < 0) total = 0;
    if (total <= 0) {
      var postData = {};
      postData.payment = {}
      postData.payment['total'] = 0
      postData.payment['installments'] = 0
      postData.cart = state.cart.map(function (item) {
        return item.meta
      })
      return registerUsers(postData);
    }

    toggleLoading('payment', true);
    getPaymentProfiles().then(function (payment_profiles) {
      if (!payment_profiles || payment_profiles.length === 0) {
        templates.toggleNewCard(true);
        $("#card-on-file").hide();
      } else {
        templates.toggleNewCard(false);
        $("#card-on-file").show();
        templates.renderPaymentOptions(payment_profiles);
      }
      toggleLoading('payment', false);

      // figure out if we need to show recurring payment option
      let chidonReg = state.cart.filter(item => item.meta.registration_type === 'chidon' && item.meta.type === 'advance registration')
      if (chidonReg.length) $("#recurring").show()
    })
  }

  /*********************** FORM HANDLERS ***********************/
  function checkField(field) {
    // skip if we are editing registration
    if (['#chidon-fee', '.book-bought', '#bookVersion'].includes(field.field) && selected_user.getChidonInfo) return false
    let value, checked
    switch (field.type) {
      case 'value':
        value = $(field.field).val()
        if (value == '' || value == 0 || value == '0' || !value.length) return field.error
        break
      case 'amount':
        // make sure we have a number greater than 0
        value = parseInt($(field.field).val())
        if (value <= 0) return field.error
        break
      case 'check':
      case 'yes/no':
        // if 'only' key then check that
        if (field.only) {
          if (field.only == 'new' && selected_user.registrationStatus.new_to_chidon == 0) break
        }
        checked = $(field.field).is(":checked")
        if (!checked) return field.error
        break
      case 'functionOnly':
        break
    }
    return false
  }

  async function confirmUser(event) {
    event.preventDefault();

    // make sure picture is uploaded
    if ($("#user-img").attr('src').includes('addphoto.png')) {
      return showError(picError)
    }

    // update the user's information
    var postData = {};
    var user_changed = false;
    current_index = parseInt($("#current_index").val());
    selected_user = state.selected_users[current_index];
    console.log(selected_user);

    // find all changed fields
    $(event.target).serializeArray().forEach(function (item) {
      if (selected_user[item.name] == '' ||
        (selected_user[item.name] && selected_user[item.name] != item.value)
      ) {
        if (!user_changed) user_changed = true;
        selected_user[item.name] = postData[item.name] = item.value;
      }
    })

    const saved = await saveAddress('#parent-address')
    if (!saved) return false

    // make sure non th school field is not empty
    if ([269, 61].includes(selected_user.school.school_id)) {
      let non_th_school_id = parseInt($("#non_th_school_id").val())
      // if (non_th_school_id == 0) {
      let non_th_school = $("#non_th_school").val().trim()
      let non_th_city = $("#non-th-city").val().trim()
      let non_th_state = $("#non-th-state").val().trim()
      let non_th_zip = $("#non-th-zip").val().trim()
      let non_th_country = $("#non-th-country").val().trim()

      // make sure city has at least 4 alphanumeric characters
      if (non_th_school.length < 4) {
        return showError("You must enter a valid Non TH School Name")
      }
      if (non_th_city.length < 3) {
        return showError("You must enter a valid Non TH School City")
      }
      // make sure state has at least 2 letters
      if (non_th_state.length < 2) {
        return showError("You must enter a valid Non TH School State")
      }
      // make sure zip has at least 4 alphanumeric characters
      if (non_th_zip.length < 4) {
        return showError("You must enter a valid Non TH School Zip/Postal Code")
      }
      // make sure country has at least 2 letters
      if (non_th_country.length < 2) {
        return showError("You must enter a valid Non TH School Country")
      }
      // }
      // updating non th schools
      postData['non_th_school_id'] = non_th_school_id
      postData['non_th_school'] = non_th_school
      postData['non_th_city'] = non_th_city
      postData['non_th_state'] = non_th_state
      postData['non_th_zip'] = non_th_zip
      postData['non_th_country'] = non_th_country
      user_changed = true // force user changed
    }

    // if the user changed update him in the background...
    if (user_changed) {
      state.selected_users[current_index] = selected_user;
      updateUser(selected_user.user_id, postData).then(function () {
        return getUsers();
      });
    }

    // make sure not to recharge khk if editing info (for cart info)
    if (selected_user.getChidonInfo && parseInt(selected_user.getChidonInfo.khk_reg) == 1) khk_fee = 0
    else khk_fee = 18

    // Detect and validate the charges accepted.
    selected_charges = {
      chayolei: $('#chayolei-registration input')[0].checked,
      // chayolei_lite: $('#chayolei-lite-registration input')[0].checked,
      ckids: $('#ckids-registration input')[0].checked,
      chidon: $('#chidon-registration input')[0].checked,
      yahadus: $('#yahadus-registration input')[0].checked,
      khk: $("#khk input")[0].checked
    }

    if (selected_charges.chayolei === false
      // && selected_charges.chayolei_lite === false
      && selected_charges.ckids === false
      && selected_charges.chidon === false
      && !selected_user.getChidonInfo
      // && selected_charges.yahadus === false
    ) {
      return showError(
        Err1
      );
    }

    if (selected_charges.chayolei === true) {
      // check that name is in english characters or apostrophe or hyphen or space or dot or double quote using regex
      // if (!/^[a-zA-Z\s'"-]+$/.test(selected_user.first)) {
      //   return showError('You must enter your first name in English characters.')
      // }
      // if (!/^[a-zA-Z\s'"-]+$/.test(selected_user.last)) {
      //   return showError('You must enter your last name in English characters.')
      // }

      // check that he name is in hebrew characters or apostrophe or hyphen or space or dot or double quote
      // if (!/^[א-ת\s'"\-\.]*$/.test(selected_user.first_he)) {
      //   return showError('You must enter your hebrew first name in Hebrew characters.')
      // }
      // if (!/^[א-ת\s'"\-\.]*$/.test(selected_user.last_he)) {
      //   return showError('You must enter your hebrew last name in Hebrew characters.')
      // }

      // make sure an amount was chosen for the chayolei fee
      if (!$("#chayolei-fee").val() || $("#chayolei-fee").val() == 0) {
        return showError('You must choose an amount you are paying for Chayolei.')
      }

      // check that verification is checked off
      if (!$("#he_name").is(":checked")) {
        return showError('You must indicate your verification of the spelling of your hebrew name.')
      }

      // check that privacy policy is checked off
      if (!$("#media").is(":checked")) {
        return showError('You must indicate your acceptance of our Privacy Policy.')
      }

      // encourage parents to upload new picture
      if (pic_uploaded == false) {
        const res = confirm('We encourage you to upload a new picture of your child.\nWould you like to do that now?')
        if (res) {
          $("#user-img").click()
          return false
        }
      }
    }

    if (selected_charges.chidon === true) {
      let mandFields = [
        {
          field: '#chidon-fee',
          type: 'amount',
          error: 'You must choose how much you are paying for chidon enrollment',
          exception: selected_user.getChidonInfo == undefined, // if editing registration, don't validate
        },
        {
          field: '.limmud',
          type: 'check',
          error: 'You must choose a limmud track'
        },
        {
          field: '.recruit',
          type: 'yes/no',
          only: 'new',
          error: 'You must indicate if someone recruited you',
          yesDependents: [
            {
              field: '#recruited_by_user_serial',
              type: 'value',
              error: 'You have indicated that you have been recruited, please enter the serial number of the friend that recruited you'
            }
          ]
        },
        {
          field: '#chidon-sweater-size',
          type: 'value',
          error: 'You must choose a sweater size'
        },
        {
          field: '#chidon-book',
          type: 'amount',
          error: 'You must choose the book number you will be using'
        },
        {
          field: '.book-bought',
          type: 'yes/no',
          error: 'You must indicate whether you purchased a book or not',
          exception: selected_user.getChidonInfo == undefined, // if editing registration, don't validate
          yesDependents: [
            {
              field: '.book-purchase',
              type: 'check',
              error: 'You must indicate where you bought your book'
            },
            {
              field: '#bookVersion',
              type: 'amount',
              error: 'You must indicate which book version you will be using'
            }
          ],
          noDependents: [
            {
              field: '.yahadus',
              type: 'check',
              error: 'You have not indicated whether you would like to purchase a book or not',
              schoolExceptions: [61, 269, 55, 66, 110, 112, 180, 256, 643, 709, 713, 690, 3, 265, 432] // intl schools
            },
          ]
        },
        {
          field: '.yahadus-poll',
          type: 'check',
          error: 'You must indicate how you will be learning for the Chidon'
        },
        {
          field: '#shabbaton',
          type: 'check',
          error: 'You must indicate your acceptance of all Terms (1st term not checked)'
        },
        {
          field: '#heNameSpelling',
          type: 'check',
          error: 'You must indicate your acceptance of all Terms (2nd term not checked)'
        },
        {
          field: '#enNameSpelling',
          type: 'check',
          error: 'You must indicate your acceptance of all Terms (3rd term not checked)'
        },
        {
          field: '#edits',
          type: 'check',
          error: 'You must indicate your acceptance of all Terms (4th term not checked)'
        },
        {
          field: '#privacy',
          type: 'check',
          error: 'You must indicate your acceptance of all Terms (5th term not checked)'
        }
      ]

      // check all fields
      let error
      let errors = []
      for (field of mandFields) {
        error = checkField(field)
        if (error) errors.push(error)
        else if (field.type == 'yes/no') {
          if (field.exception !== undefined && field.exception) continue // skip if exception
          // check dependants if relevant
          let elem = field.field + ':checked'
          let value = $(elem).val()
          if (value === '1') {
            if (field.yesDependents) {
              for (dependent of field.yesDependents) {
                error = checkField(dependent)
                if (error) errors.push(error)
              }
            }
          } else if (value === '0') {
            if (field.noDependents) {
              for (dependent of field.noDependents) {
                if (dependent.schoolExceptions && dependent.schoolExceptions.includes(selected_user.school.school_id)) continue // don't validate if exception for this school
                error = checkField(dependent)
                if (error) errors.push(error)
              }
            }
          }
        }
      }
      // check myshliach term if relevant
      if (selected_user.school.school_id == myshliach) {
        if (!$("#myshliach").is(":checked")) {
          errors.push("You must indicate your acceptance of all Terms (6th term not checked)")
        }
      }

      // check recruited_by_user serial
      if ($(".recruit:checked").val() == '1') {
        let number = $("#recruited_by_user_serial").val().trim()
        if (number.length != 7) errors.push('Invalid serial number')
        else if (selected_user.user_serial == number) errors.push('You cannot enter your own serial number')
        else {
          const result = await $.post('api/checkSerial.php', {serial: number})
          if (!result) {
            errors.push('No such serial number found in our system')
          }
        }
      }

      // console.log(errors)
      if (errors.length) {
        let html = ''
        for (let i = 1; i <= errors.length; i++) {
          html += i + ') ' + errors[i - 1] + '.<br />'
        }
        return showError(html)
      }

      // check yarmulka
      if ($(".gender:checked").val() === 'M' && $("#yarmulka-size").val() == 0) {
        return showError(Err14)
      } else if ($(".gender:checked").val() === 'F') {
        $("#yarmulka-size").val(0)
      }

      // make sure that if they bought a book from a store, that the store info is filled out
      if ($(".book-purchase:checked").val() == 'store' && ($("#store-name").val().trim() == '' || $("#store-city").val().trim() == '')) {
        return showError(Err5);
      }
    }

    // validate that they have accepted to be used in media campaigns
    // if ( $(event.target).find( "#media" )[0].checked ){
    //     $(event.target).find( "#media" )[0].checked = false;
    // } else {
    //     return showError(Err11 )
    // }
    // remove the user if we had him before...
    state.cart = state.cart.filter(function (item) {
      return item.meta.user_id != selected_user.user_id
    });
    // console.log(state.cart)
    // and re-add him to the cart
    if (selected_charges.chayolei) {
      selected_user.registrationRates.chayolei = parseInt($("select#chayolei-fee").val());
      var discount = selected_user.getDiscount.length ? selected_user.getDiscount[0].amount : 0
      state.cart.push({
        description: Msg1 + selected_user.first,
        price: selected_user.registrationRates.chayolei,
        meta: {
          type: 'registration',
          user_id: selected_user.user_id,
          registration_type: 'chayolei',
          paid: selected_user.registrationRates.chayolei - discount,
          discount: discount,
          code: "C" + selected_user.user_serial + ":" + selected_user.school.school_id + ":THE-" + (selected_user.registrationRates.chayolei - discount) +
            (discount ? ":V-" + discount : ""),
          codeOnly: 'THE'
        }
      });
    }
    if (selected_charges.chayolei_lite) {
      selected_user.registrationRates.chayolei = 0;
      state.cart.push({
        description: Msg1 + selected_user.first,
        price: selected_user.registrationRates.chayolei,
        meta: {
          type: 'registration',
          user_id: selected_user.user_id,
          registration_type: 'chayolei',
          paid: selected_user.registrationRates.chayolei,
          lite_version: 1
        }
      });
    }
    if (selected_charges.ckids) {
      selected_user.registrationRates.chayolei = 0;
      state.cart.push({
        description: Msg3 + selected_user.first,
        price: selected_user.registrationRates.chayolei,
        meta: {
          type: 'registration',
          user_id: selected_user.user_id,
          paid: selected_user.registrationRates.chayolei,
          registration_type: 'chayolei',
          paid: selected_user.registrationRates.chayolei,
          ckids: 1
        }
      });
    }
    if (selected_charges.chidon) {
      var anash = selected_user.school.school_id === anash_kinder;
      var myshliach = selected_user.school.school_id === myshliach;
      var recruited = $(".recruit:checked").length ? $(".recruit:checked").val() : 0
      var recruited_by = $("#recruited_by_user_serial").val()
      if (!recruited || recruited === '0' || recruited_by == '') recruited_by = 0
      let fee = parseInt($("#chidon-fee").val())
      let codeFee = fee
      let poll = []
      $(".yahadus-poll").each(function () {
        if ($(this).is(":checked")) poll.push(this.value)
      })

      let shipCode, shipCodeBC
      if ([269, 61].includes(selected_user.school.school_id) && !selected_user.getChidonInfo) { // make sure not editing enrollment
        showClasses = 1;
        // figure out if we are adding codes for enrollment shipping / bc fee
        if (selected_user.school.school_id == 61) {
          shipCode = 'MYSLDS-10'
          codeFee -= 10
        } else if (selected_user.school.school_id == 269) {
          shipCode = 'AKLDS-10:AKLDBC-20' // for shipping and bc fee
          codeFee -= 30
        }
      }

      state.cart.push({
        description: Msg4 + selected_user.first + ' ' + (myshliach || anash ? selected_user.school.school_name : '') + (anash ? Msg5 : ''),
        price: fee,
        meta: {
          type: 'registration',
          user_id: selected_user.user_id,
          registration_type: 'chidon',
          paid: fee,
          track: $(".limmud:checked").val(),
          size: $("select#chidon-sweater-size").val(),
          yarmulka: $("select#yarmulka-size").val(),
          book: $("select#chidon-book").val(),
          purchased: $(".book-bought:checked").val(),
          purchasedWhere: $(".book-purchase:checked").length ? $(".book-purchase:checked").val() : '',
          store: {
            store_name: $("#store-name").val(),
            store_city: $("#store-city").val()
          },
          bookVersion: $("#bookVersion").val(),
          recruited: recruited,
          recruitedBy: recruited_by,
          poll: poll,
          name_pref: '',
          comments: $("#comments").val(),
          chidon_prizes: user_prizes[current_user],
          code: "C" + selected_user.user_serial + ":LDE-" + codeFee + (shipCode ? ":" + shipCode : '') + (shipCodeBC ? ":" + shipCodeBC : ''),
          codeOnly: 'LDE' + (shipCode ? ":" + shipCode : '') + (shipCodeBC ? ":" + shipCodeBC : ''),
          editingOnly: selected_user.getChidonInfo ? true : false
        }
      });
    }
    if (selected_charges.yahadus) {
      var shipping_included = selected_user.school.shipping_method !== 'pickup';
      var shipping_charge = 0;
      var cost = 40
      // const currentDateTime = new Date();
      // const otherDateTime = new Date("2024-09-19T00:00:00");
      // if (currentDateTime < otherDateTime) cost = 40
      if (selected_user.school.school_id == anash_kinder) {
        shipping_included = true; // override for anash kinder to make sure shipping is being charged
        if (usa.includes(selected_user.parentAccount.admin_country)) shipping_charge = 15;
        else return showError('You cannot order a Yahadus Book and have it shipped outside of the USA!');
      } else {
        // shipping to non USA schools is an additional $15
        let country = selected_user.school.shipping_country
        if (!usa.includes(country)) {
          shipping_charge = 15
        }
      }
      // // don't add to cart if anash kinder / myshliach and not in USA
      // if (
      //     ! [ 269, 61 ].includes( selected_user.school.school_id ) ||
      //     ( [ 269, 61 ].includes( selected_user.school.school_id ) && selected_user.parentAccount.admin_country.toUpperCase() == 'USA' )
      // ) {
      state.cart.push({
        description: Msg6 + $("select#chidon-book").val() + ' for ' + selected_user.first + (shipping_included ? Msg7 : ''),
        price: shipping_included ? (cost + shipping_charge) : cost,
        meta: {
          type: 'registration',
          user_id: selected_user.user_id,
          registration_type: 'yahadus',
          paid: shipping_included ? (cost + shipping_charge) : cost,
          code: "C" + selected_user.user_serial + ":YB" + $("select#chidon-book").val() + "-" + (shipping_included ? (cost + shipping_charge) : cost),
          codeOnly: 'YB' + $("select#chidon-book").val()
        }
      });
      // }
    }
    if (selected_charges.khk && khk_fee) {
      state.cart.push({
        description: selected_user.first + " KHK Enrollment",
        price: khk_fee,
        meta: {
          type: 'registration',
          registration_type: 'khk',
          paid: khk_fee,
          user_id: selected_user.user_id,
          code: "C" + selected_user.user_serial + ":KHKE-" + khk_fee,
          codeOnly: 'KHKE'
        }
      })
    }

    if (selected_charges.chidon && !selected_user.getChidonInfo) {
      // only need to save address if it wasn't already saved once
      if (!parseInt(selected_user.parentAccount['updated_address'])) {
        saveAddress('#chidon-address')
          .then(async function (saved) {
            if (saved) await nextStep()
          })
          .catch(function (err) {
            alert(err)
            return false
          })
      }
      else
        await nextStep()
    }
    else
      await nextStep()
  }

  async function saveAddress(elem) {
    // save address
    let address = {
      address1: $("#c-address").val().trim(),
      address2: $("#c-apt").val().trim(),
      city: $("#c-city").val().trim(),
      state: $("#c-state").val().trim(),
      zip: $("#c-zip").val().trim(),
      country: $("#c-country").val().trim()
    }

    // make sure there's a value for each field
    if (!(address.address1 && address.city && address.zip && address.country)) {
      alert("You must enter a value for your home address / city / zip / country")
      return false
    }

    // do some more validations
    // make sure address1 has at least 1 number followed by a space and at least 3 letters following
    if (address.country == 'USA' && !/^[0-9]+ .{3,}/.test(address.address1)) {
      alert("You must enter a valid address")
      $("#c-address").focus()
      return false
    }
    // make sure city has at least 3 letters
    if (address.city.length < 3) {
      alert("You must enter a valid city")
      $("#c-city").focus()
      return false
    }
    // make sure state has at least 2 letters
    if (address.state.length < 2) {
      alert("You must enter a valid state")
      $("#c-state").focus()
      return false
    }
    // make sure zip has at least 4 alphanumeric characters
    if (address.zip.length < 4) {
      alert("You must enter a valid zip code")
      $("#c-zip").focus()
      return false
    }
    // make sure country has at least 3 letters
    if (address.country.length < 3) {
      alert("You must enter a valid country")
      $("#c-country").focus()
      return false
    }

    // if country contains 'united states' or 'us' change to 'USA'
    if (address.country.toLowerCase().includes('united states')) {
      address.country = 'USA'
    }

    // Add confirmation prompt to shipping address
    const form = document.querySelector(elem)
    const result = await mapboxsearch.confirmAddress(form, {
      accessToken: ACCESS_TOKEN,
      theme: {variables: {border: '3px solid rgba(0,0,0,0.35)', borderRadius: '18px'}},
      minimap: {
        defaultMapStyle: ['mapbox', 'outdoors-v11'],
        satelliteToggle: true
      },
      skipConfirmModal: (feature) => {
        if (address.country == 'USA') return false // overrides default behavior, show dialog every time
        return true
      }
    });
    console.log(result)
    if (result.type == 'cancel') {
      alert('You must fix up your address before continuing')
      return false
    }

    const res = await $.post('updateAddress.php', {info: address})
    if (res.success) return true
    else {
      alert(res.error)
      return false
    }
  }

  const deviceType = () => {
    const ua = navigator.userAgent;
    if (/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i.test(ua)) {
      return "tablet";
    } else if (/Mobile|Android|iP(hone|od)|IEMobile|BlackBerry|Kindle|Silk-Accelerated|(hpw|web)OS|Opera M(obi|ini)/.test(ua)) {
      return "mobile";
    }
    return "desktop";
  };

  function checkForException(exceptions) {
    let school_id = selected_user.school.school_id
    for (let id of exceptions) {
      if (parseInt(id) == school_id) return true
    }
    return false
  }

  function setupChidonPrizes() {
    // initialize user prize cart
    user_prizes[current_user] = []
    // get prizes
    $.post('api/getPrizes.php', {user_id: current_user}, function (results) {
      var res = JSON.parse(results)
      // console.log(res)
      var html = ''

      // figure out height needed for items
      const width = $(window).width()
      // check for phones
      // let isMobile = window.matchMedia('only screen and (max-width: 768px)').matches;
      // if (! isMobile) {
      //     // check a different way
      //     const device = deviceType()
      //     if (device === 'mobile') isMobile = true
      // }
      // if (isMobile) {
      //     height = 'height: 170px;'
      // }

      // disable changing of prizes if prize has been paid for
      // disable changing of prizes after a certain date
      const date = new Date()
      const cutoff = new Date('2024-12-01') // cutoff date for changing prizes
      if (date > cutoff) disable = true

      for (let prize of res) {
        let disable = false // disable prize if it's personalized

        // check if prize is not suppose to show for this school
        if (prize.exceptions && prize.exceptions.length && checkForException(prize.exceptions)) continue

        let personalization = prize.personalization ? 1 : 0

        const id = prize.prize_id
        let height = 100
        if (personalization) height = 135
        if (width < 500) {
          if (width < 400) {
            height = 130
          }
          if (personalization) {
            if (width > 354) height = 185
            else height = 210
          }
        }
        // disable prizes that were already selected and paid for (all personalized prizes that have names need to be paid for)
        if (personalization && prize.selected && prize.he_name.trim().length > 1) disable = true
        html += `<div style="height: ${height}px; border-bottom: 1px solid #D3D3D3; margin-top: 10px;">
                        <img src="https://mashpia.com${prize.prize_picture}" style="float: right; height: 50px;" />
                        <input type="checkbox" class="prize ${personalization ? 'personalize' : ''}" name="prize_${id}" data-info="${id}:${prize.price}:${personalization}" 
                            data-qty="${prize.quantity}" `
        if (prize.selected) html += 'checked '
        // if (personalization) html += `onclick="alertPersonalization(this)" `
        // if (prize.quantity <= 0) html += 'disabled '
        if (disable) html += 'disabled '
        html += `/>
                        ${prize.prize_name} (<span class="prize-qty">${prize.quantity}</span> left in stock)<br />
                        ${parseInt(prize.price)} Credits`
        if (prize.color) {
          html += `<br />Color: ${prize.color}`
        }
        if (prize.size) {
          html += `<br />Size: ${prize.size}`
        }
        if (personalization) {
          html += `<br /><span style="font-size: 12px">${prize.personalization}. `
          if (id == 175) html += 'Limit 12 characters.'
          else html += 'Limit 22 characters.'
          html += ` <input type="text" name="he_name_${id}" id="he_name_${id}" class="he_name${id == 175 ? ' bracelet' : ''}" 
                        data-info="${id}" dir="rtl" value="${prize.he_name ? prize.he_name : ''}" `
          if (disable) html += 'disabled '
          html += `/></span>`
        }
        html += `</div>`

        if (prize.selected) addToPrizes(id, prize.price, personalization, false)
        if (prize.he_name) addHeName(id, prize.he_name)
      }
      $("#listOfPrizes").empty()
      $("#listOfPrizes").append(html)
      hebrew_keyboard.attach(".he_name"); // use hebrew in the right places
      // $("#prizes").modal('show')
      $("#prizes").modal({
        backdrop: 'static',
        keyboard: false,
        show: true
      })

      $(".prize").click(function (e) {
        var info = $(this).data('info')
        var infoArr = info.split(':')
        var prize = infoArr[0]
        var price = infoArr[1]
        var personalization = infoArr[2]
        var qty = parseInt($(this).data('qty'))
        var checked = $(this).is(":checked")

        if (checked) {
          e.target.checked = qty && addToPrizes(prize, price, personalization)
          if (! qty) alert('This prize has nothing left in stock, please choose a different prize.')
        } else {
          e.target.checked = !removeFromPrizes(prize)
          if (!e.target.checked) {
            $(this).parent().find(".he_name").val('')
            // update qty
            $(this).data('qty', qty + 1)
            $(this).parent().find('.prize-qty').text(qty + 1)
          }
        }
      })

      // $(".he_name").focus(function (e) {
      //   // check if we are going to go over 75 points
      //   let info = $(this).parent().parent().find('.personalize').data('info')
      //   let infoArr = info.split(':')
      //   let prize_id = infoArr[0]
      //   let price = infoArr[1]
      //   if (cartIsFull(price)) {
      //     let text = 'You cannot add this prize as it will put you over the 75 point limit.'
      //     setTimeout(function () {
      //       alert(text)
      //     }, 0)
      //     $(this).blur()
      //     return false
      //   }
      // })

      // $(".he_name").keyup(function (e) {
      //   let he_name = e.target.value
      //   // add prize to list
      //   if (he_name && !$(this).parent().parent().find('.prize').is(":checked")) {
      //     $(this).parent().parent().find('.prize').trigger('click')
      //   }
      // })

      $(".he_name").blur(function (e) {
        let he_name = e.target.value.trim()
        if (!he_name) return
        // add prize to list
        if (he_name.length && !$(this).parent().parent().find('.prize').is(":checked")) {
          $(this).parent().parent().find('.prize').trigger('click')
        }
        // add hebrew name to list
        let id = $(this).data('info')
        if (he_name.length && !addHeName(id, he_name)) {
          setTimeout(function () {
            alert('Error adding hebrew name')
            console.log(id, he_name)
          }, 0)
        } else if (he_name.length) {
          setTimeout(function () {
            alert("You have selected a prize with your child\'s name on it so you will need to pre-pay the prize value of this prize " +
              "NOW instead of during the Chidon Experience Registration. If your child does NOT earn their prize, you will NOT be refunded this charge. " +
              "If your child DOES pass, this money is automatically applied towards this child’s registration fee.\nIf you would like, you can go back " +
              "and remove the name from the prize or choose a different prize so that you don\'t need to prepay anything now. ")
          }, 0)
        }
      })
    })
  }

  let prizes_are_good = true

  $("#prizes").on('hide.bs.modal', async (e) => {
    // e.preventDefault()
    prizes_are_good = validatePrizes()
    if (prizes_are_good) {
      $(".confirm").attr('disabled', true)
      await checkForPrizeFee()
      $(".confirm").attr('disabled', false)
      addToCart() // add prizes to cart
      $("#prizes").modal('hide')
      await nextStep()
    }
  })

  $("#prizes").on('hidden.bs.modal', (e) => {
    if (!prizes_are_good) $("#prizes").modal('show')
  })

  function validatePrizes() {
    if (!user_prizes[current_user] || !user_prizes[current_user].length) {
      alert('You must choose which prizes you would like to receive if you are eligible!')
      return false
    }

    // make sure cart does not have more than 75 points worth of prizes
    if (cartIsFull()) {
      alert('You cannot have more than 75 points worth of prizes selected.')
      return false
    }

    var total = 0
    // make sure that he name was filled out if its needed
    for (var p of user_prizes[current_user]) {
      total += parseInt(p.price)
      if (parseInt(p.personalization) && (!p.he_name || p.he_name == '')) {
        let conf = confirm('You have indicated that you want a prize that needs a hebrew name, but you have not entered the name, are you sure you want to continue?')
        if (!conf) return false
      }
    }
    // make sure we have at least 65 credits
    if (total < 65) {
      alert('You must choose at least 65 credits worth of prizes.')
      return false
    }

    return true
  }

  function checkPersonalizedPrize() {
    // check if any prizes that have names have been selected
    personalized_amount = 0
    for (var p of user_prizes[current_user]) {
      if (p.he_name && p.he_name.trim().length) {
        personalized_amount = parseInt(p.price)
        break;
      }
    }
    return personalized_amount
  }

  async function checkPrePayment() {
    const advancedPayment = parseInt($("#chidon-reg").val())
    if (!advancedPayment) {
      chidonPayment[current_user] = true
      await nextStep()
    } else {
      checkForRegShipping = true // set flag so we know to check for shipping
      // create text for modal
      let options = []
      const track = $(".limmud:checked").val()
      switch (track) {
        case 'maven':
          options = [50, 75, 100, 136, 150]
          break
        case 'pro':
          options = [110, 136, 150, 180, 200]
          break
        case 'expert':
        case 'genius':
          options = [210, 225, 250, 300]
          break
      }

      let html = `
                <div class="col-12" style="padding: 10px 20px;">
                  Important Note: This amount is on top of any personlized item chosen during prize selection - as that amount is non-refundable and will be due today.<br/>
                  Example: If you plan to pass Havonah which will be $200 at Registeration, and you chose a $75 personalized item, 
                  you would only need to pay at Registeration $125, so the amount you choose to prepay should reflect that amount.<br /><br/>
                  Please choose the amount you would like to pre-pay towards your family’s account for Chidon Registration<br/>
                    <select id="chidonReg" class="form-control" style="margin-top: 10px;">
                      <option value="0">Choose Amount</option>
                      ${options.map(o => `<option value="${o}">$${o}</option>`).join('')}
                    </select>
                </div>
                <div class="col-12" style="padding: 10px 20px;">
                  <label for="early-reg-terms">
                    <input type="checkbox" id="early-reg-terms" name="early-reg-terms" style="display: inline !important; width: 25px; height: 25px;" />
                    I am aware that if my child ends up passing a lower track, 
                    so there is a lower registration fee, I will need to opt in for a refund of the difference at registration. 
                    If my child passes a higher track, I will need to pay the difference during registration.
                  </label>
                </div>
            `
      $("#chidon-enrollment .modal-body").empty().append(html)
      $("#chidon-enrollment").modal('show')
    }
  }

  async function alreadyPaidPrize(amount) {
    const res = await fetch('api/checkRegistrationPayment.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({user: current_user, amount: amount})
    })
    const paid = await res.json()
    return paid
  }

  $("#chidon-enrollment").on('hidden.bs.modal', async function (e) {
    let amount = parseInt($("#chidonReg").val())
    if (!amount) {
      alert('You must select an amount to pay for early chidon experience registration!')
      $("#chidon-enrollment").modal('show')
    } else {
      // check that terms are accepted
      if (!$("#early-reg-terms").is(":checked")) {
        alert('You must accept the terms for early registration!')
        $("#chidon-enrollment").modal('show')
        return false
      }

      // change amount if personalized prize was selected
      let personalized_amount = checkPersonalizedPrize()
      amount -= personalized_amount
      if (amount < 0) amount = 0

      if (amount > 0) {
        // add to cart
        const trackCode = 'RRFAM-'
        const family_id = selected_user.parentAccount.admin_id
        const index = state.users.findIndex(user => user.user_id === selected_user.user_id)
        state.cart.push({
          description: state.users[index].parentAccount.last + " Family Chidon Pre-Registration Payment",
          price: amount,
          meta: {
            type: 'advance registration',
            registration_type: 'chidon',
            paid: amount,
            user_id: current_user,
            code: "F" + family_id + ":" + trackCode + amount,
            codeOnly: trackCode.substring(0, trackCode.length - 1)
          }
        })
      }

      chidonPayment[current_user] = true
      await nextStep()
    }
  })

  function cartIsFull(price = 0) {
    let MAX = 75
    let total = 0
    for (var p of user_prizes[current_user]) {
      total += parseInt(p.price)
    }
    total += parseInt(price)
    return total > MAX
  }

  function addToPrizes(prize, price, personalization, checkCart = true) {
    // check if we can add it
    if (checkCart && cartIsFull(price)) {
      let text = 'You cannot add this prize as it will put you over the 75 point limit.'
      setTimeout(function () {
        alert(text)
      }, 0)
      return false
    }
    // check if it's already in the cart
    let found = false
    for (var p of user_prizes[current_user]) {
      if (p.id == prize) {
        found = true
      }
    }
    if (!found) {
      let prizeToAdd = {id: prize, price: price, personalization: personalization}
      user_prizes[current_user].push(prizeToAdd)
      return true
    }
  }

  function removeFromPrizes(prize) {
    for (var i in user_prizes[current_user]) {
      var p = user_prizes[current_user][i]
      if (p.id == prize) {
        user_prizes[current_user].splice(i, 1)
        return true
      }
    }
    return false
  }

  function addHeName(prize_id, he_name) {
    for (let p in user_prizes[current_user]) {
      if (user_prizes[current_user][p].id == prize_id) {
        let prize = user_prizes[current_user][p]
        prize.he_name = he_name
        return true
      }
    }
    return false
  }

  function addToCart() {
    for (let item of state.cart) {
      if (item.meta.registration_type == 'chidon' && item.meta.user_id == current_user) {
        item.meta.chidon_prizes = user_prizes[current_user]
      }
    }
  }

  async function checkForPrizeFee() {
    // check if personalized prize was selected
    let amount = checkPersonalizedPrize()
    if (amount > 0) {
      // check if child prize has already been paid
      already_paid = await alreadyPaidPrize(amount)
      if (!already_paid) {
        // find out track for code
        const track = $(".limmud:checked").val()
        let trackCode = ''
        switch (track) {
          case 'maven':
            trackCode = 'RRYSD-'
            break
          case 'pro':
            trackCode = 'RRYDA-'
            break
          case 'expert':
          case 'genius':
            trackCode = 'RRHVN-'
            break
          default:
            trackCode = 'RRHVN-' // default to havonah
            break
        }

        // check if it's already in cart using code, user_id and amount
        let exists = state.cart.filter(item => {
          item.meta.codeOnly === trackCode.substring(0, trackCode.length - 1)
          && item.meta.user_id === selected_user.user_id
          && item.price === amount
        })
        if (! exists.length) {
          // add to cart
          state.cart.push({
            description: selected_user.first + " Early Chidon Payment for Personalized Prize (Non-Refundable)",
            price: amount,
            meta: {
              type: 'advanced prize payment',
              registration_type: 'chidon',
              paid: amount,
              user_id: selected_user.user_id,
              code: "C" + selected_user.user_serial + ":" + trackCode + amount,
              codeOnly: trackCode.substring(0, trackCode.length - 1)
            }
          })
        }
      }
    }
  }

  async function nextStep() {
    // first check if we need to show prizes or early chidon registration
    if (checkForChidonReg()) {
      if (!user_prizes[current_user] || !user_prizes[current_user].length) {
        setupChidonPrizes()
        return false
      }
      if (!chidonPayment[current_user]) {
        checkPrePayment()
        return false
      }
    }
    current_index += 1;
    if (state.selected_users.length <= current_index) {
      if (checkForChayoleiReg() && !hachayolChosen) {
        current_index -= 1 // reverse adding to index
        chooseHachayols()
        return false
      }
      if (checkForRegShipping) { // check regardless whether this user is registering for chidon or not
        current_index -= 1 // reverse adding to index
        checkRegShipping()
        return false
      }
      // if child is editing enrollment, make sure that they acknowledge that everything is correct
      if (selected_user.getChidonInfo) {
        const update = confirm("By clicking on 'OK' you are confirming that all information is correct and accurate. " +
          "If NOT, please click on 'Cancel' so that you can make more changes.")
        if (! update) {
          user_prizes[current_user] = [] // reset prizes
          return false
        }
      }
      return step3()
    } else {
      selected_user = state.selected_users[current_index]
      current_user = selected_user.user_id // update current_user
      school_id = selected_user.school.school_id
      templates.showUser(selected_user, current_index);
      $('html, body').animate({scrollTop: 0}, 'fast'); // scroll to the top of the page
    }
  }

  async function checkRegShipping() {
    const registering = state.cart.filter(item => item.meta.type === 'advance registration' && item.meta.registration_type === 'chidon')
    // get index of user in users array
    const index = state.users.findIndex(user => user.user_id === selected_user.user_id)

    // make sure that if editing chidon info, we check if parent already paid for shipping
    const paid = await fetch('api/checkShippingFee.php', {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({admin: selected_user.parentAccount.admin_id}),
    })
    if (parseInt(paid)) {
      checkForRegShipping = false
      await nextStep()
    }

    const school_id = state.users[index].school.school_id
    const country = state.users[index].parentAccount.admin_country
    const shippingFee = calcShippingFee(school_id, country, registering.length)

    if (shippingFee) {
      // show modal
      let html = `
        <div class="col-12" style="padding: 10px 20px;">
            <label for="chidon-shipping-fee">Early Registration Shipping Fee</label><br />
            <input type="radio" name="chidon-shipping-fee" id="chidon-shipping-fee" value="1" checked /> 
            I would like to have my registration items shipped to my address on file for an additional charge of $${shippingFee}<br />
            <input type="radio" name="chidon-shipping-fee" id="chidon-shipping-fee" value="0" /> 
            I will be picking up my registration items (Free)
        </div>
      `
      $("#chidon-shipping .modal-body").empty().append(html)
      $("#chidon-shipping").modal('show')

      $("#chidon-shipping").on('hidden.bs.modal', async function (e) {
        // check if shipping option was selected
        if (!$("#chidon-shipping-fee:checked").val()) {
          alert('You must select a shipping option!')
          $("#chidon-shipping").modal('show')
          return false
        } else {
          let shipping = parseInt($("#chidon-shipping-fee:checked").val())
          // if (shipping) {
          // figure out code to use
          let shipCode = ''
          if (country === 'USA') shipCode = 'RRSUSA-'
          else if (country === 'Canada') shipCode = 'RRSCAN-'
          else shipCode = 'RRSINT-' // international
          // for some reason this is showing more than once under certain circumstances
          // check if it doesn't already exist
          const exists = state.cart.filter(item => item.meta.codeOnly === shipCode.substring(0, shipCode.length - 1))
          const shipping_amount = shipping ? shippingFee : 0
          if (!exists.length) {
            state.cart.push({
              description: state.users[index].parentAccount.last + " Family Early Chidon Registration Shipping",
              price: shipping_amount,
              meta: {
                type: 'advance shipping registration',
                registration_type: 'shipping',
                paid: shipping ? shippingFee : 0,
                user_id: state.users[index].user_id,
                code: "F" + state.users[index].parentAccount.admin_id + ":" + shipCode + shipping_amount,
                codeOnly: shipCode.substring(0, shipCode.length - 1)
              }
            })
          }
          // }
          checkForRegShipping = false
          await nextStep()
        }
      })
    } else {
      checkForRegShipping = false
      await nextStep()
    }
  }

  function checkForChayoleiReg() {
    const reg = state.cart.filter(item => item.meta.user_id === selected_user.user_id && item.meta.type === 'registration'
      && item.meta.registration_type === 'chayolei')
    return reg.length
  }

  function checkForChidonReg() {
    const reg = state.cart.filter(item => item.meta.user_id === selected_user.user_id && item.meta.type === 'registration'
      && item.meta.registration_type === 'chidon')
    return reg.length
  }

  function calculateAge(dob) {
    let today = new Date()
    let birthDate = new Date(dob)
    let age = today.getFullYear() - birthDate.getFullYear()
    let m = today.getMonth() - birthDate.getMonth()
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
      age--
    }
    return age
  }

  function getOldestChild(children) {
    // find oldest child that is 10 yrs old or under (registered OR currently registering)
    // filter for children who are already registered OR currently being registered for chayolei
    const registeringChildren = children.filter(c => {
      // Check if already registered
      if (c.user_registered && c.user_registered !== 'null' && c.user_registered !== '0000-00-00') return true
      
      // Check if currently being registered in cart
      const isRegistering = state.cart.some(item => 
        item.meta.user_id === c.user_id && 
        item.meta.type === 'registration' && 
        item.meta.registration_type === 'chayolei'
      )
      return isRegistering
    })
    
    const childrenToCheck = registeringChildren.length > 0 ? registeringChildren : children
    
    let oldest = childrenToCheck[0].user_id
    let oldestAge = calculateAge(childrenToCheck[0].dob)
    for (let c in childrenToCheck) {
      let age = calculateAge(childrenToCheck[c].dob)
      if (age > 10) continue
      if (age > oldestAge) {
        oldest = childrenToCheck[c].user_id
        oldestAge = age
      }
    }
    return oldest
  }

  async function chooseHachayols() {
    // get children from both users and registered without duplicates
    const allChildren = [...state.users, ...state.registered];
    const children = allChildren.filter((child, index, arr) => 
      arr.findIndex(c => c.user_id === child.user_id) === index
    );
    
    // Filter to only show registered or currently registering children
    const eligibleChildren = children.filter(c => {
      // Check if already registered
      if (c.user_registered && c.user_registered !== 'null' && c.user_registered !== '0000-00-00') return true
      
      // Check if currently being registered in cart
      const isRegistering = state.cart.some(item => 
        item.meta.user_id === c.user_id && 
        item.meta.type === 'registration' && 
        item.meta.registration_type === 'chayolei'
      )
      return isRegistering
    })
        
    // get children that are set to get hachayol
    let oldest = null
    const response = await fetch('/ajax/hachayols/getHachayolsInfo.php')
      .then(res => res.json())
    if (!response.success) {
      alert('Could not get hachayols information.')
      return false
    }
    
    let hachayols = response.hachayols
    
    // Check if existing hachayols are for non-eligible children
    const ineligibleHachayols = hachayols.filter(hId => {
      return !eligibleChildren.some(c => c.user_id === hId)
    })
    
    // If there are ineligible hachayols and we have eligible children, swap them
    if (ineligibleHachayols.length > 0 && eligibleChildren.length > 0) {
      // Remove ineligible hachayols from the list
      hachayols = hachayols.filter(hId => !ineligibleHachayols.includes(hId))
      
      // Get the oldest eligible child to replace them
      oldest = getOldestChild(eligibleChildren)
      if (oldest && !hachayols.includes(oldest)) {
        hachayols.push(oldest)
      }
    }
    
    // if no hachayols set, get oldest child and add to hachayols
    if (!hachayols.length && eligibleChildren.length > 0) {
      oldest = getOldestChild(eligibleChildren)
      hachayols.push(oldest)
    }
      
    let html = "<div style='margin-left: 2em; margin-top: -2em;'>"
    for (let c in eligibleChildren) {
      let child = eligibleChildren[c]
      html += `<label for="${child.user_id}">
                  <div style='float: left; margin-right: 10px;'>
                    <input type="checkbox" name="hachayol[]" class="hachayol" id="${child.user_id}" value="${child.user_id}" 
                      ${( hachayols.includes(child.user_id) ? 'checked' : '')} /> 
                    <span class="checkbox"></span>
                  </div>
                  <div>
                      ${child.first} ${child.last}
                  </div>
              </label>
              <br />`
    }
    html += "</div>"

    $("#hachayolChildren").empty().append(html)
    $("#hachayol").modal('show')
    $("#hachayol").on('hidden.bs.modal', function () {
      processHachayols(hachayols, eligibleChildren, ineligibleHachayols)
    })
  }

  $(document).on('click', '.hachayol', function (e) {
    // find out if there are any other checks
    let numChecked = $(".hachayol:checked").length
    if (!numChecked && !$(this).is(":checked")) {
      // if not, keep this child checked
      alert('You must have at least one child checked.')
      e.preventDefault()
    }
  })

  async function processHachayols(hachayols, eligibleChildren, ineligibleHachayols = []) {
    const numChecked = $(".hachayol:checked").length
    if (numChecked < hachayols.length) {
      alert('You must have at least ' + hachayols.length + ' child(ren) checked.')
      $("#hachayol").modal('show')
      return false
    }

    // if numChecked is more than hachayols.length, we need to add to cart
    let addedToCart = []
    if (numChecked > hachayols.length) {
      for (let i = hachayols.length; i < numChecked; i++) {
        const id = $(".hachayol:checked").eq(i).val()
        const family_id = eligibleChildren.find(user => user.user_id == id).parentAccount.admin_id
        // check if this child is already in the cart
        const alreadyInCart = state.cart.find(item => item.meta.user_id == id && item.meta.type == 'hachayol')
        if (alreadyInCart) continue
        state.cart.push({
          description: `Extra Hachayol Fee (ID: ${id})`,
          price: HACHAYOL_FEE,
          meta: {
            type: 'hachayol',
            paid: HACHAYOL_FEE,
            user_id: id,
            code: "F" + family_id + ":HACH-" + HACHAYOL_FEE,
            codeOnly: 'HACH'
          }
        })
        addedToCart.push(id)
      }
    }

    let toAdd = []
    let toRemove = [...ineligibleHachayols] // Start with ineligible hachayols to remove
    $(".hachayol").each(function () {
      let id = parseInt($(this).val())
      let checked = $(this).is(":checked")
      
      if (checked) {
        // only add if not already added to cart (all children in modal are eligible)
        if (!addedToCart.includes(id)) {
          toAdd.push(id)
        }
      } else {
        // only add to remove if not already in the ineligible list
        if (!toRemove.includes(id)) {
          toRemove.push(id)
        }
      }
    })
    console.log('toAdd:', toAdd)
    console.log('toRemove (including ineligible):', toRemove)

    if (toAdd.length || toRemove.length) {
      const result = await $.post('/ajax/hachayols/updateHachayols.php', { toAdd, toRemove })
      const res = JSON.parse(result)
      if (!res.success) {
        alert(res.error)
        $("#hachayol").modal('show')
        return false
      }
    }
    hachayolChosen = true
    await nextStep()
  }

  function confirmShipping(event) {
    event.preventDefault();
    // remove any old shipping items from the cart
    state.cart = state.cart.filter(function (item) {
      return item.meta.type != 'shipping'
    });
    // get shipping charge if exists
    let shipping_charge = parseInt($("#shipping-cost").val())
    let school_id = selected_user.school.school_id

    let addShipping = false;
    if (shipping_charge > 0) {
      addShipping = true
      if (school_id == 61 && shipping_charge == 45 && $("#cancel-shipping").is(":checked")) addShipping = false
    }

    if (addShipping) {
      // figure out code for shipping
      let shipCode = ''
      const country = selected_user.parentAccount.admin_country
      if (school_id == 269) {
        if (country === 'USA') shipCode = 'THAKUSA-'
        else if (country === 'Canada') shipCode = 'THAKCAN-' // canada
        else shipCode = 'THAKINT-' // international
      } else if (school_id == 61) {
        if (country === 'USA') shipCode = 'THMSUSA-'
        else if (country === 'Canada') shipCode = 'THMSCAN-' // canada
        else shipCode = 'THMSINT-' // international
      }
      // add shipping to cart
      state.cart.push({
        description: 'Prepaid Chayolei Shipping',
        price: shipping_charge,
        meta: {
          type: 'shipping',
          paid: shipping_charge,
          user_id: selected_user.user_id,
          shipping_type: state.shipping_type,
          shipping_charges: shipping_charge,
          code: "F" + selected_user.parentAccount.admin_id + ":" + shipCode + shipping_charge,
          codeOnly: shipCode.substring(0, shipCode.length - 1)
        }
      })
    }

    return step4();
  }

  function registerUsersHandler(event) {
    event.preventDefault();
    // prevent double submission (can cause "active transaction" error if two requests hit same DB connection)
    var $btn = $("#payment-button");
    if ($btn.prop('disabled')) return;
    $btn.prop('disabled', true);
    var postData = {};
    // show loading
    $btn.html('<i class="fas fa-circle-notch fa-spin fa-2x"></i>');
    postData.payment = formToJSON(event.target);
    // sanitize input
    postData.payment["cc-number"] = postData.payment["cc-number"].replace(/ /g, '');
    postData.payment["cc-exp"] = postData.payment["cc-exp"].replace(/ /g, '');
    postData.payment["x_card_code"] = postData.payment["x_card_code"].replace(/ /g, '');
    postData.payment["zip"] = postData.payment["zip"].replace(/ /g, '');
    // validate form
    if (postData.payment["cc-number"]) {
      event.target.checkValidity();
      $(event.target).addClass('was-validated');
    }
    postData.payment['installments'] = $(".installments:checked").val()
    postData.cart = state.cart.map(function (item) {
      return item.meta
    })
    registerUsers(postData);
  }

  /*********************** HELPER FUNCTIONS ***********************/
  // Calculate months between two dates
  function monthsBetween(date1, date2) {
    // Ensure date1 is earlier than date2
    let d1 = new Date(date1);
    let d2 = new Date(date2);
    if (d1 > d2) [d1, d2] = [d2, d1];

    const years = d2.getFullYear() - d1.getFullYear();
    const months = d2.getMonth() - d1.getMonth();
    let totalMonths = years * 12 + months;

    // Adjust if the day of the month in d2 is less than in d1
    if (d2.getDate() < d1.getDate()) {
      totalMonths -= 1;
    }
    return totalMonths;
  }

  // navigate to a specific section
  function showSection(id) {
    $("#pages section").hide();
    $("#pages section#" + id).show();
  }

  $(window).bind('hashchange', function () {
    // support sub-routing in step-2
    if (window.location.hash.match(/^#step-2-[0-9]/g)) {
      var index = parseInt(window.location.hash.split('-')[2]);
      templates.showUser(state.selected_users[index], index);
      if ($("section#step-2").css('display') == 'none') showSection('step-2');
    } else if ($("section" + window.location.hash).css('display') == 'none') {
      if (window.location.hash === '#step-1') return step1();
      if (window.location.hash === '#step-2') return step2();
      if (window.location.hash === '#step-3') return step3();
      if (window.location.hash === '#step-4') return step4();
    }
  });

  // toggle the loading dot
  function toggleLoading(id, loading) {
    id = "#" + id;
    if (!loading) {
      $(id + " .spinner").hide();
      $(id + " .content").show();
    } else {
      $(id + " .spinner").show();
      $(id + " .content").hide();
    }
  }

  // Auto-detect credit card type
  function validateCardInput(event) {
    var cardInput = $("#cc-number");
    cardInput.removeClass("visa mastercard amex discover");
    $("#x_card_code").attr("placeholder", "XXX");
    // digits only
    if (event.key && !event.key.match(/[0-9]/))
      event.target.value = event.target.value.replace(/[^0-9 ]/g, '');
    // decect card type from: https://www.regular-expressions.info/creditcard.html
    var cardNumber = event.target.value.replace(/\D/g, '');
    if (cardNumber.match(/^4[0-9]{12}(?:[0-9]{3})?$/g)) {
      cardInput.addClass("visa");
    } else if (cardNumber.match(/^(?:5[1-5][0-9]{2}|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}$/g)) {
      cardInput.addClass("mastercard");
    } else if (cardNumber.match(/^3[47][0-9]{13}$/g)) {
      cardInput.addClass("amex");
      $("#x_card_code").attr("placeholder", "XXXX");
    } else if (cardNumber.match(/^6(?:011|5[0-9]{2})[0-9]{12}$/g)) {
      cardInput.addClass("discover");
    }
  }

  /*********************** API CALLS ***********************/
  function getUsers() {
    return new Promise(function (resolve, reject) {
      APIRequest('GET', api_url + '?action=getUsers', {}, function (response) {
        state.users = [];
        response.users.forEach(function (user) {
          user.dob = user.dob ? user.dob.split(" ")[0] : user.dob;
          if (user.user_registered && user.user_registered !== '0000-00-00') state.registered.push(user)
          if (user.registrationStatus.chayolei === false || user.registrationStatus.chidon === false ||
            user.registrationStatus.chidonEdit === true)
            // if ( user.registrationStatus.chayolei === false || user.registrationStatus.chidon === false )
            state.users.push(user);
        });
        if (state.users.length === 1) state.selected_users = state.users;
        resolve(state.users);
      });
    });
  }

  // TODO, update to point to unified core API
  function updateUser(user_id, postData) {
    return new Promise(function (resolve, reject) {
      $.post("/api/core/users?id=" + user_id, postData, function (response) {
        if (!response.success) {
          showError(Err16);
        }
        resolve(response);
      });
    });
  }

  function getShipping(school_ids) {
    return new Promise(function (resolve, reject) {
      APIRequest('POST', api_url + '?action=getShipping', {school_ids: school_ids}, resolve);
    });
  }

  function getPaymentProfiles() {
    return new Promise(function (resolve, reject) {
      APIRequest('GET', '/api/payments/profiles.php', {}, resolve);
    });
  }

  function registerUsers(postData) {
    console.log(postData)
    var $btn = $("#payment-button");
    $btn.prop('disabled', true);
    return new Promise(function (resolve, reject) {
      APIRequest('POST', api_url + '?action=registerUsers', postData, resolve)
    }).then(function (data) {
      if (typeof data === 'string' || typeof data === 'object' && data.success) {
        if (showClasses) $("#successModal #myshliach-extra").html("<br /><p>" + Msg8 + "<a href='https://merkos302.formstack.com/forms/chidon_shiurim_registration'>" + Msg9 + "</a></p>")
        // figure out if we are updating or enrolling
        let editing = false
        for (let user of postData.cart) {
          if (user.editingOnly) {
            editing = true
            break
          }
        }
        if (editing) $("#updatingText").show()
        else $("#enrollingText").show()
        $("#successModal").modal('show')
      } else {
        showError(data.error);
        $btn.prop('disabled', false).html('Pay');
      }
    });
  }

  /**
   * onImageUploaded
   *
   * callback for when image is uploaded to server.
   *
   * updates the UI and form ( steps 1 and 2 ) and attempts to update the users profile picture
   *
   * @param {object} data
   */
  function onImageUploaded(data) {
    var user_id = $("#step-2 form #user_id").val();

    $("input#mobile_pic").val(data.filename);
    $("img#user-img, #child-" + user_id + " img").attr("src", data.location);

    $.post("/api/core/users?id=" + user_id, {mobile_pic: data.filename}, function (response) {
      if (!response.success) {
        showError(Err12);
      } else {
        pic_uploaded = true
      }
    });
  }
}();

var templates = function () {
  return {
    child: function (child) {
      return '<div class="child col-12 col-lg-6" id="child-' + child.user_id + '"><label>' +
        '<div class="row">' +
        '<div class="col-4">' +
        '<img src="' + child.profilePicture + '" />' +
        '</div><div class="col-6">' +
        '<p class="name">' + child.first + " " + child.last + '<div style="font-size: 1em;">Serial: ' + child.user_serial + '</div></p>' +
        /*
                        ( child.registrationStatus.chayolei === false ?
                            child.school.inst_id === 10 ? ( '<p class="reg_cost">CKids Registration: $0</p>' ) :
                            ( '<p class="reg_cost">Tzivos Hashem: $' + (!child.registrationStatus.confirmation ? child.registrationRates.chayolei : 0) ) : '' ) +
                            // ( '<p class="reg_cost">Tzivos Hashem: $' + child.registrationRates.chayolei + '<br />($0 Free Lite Edition)</p>' ) : '' ) +
                        ( child.registrationStatus.chidon === false ?
                            ( '<p class="reg_cost">Chidon: $' + child.registrationRates.chidon + '</p>' ) : '' ) +
                         */
        '</div><div class="col-2">' +
        '<input type="checkbox" data-user_id="' + child.user_id + '" />' +
        '<span class="checkbox"></span>' +
        '</div>' +
        '</div>' +
        '</label></div>';
    },
    beforeDate: function (date) {
      const targetDate = new Date(date);
      const now = new Date();
      // Convert both dates to EST
      const targetEST = new Date(targetDate.toLocaleString('en-US', {timeZone: 'America/New_York'}));
      const nowEST = new Date(now.toLocaleString('en-US', {timeZone: 'America/New_York'}));
      // Compare the dates
      return targetEST > nowEST;
    },
    setChidonReg: async function (user) {
      /*
            There's a couple of different scenarios that can be happening
            1. a non-registered child is shown for the first time
            2. a non-registered child is shown by coming "back" to him/her
            3. a non-registered child is shown "after" a child who was previously registered
            4. a registered child is shown for editing "first" time
            5. a registered child is shown by coming "back" to that child (so child has both original info and cart info)
            **/
      let html = '<option value="0">Select Amount to Pay</option>'
      let fees = [25, 30, 40, 50]
      if (user.school.school_id == 61) fees = [36, 40, 45]
      else if (user.school.school_id == 269) fees = [55, 60, 65, 70]

      async function checkChangeFees() {
        const res = await fetch('api/checkChangeFees.php')
        const data = await res.json()
        return data
      }

      // const user_exceptions = [72142, 60368, 60368, 75325, 81279, 81280, 81281, 81320, 25391, 63651]
      // const school_exceptions = [87, 659]
      const user_exceptions = []
      const school_exceptions = []

      if (
        !user_exceptions.includes(user.user_id) && 
        !school_exceptions.includes(user.school.school_id) &&
        await checkChangeFees()
      ) {
        fees = [50]
        if (user.school.school_id == 61) fees = [60]
        else if (user.school.school_id == 269) fees = [80]
      }

      for (let fee of fees) {
        html += `<option value="${fee}">$${fee}</option>`
      }
      $("#chidon-fee").empty().append(html)

      if (!user.getChidonInfo) {
        // non registered child
        // check if coming after a prev registered child
        if ($("#chidon-reg-text").html().includes('You have already enrolled for')) {
          $("#chidon-reg-text").html('<strong>I would like to enroll for the Chidon Limmud Program</strong>')
          document.getElementById('chidon').checked = false
          // reset chidon fee
          $("#chidon-fee").html(html)
          $("#chidon-fee").show()
          $("#reg_text").show()
        }

        $("#khk-reg-info").show()
        $("#khk-edit-info").hide()

        // determine text for new enrollment
        let text = "The Chidon Limmud (Chidon Kop Cards, the Study Guide & 3 tests) costs $40 per child " +
          "and is subsidized by our generous donors. I would like to pay:"
        if (user.school.school_id == 61) text = "The Chidon Limmud (Chidon Kop Cards, the Study Guide, " +
          "3 tests & Shipping) costs $50 per child and is subsidized by our generous donors. I would like to pay:"
        if (user.school.school_id == 269) text = "The Chidon Limmud (Chidon Kop Cards, the Study Guide, " +
          "3 tests Shipping & Chidon Coordinator) costs $70 per child and is subsidized by our generous donors. I would like to pay:"
        $("#reg_text").empty().append(text)
        $("#advanced-registration").show()
        if (parseInt(user.parentAccount['updated_address'])) $("#chidon-address").hide()
        $("#chidon-learning").show()
        $("#chidon-books").show()
        $("#agreements").show()
      } else {
        document.getElementById('chidon').checked = true
        $("#chidon").attr('disabled', true)
        $("#chidon-fee").html("<option value='0'>$0</option>")
        $("#chidon-fee").attr('disabled', true)
        $("#chidon-fee").hide()
        $("#reg_text").hide()
        $("#chidon-registration").find('#chidon-reg-text').html(`
                        <strong>You have already enrolled for $${user.getChidonInfo.amount}, the payment can not be edited. 
                    `)
        $("#chidon-details").show()
        // also khk
        if (user.getChidonInfo.khk_reg === '1') {
          $("#khk-reg-info").hide()
          $("#khk-edit-info").show()
        } else {
          $("#khk-reg-info").show()
          $("#khk-edit-info").hide()
        }
        // $("#advanced-registration").hide()
        // $(".limmud").attr('disabled', true)
        // $(".recruit").attr('disabled', true)
        // $("#yarmulka-size").attr('disabled', true)
        // $("#chidon-sweater-size").attr('disabled', true)
        $("#chidon-address").hide()
        $("#chidon-learning").hide()
        // $("#chidon-books").hide()
        $("#agreements").hide()
        $(".yahadus-poll").attr('disabled', true)
        // $("#c-address").attr('disabled', true)
        // $("#c-apt").attr('disabled', true)
        // $("#c-city").attr('disabled', true)
        // $("#c-state").attr('disabled', true)
        // $("#c-zip").attr('disabled', true)
        // $("#c-country").attr('disabled', true)
        // $("#chidon").parent().empty()
      }
    },
    showUser: function (user, index) {
      if (index > 0) window.location.hash = 'step-2-' + index;

      $(".chayolei_year").text(user.regYears.chayolei)
      $(".chidon_year").text(user.regYears.chidon)

      // hide all sections that only should be shown under certain circumstances
      $(".hide").hide();

      // reset all fee checkboxes
      $('#chayolei-registration input')[0].checked = false

      $('#chidon-registration input')[0].checked = false
      $('#yahadus-registration input')[0].checked = false
      $('#khk input')[0].checked = false

      // hide chayolei reg if not applicable
      if (user.registrationStatus.chayolei) $("#chayolei-registration").hide()
      else {
        // reset chayolei reg checkbox if needed
        if (document.getElementById('chayolei').checked) document.getElementById('chayolei').checked = false
        $("#chayolei-registration").show()
      }

      // hide chidon reg if not applicable
      if (user.registrationStatus.chidon) $("#chidon-registration").hide()
      if (Cookies.get('naftoli') && user.getChidonInfo) $("#chidon-registration").show()
      // if (user.registrationStatus.chidon && !user.getChidonInfo) $("#chidon-registration").hide()
      // else $("#chidon-registration").show()

      // determine if we need to show non th school fields or not
      if (user.school.school_id === anash_kinder || user.school.school_id === myshliach) {
        $('#non_th_school_id').val(user.non_th_school_id);
        $('#non_th_school').val(user.non_th_school);
        if (non_th_schools[user.non_th_school_id]) {
          let school_info = non_th_schools[user.non_th_school_id].split(',')
          // alert(school_info)
          $("#non-th-city").val(school_info[1])
          $("#non-th-state").val(school_info[2])
          $("#non-th-zip").val(school_info[3])
          $("#non-th-country").val(school_info[4])
        }
        $("#non_th_school_div").show()
      }

      // add the dropdown for naftali
      var class_select = $('#class_name select');
      class_select.html('');
      if ([269, 61].includes(user.school.school_id)) {
        // get the class list and update the dropdown
        $.get("api/classes.php", {'school_id': user.school.school_id}, function (response) {
          class_select.html('');
          response.data.forEach(function (option) {
            class_select.append("<option value='" + option.id + "'>" + option.name + "</option>");
          });
          class_select.val(user.class_id);
          $('#class_name').show(); // make sure it is visiable
        });
      } else {
        $('#class_name').hide(); // hide it
      }

      // setup the index state
      $('#step-2 form #current_index').val(index);

      // populate fields with current values
      $('#user_id').val(user.user_id);
      $('#mobile_pic').val(user.mobile_pic);
      $('#mobile_pic + img').attr('src', user.profilePicture);
      $('.gender[value=\'' + user.gender + '\']')[0].checked = true;
      $('#school_name').val(user.school.school_name);
      $('#first').val(user.first);
      $('#last').val(user.last);
      $('#first_he').val(user.first_he);
      $('#last_he').val(user.last_he);
      $('#lang_id').val(user.lang_id);
      $('#dob').val(user.dob);

      // setup the payment options
      templates.toggleRates(user);

      // add shipping text where appropriate
      if (user.school.school_id === anash_kinder) {
        if (usa.includes(user.parentAccount.admin_country)) $("#yahadus-shipping").html("There is an extra shipping charge of <b>$15.</b>");
        else $("#yahadus-shipping").html("We do not currently ship internationally. Orders placed to another country will be canceled.")
      } else {
        if (usa.includes(user.school.shipping_country)) $("#yahadus-shipping").html("Your book will be shipped to your school free of charge.");
        else $("#yahadus-shipping").html("There is an extra shipping charge of <b>$15.</b>");
      }

      // show khk if relevant
      if (!user['registrationStatus']['khk']) {
        $("#khk").show()
        // $("#khkWhatsapp").show()
      } else {
        $("#khk").hide()
        // $("#khkWhatsapp").hide()
      }

      // $("#chidonWhatsapp").show()

      // show/hide yarmulka
      if (user.gender == 'M') {
        $("#yarmulka").show()
        // $("#boysWhatsapp").show()
        // $("#girlsWhatsapp").hide()
        // $("#khkBoys").show()
        // $("#khkGirls").hide()
      } else {
        $("#yarmulka").hide()
        // $("#girlsWhatsapp").show()
        // $("#boysWhatsapp").hide()
        // $("#khkBoys").hide()
        // $("#khkGirls").show()
      }

      if (user.registrationStatus.new_to_chidon == 1) {
        $("#chidonRecruitment").show()
        $("#recruited_by_user_serial").val(user.recruited_by > 0 ? user.recruited_by : '')
      } else {
        $("#chidonRecruitment").hide()
        $("#recruited_by_user_serial").val('')
      }

      var bookHtml = "<option value='0'>Please choose</option>"
      let maxBook = parseInt(user.regYears.chidon) - 3761
      for (var i = 2011; i <= maxBook; i++) {
        bookHtml += `<option value='${i}'>${i}</option>`
      }
      $("#step-2 form #bookVersion").empty()
      $("#step-2 form #bookVersion").append(bookHtml)

      // setup yahadus poll html
      let poll = {
        own: 'On my own',
        chavrusa: 'With a chavrusa (study partner)',
        parent: 'With a parent',
        grandparent: 'With a Bubby or Zaidy',
        online: 'On the online class',
        thechidon: 'Online at thechidon.com'
      }
      let html = ''
      for (let k in poll) {
        html += `<label>
                            <div style="float: left; padding-right: 15px;">
                                <input type="checkbox" class="yahadus-poll" value="${k}" />
                                <span class="checkbox"></span>
                            </div>
                            <div style="float: right;">
                                ${poll[k]}
                            </div>
                        </label><br />`
      }
      $("#yahadus-poll").empty().append(html)

      // update the address info
      if ((user.getChidonInfo && user.registrationStatus.chidonEdit) ||
        (user.parentAccount.admin_address1 && user.parentAccount.admin_address1 != '' &&
          user.parentAccount.admin_city && user.parentAccount.admin_city != '' &&
          user.parentAccount.admin_state && user.parentAccount.admin_state != '' &&
          user.parentAccount.admin_postal && user.parentAccount.admin_postal != '' &&
          user.parentAccount.admin_country && user.parentAccount.admin_country != '')) {
        if (user.parentAccount.admin_address1) $('#c-address').val(user.parentAccount.admin_address1);
        if (user.parentAccount.admin_address2) $('#c-apt').val(user.parentAccount.admin_address2);
        if (user.parentAccount.admin_city) $('#c-city').val(user.parentAccount.admin_city);
        if (user.parentAccount.admin_state) $('#c-state').val(user.parentAccount.admin_state);
        if (user.parentAccount.admin_postal) $('#c-zip').val(user.parentAccount.admin_postal);
        if (user.parentAccount.admin_country) $('#c-country').val(user.parentAccount.admin_country);
      }

      // reset all fields
      let resets = [
        {
          field: '#chidon-fee',
          type: 'select',
          cart: 'paid'
        },
        {
          field: '.limmud',
          type: 'radio',
          cart: 'track',
          db: 'test_type'
        },
        {
          field: '.recruit',
          type: 'radio',
          cart: 'recruited',
          triggerEvent: true,
          db: 'custom'
        },
        {
          field: '#recruited_by_user_serial',
          type: 'text',
          cart: 'recruitedBy',
          db: 'recruited_by'
        },
        {
          field: '#yarmulka-size',
          type: 'select',
          cart: 'yarmulka',
          db: 'yarmulka'
        },
        {
          field: '#chidon-sweater-size',
          type: 'select',
          cart: 'size',
          db: 'size'
        },
        {
          field: '#chidon-book',
          type: 'select',
          cart: 'book',
          db: 'book'
        },
        {
          field: '.book-bought',
          type: 'radio',
          cart: 'purchased',
          triggerEvent: true,
          db: 'custom'
        },
        {
          field: '.book-purchase',
          type: 'radio',
          cart: 'purchasedWhere',
          db: 'location'
        },
        {
          field: '#store-name',
          type: 'text',
          db: 'store_name'
        },
        {
          field: '#store-city',
          type: 'text',
          db: 'store_city'
        },
        {
          field: '#bookVersion',
          type: 'select',
          cart: 'bookVersion',
          db: 'version'
        },
        {
          field: '.yahadus',
          type: 'radio',
          triggerEvent: true
        },
        {
          field: '.yahadus-poll',
          type: 'multi',
          cart: 'poll',
          db: 'poll'
        },
        {
          field: '#comments',
          type: 'text',
          cart: 'comments',
          db: 'comments'
        },
        {
          field: '#agreements input',
          type: 'checkbox',
          cart: true,
          db: 'custom'
        },
        {
          field: '#khk_enrollment',
          type: 'checkbox',
          db: 'khk_reg'
        },
        {
          field: '#he_name',
          type: 'checkbox',
        },
        {
          field: '#media',
          type: 'checkbox',
        },
        {
          field: '#myshliach',
          type: 'checkbox'
        },
        {
          field: '#chidon-reg',
          type: 'select'
        }
      ]

      // reset all fields
      for (reset of resets) {
        switch (reset.type) {
          case 'checkbox':
          case 'radio':
          case 'multi':
            $(reset.field).each(function () {
              this.checked = false
            })
            break
          case 'text':
            $(reset.field).val('')
            break
          case 'select':
            let value = 0
            $(reset.field).val(value)
            break
        }
      }

      // if (user.registrationStatus.chidonEdit) {
        this.setChidonReg(user) // can only do it after the resets bc the chidon fee value is reset to 20
      // }

      if (user.school.school_id == anash_kinder) $("#anash_kinder_text").show();
      else $("#anash_kinder_text").hide();

      if ([61, 269].includes(user.school.school_id)) {
        $("#terms-1").text("I am aware that if my child qualifies for the Chidon Experience by getting a passing average " +
          "on the 3 tests there will be a fee of $50 - $360 depending on the track they pass plus a shipping fee (amount is dependant on your address).")
        $("#myshliachTerms").show()
      } else {
        $("#terms-1").text("I am aware that if my child qualifies for the Chidon Experience by getting a passing " +
          "average on the 3 tests there will be a fee of $50 - $360 depending on the track they pass.")
        $("#myshliachTerms").hide()
      }

      // find out if the cart already has info for this child
      let info = state.cart.filter(item => item.meta.user_id == user.user_id)
      if (info.length) {
        let yahadus = false
        // trigger clicks for the registrations already checked off
        for (item of info) {
          if (item.meta.registration_type === 'chayolei') {
            $("#chayolei").trigger('click')
            $("#he_name").trigger('click')
            $("#media").trigger('click')
          }
          if (item.meta.registration_type === 'chidon') {
            document.getElementById('chidon').checked = true
            for (elem of resets) {
              if (elem.cart) {
                switch (elem.type) {
                  case 'text':
                  case 'select':
                    $(elem.field).val(item.meta[elem.cart])
                    break
                  case 'checkbox':
                    $(elem.field).each(function () {
                      this.checked = true
                    })
                    break
                  case 'radio':
                    // for some inputs we need to trigger a click event
                    $(elem.field).each(function () {
                      if (this.value === item.meta[elem.cart]) {
                        if (elem.triggerEvent) $(this).trigger('click')
                        else this.checked = true
                      }
                    })
                    break
                  case 'multi':
                    $(elem.field).each(function () {
                      if (item.meta[elem.cart] && item.meta[elem.cart].includes(this.value)) this.checked = true
                    })
                    break
                }
              }
            }
          }
          if (item.meta.registration_type === 'yahadus') {
            yahadus = true
            $(".yahadus").each(function () {
              if (this.value === '1') this.checked = true
            })
          }
          if (item.meta.registration_type === 'khk') document.getElementById('khk_enrollment').checked = true
        }
        // check off no for yahadus book if yes is not selected
        if (!yahadus) {
          $(".yahadus").each(function () {
            if (this.value === '0') this.checked = true
          })
        }
      } else {
        // check if child already registered and we are pulling up their info to allow changes
        if (user.getChidonInfo) {
          // document.getElementById('chidon').checked = true
          let info = user.getChidonInfo
          for (elem of resets) {
            if (elem.db) {
              switch (elem.type) {
                case 'text':
                case 'select':
                  if (info[elem.db]) $(elem.field).val(info[elem.db])
                  break
                case 'checkbox':
                case 'radio':
                  switch (elem.field) {
                    // some fields have exceptions
                    case '#khk_enrollment':
                      if (parseInt(info.khk_reg)) {
                        document.getElementById('khk_enrollment').checked = true
                        $("#khk_enrollment").attr('disabled', true)
                      } else {
                        document.getElementById('khk_enrollment').checked = false
                        $("#khk_enrollment").attr('disabled', false)
                      }
                      break
                    case '.recruit':
                      if (user.registrationStatus.khk === false && user.registrationStatus.new_to_chidon == 1)
                        $("#chidonRecruitment").show()
                      $(elem.field).each(function () {
                        if (parseInt(info.recruited_by) && this.value == 1) $(this).trigger('click')
                        else if (!parseInt(info.recruited_by) && this.value == 0) this.checked = true
                      })
                      break
                    case '.book-bought':
                      // $(elem.field).each( function() {
                      //     if (this.value == 1 && info.location && info.location.length) $(this).trigger('click')
                      //     else if (this.value == 0 && (!info.location || (info.location && !info.location.length))) {
                      //         $(this).trigger('click')
                      //         $(".yahadus").each( function() {
                      //             if (this.value == 0) this.checked = true
                      //         })
                      //     }
                      // })
                      break
                    case '#agreements input':
                    case '#he_name':
                    case '#media':
                      $(elem.field).each(function () {
                        this.checked = true
                      })
                      break
                    default:
                      // for some inputs we need to trigger a click event
                      $(elem.field).each(function () {
                        if (info[elem.db] && this.value === info[elem.db]) {
                          if (elem.triggerEvent) $(this).trigger('click')
                          else this.checked = true
                        }
                      })
                      break
                  }
                  break
                case 'multi':
                  if (info[elem.db]) {
                    let values = info[elem.db].split(',') // coming from db it's a string
                    $(elem.field).each(function () {
                      if (values.includes(this.value)) this.checked = true
                    })
                  }
                  break
              }
            }
          }
        }
      }

      console.log(user);
    },
    toggleRates: function (user) {
      ['chayolei', 'chidon'].forEach(function (type) {
        $("#" + type).checked = false;
        if (user.registrationStatus[type] === false) {
          $("#" + type + "-registration").show()
          if (type === 'chayolei') {
            // setup chayolei fee dropdown
            let htmlFee = '';
            let rates = [100, 75, 65, 60];
            for (let n of rates) {
              if (n < user.registrationRates[type]) break;
              htmlFee += "<option value=" + n + ">$" + n + "</option>";
            }
            $('#chayolei-fee').empty().append(htmlFee);
            $('#chayolei-fee').val(user.registrationRates[type])
          }
        }
      })
    },
    renderCheckout: function (cart) {
      console.log(cart)
      let futurePayment = 0
      let total = cart.reduce(function (total, item) {
        return parseFloat(total) + parseFloat(item.price)
      }, 0);
      for (i = 0; i < cart.length; i++) {
        if (cart[i].meta.discount) total -= parseFloat(cart[i].meta.discount)
        if (cart[i].meta.type === 'advance registration') futurePayment += parseFloat(cart[i].price)
      }
      if (total < 0) total = 0;
      $("#charges").html('');
      // add each item
      cart.forEach(function (item) {
        // only show when there's a charge
        if (item.price > 0) {
          $("#charges").append('<div class="row">' +
            '<div class="col-10">' + item.description + '</div>' +
            '<div class="col-2 reg_cost">$' + item.price + '</div>'
            + "</div>");
          if (item.meta.discount) {
            $("#charges").append('<div class="row">' +
              '<div class="col-10">Discount</div>' +
              '<div class="col-2 reg_cost">-$' + item.meta.discount + '</div>'
              + "</div>");
          }
        }
      });
      // add future payment options
      if (futurePayment) {
        $("#charges").append('<div class="row total-row">' +
          '<div class="col-9 col-md-10"><strong>Due Today</strong></div>' +
          '<div class="col-3 col-md-2 reg_cost">$' + (total - futurePayment) + '</div>'
          + "</div>");
        $("#charges").append('<div class="row total-row">' +
          '<div class="col-9 col-md-10"><strong>Eligible for installments</strong></div>' +
          '<div class="col-3 col-md-2 reg_cost">$' + futurePayment + '</div>'
          + "</div>");
        // update amounts to be charged in installments
        $("#earlyRegTotal").text(futurePayment.toFixed(2))
        // update installments options
        const monthsLeft = monthsBetween(new Date(), new Date("2027-02-04"));
        const installmentsInfo = {
          1: 'One',
          2: 'Two',
          3: 'Three',
          4: 'Four',
          5: 'Five',
          6: 'Six',
          7: 'Seven',
          8: 'Eight',
          9: 'Nine',
          10: 'Ten', 
          11: 'Eleven',
          12: 'Twelve'
        }
        for (let i of Object.keys(installmentsInfo)) {
          if (parseInt(i) > monthsLeft) continue;
          let elem = '#earlyReg' + installmentsInfo[i]
          $(elem).text((futurePayment / parseInt(i)).toFixed(2))
        }
      } else {
        // just show total
        $("#charges").append('<div class="row total-row">' +
          '<div class="col-9 col-md-10"><strong>Total</strong></div>' +
          '<div class="col-3 col-md-2 reg_cost">$' + total + '</div>'
          + "</div>");
      }
      // add the total row
      // var text = "Total Balance";
      // if (Cookies.get('lang') == 'he' || localStorage.getItem('locallang') == 'he') text = "איזון כולל";
      // $("#charges").append('<div class="row total-row">' +
      //   '<div class="col-9 col-md-10"><strong>' + text + '</strong></div>' +
      //   '<div class="col-3 col-md-2 reg_cost">$' + total + '</div>'
      //   + "</div>");
      $("#total").val(total);
    },
    toggleNewCard: function (required) {
      if (required) $("#new-card").show();
      else $("#new-card").hide();

      $.each($("#new-card input"), function (index, input) {
        input.required = required;
      });
    },
    renderPaymentOptions: function (payment_profiles) {
      var html = '';
      payment_profiles.forEach(function (payment, index) {
        var cc = payment.payment.creditCard;
        console.log(cc)
        cc.cardType = cc.cardType || "Unknown";
        const expiry = cc.expirationDate.split('-');
        const expiryDate = expiry[1] + '/' + expiry[0].substring(2);
        var msg = '<span>' + ' ending in ' + cc.cardNumber.slice(4) + '</span>';
        if (Cookies.get('lang') == 'he' || localStorage.getItem('locallang') == 'he') msg = '<span dir="rtl">כרטיס ' + ' המסתיים בספרות ' + cc.cardNumber.slice(4) + '</span>'
        html +=
        `
          <div class="payment-option cc-number identified ${cc.cardType.toLowerCase()}">
            <label class="radio-label">
              <input type="radio" id="payment_profile" name="payment_profile" value="${payment.customerPaymentProfileId}" ${index === 0 ? "checked" : ""}/>
              <span class="radio"></span>
            </label>
            <span style="vertical-align: super; margin-left: 60px;">${msg}</span>
            <span style="vertical-align: super"> 
              <a href="javascript:void(0)" id="expiry-date-link" 
                onclick="document.getElementById('expiry-date').value='${cc.expirationDate}';
                document.getElementById('edit-expiry-modal').style.display='block';">
              (${expiryDate})</a></span>
            <span style="font-size: smaller; float: right;"><a style="vertical-align: sub;" href="javascript:void(0)" onclick="templates.removePaymentProfile(${payment.customerPaymentProfileId}); return false;"><i class="fas fa-trash"></i></a></span>
          </div>
          <div class="modal" id="edit-expiry-modal" style="position: relative; left: 50%; transform: translateX(-50%); margin-bottom: 10px;">
            <div class="modal-content">
              <input type="month" id="expiry-date" value="${cc.expirationDate}" data-card_exp="${cc.expirationDate}" data-card_num="${cc.cardNumber}" 
                onchange="templates.updatePaymentProfile(${payment.customerPaymentProfileId}, this);
                document.getElementById('edit-expiry-modal').style.display='none';">
            </div>
          </div>
          `
      });
      var msg2 = '<span style="vertical-align: super;">New Card</span>';
      if (Cookies.get('lang') == 'he' || localStorage.getItem('locallang') == 'he') msg2 = "<span>כרטיס (אשראי) חדש</span>"
      html +=
        '<div class="payment-option">' +
        '<label class="radio-label">' +
        '<input type="radio" id="payment_profile" name="payment_profile" value=""/>' +
        '<span class="radio"></span>' +
        '</label>&nbsp;' + msg2 +
        '</div>';

      $("#card-on-file").html(html);
      $("input#payment_profile").change(function (event) {
        templates.toggleNewCard(!event.target.value);
      });
    },
    removePaymentProfile: function (id, elem) {
      const del = confirm('Are you sure you want to delete this card?')  
      if (!del) return
      $.post('/mobile/reg/ajax/card_management.php', {
        action: 'delete_card',
        profile_id: id
      }, function (response) {
        const res = JSON.parse(response)
        if (res.success) {
          // find which number card this was
          const index = $(".payment-option").index(elem)
          // remove this card from list of cards
          $(".payment-option").eq(index).remove()
          // find next card and select it
          $(".payment-option").eq(index + 1).find('input').trigger('click')
          // toggle new card if no more cards
          if ($(".payment-option").length === 1) {
            templates.toggleNewCard(true)
          }
        } else {
          alert('Failed to delete card.')
        }
      })
    },
    updatePaymentProfile: function (id, input) {
      // build card info
      const cardInfo = {}
      cardInfo.expiryDate = input.value
      cardInfo.cardNumber = input.getAttribute('data-card_num')

      // update card
      $.post('/mobile/reg/ajax/card_management.php', {
        action: 'update_card',
        profile_id: id, 
        cardInfo: cardInfo
      }, function (response) {
        const res = JSON.parse(response)
        if (! res.success) {
          alert('Failed to update card: ' + res.error)
        } else {
          alert('Card updated successfully')
          templates.updateExpiryDateLink(cardInfo.expiryDate)
        }
      })
    },
    updateExpiryDateLink: function(expiryDate) {
      const expiry = expiryDate.split('-');
      const newExpiryDate = expiry[1] + '/' + expiry[0].substring(2);
      document.getElementById('expiry-date-link').textContent = `(${newExpiryDate})`;
    }
  }
}();