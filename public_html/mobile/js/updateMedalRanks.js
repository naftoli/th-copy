/* updateMedalRanks object.
    
    Handles updating the medal and rank status of a user and showing the correct modals....

*/
var debug;
// allow first and last names to be defined...
var first_name;
var last_name;

var updateMedalRanks = function(){
    // define some internal variables we may need
    var modal = $("#rankMedalModal");
    var callback_run = false;
    var timeout = false;
    
    var show_social = first_name && last_name; // determine if we should show the social sharing buttons...
    
    var onCloseCallback; // create a blank callback for the function
    var rank_names = ["",   "Private",    "Sergeant", "Sergeant Major",   "Second Lieutenant",
        "First Lieutenant", "Captain",  "Major",    "Colonel",  "General",
        "1* General",   "2* General", "3* General",   "4* General",   "5* General"
    ];
    
    // callback when the modal is closed...
    modal.on('hidden.bs.modal', function(event) {
        if(onCloseCallback) { // make sure that onCloseCallBack is set...
            onCloseCallback = onCloseCallback(event); // then set it to the result of itself...
        }
    });
    
    // callback to clear the timeout...
    // this essentially just cleansup after the modal is closed...
    function clearTimeoutCallback() { // onCloseCallback handler to clear the latest internal timeout.
        if (timeout) {
            clearTimeout(timeout);
            timeout = false;
        }
    }
    
    // exposed function update....
    function update(user_id) {
        $.post('../ajax/updateMedalsRanks.php', { user : user_id }, function(data){
            data = JSON.parse(data);
            
            if (data.medal) {
                showMedalModal(data.medal, user_id);
            }
            
            if (data.medal && data.rank) { // if there is a medal and a rank...
                callback_run = false; // we have not run the callback yer...
                
                onCloseCallback = function(){
                    callback_run = true; // note that we have run the call back...
                    showRank(data.rank); // show the rank...
                    return clearTimeoutCallback; // return the function that clears the timeout if closed...
                };
                
                var rank = data.rank; // take note of the rank for inside the timeout (prevent garbage collection...)
                
                timeout = setTimeout(function(){ // set a timeout to run after the medal has pulsed 3 times...
                    if ( !callback_run ) { // make sure the user has not closed the modal
                        callback_run = true; // then note that we do not need to know this anymore...
                        onCloseCallback = clearTimeoutCallback; // clear the timeout when the page closes...
                        $(".rank-medal-modal .cong-box .rbn img").addClass("rbn-close"); // show the closing animation...
                        
                        timeout = setTimeout(function(){ // set a timeout equal to the duration of said animation to show the rank when done...
                            showRank(rank);
                        }, 1500); // 1x animation speed in css file.
                    }
                }, 7700); // 3x animation speed...
            }
        });
    }
    
    function showMedalModal(medal_info, user_id) {
        hideRank(); // get rid of the rank if it was open...
        // get the current info from the user (all the juicy details...)
        $.post("//mashpia.com/mobile/reg/medals/ajax/getMedalInfo.php", {user_id: user_id}, function(data){
            data = JSON.parse(data);
            var details = false;
            for (var item = 0; item < data.length; item++) {
                if (data[item].id == medal_info.subject_id) {
                    details = data[item]; break;
                }
            }
            
            // show the modal...
            modal.find("#award-type").text("Medal");
            modal.find(".rbn").css({"display": "block"}); // show the medals...
            modal.find(".rbn").html('<img src="https://mashpia.com/file_view.php?id='+details.photo+'">'); // add the image...
            modal.find("#details").html(" You have earned a <strong>" + details.medal + " " + details.name + "</strong> Medal! " );
            
            updateSocialMedia("Mazel Tov! "+first_name + " " + last_name + "has heared a "+ details.medal + " " + details.name + " medal from Tzivos Hashem!" );
            
            modal.modal('show'); // show the modal
        });
    }
    
    function showRank(rank_info) {
        hideMedal();
        
        // update the info and show the modal..
        modal.find("#award-type").text("Rank");
        modal.find(".rank").css({"display": "block"});
        modal.find(".rank").html('<img src="//mashpia.com/mobile/reg/medals/images/trophits/'+rank_info.rank_ord+'.png" alt="">');
        modal.find("#details").html("You have been promoted to the rank of <strong>" + rank_names[rank_info.rank_ord] + "</strong>");
        
        updateSocialMedia("Mazel Tov! "+ first_name + " " + last_name + " has been promoted to the rank of "+ rank_names[rank_info.rank_ord] + " in Tzivos Hashem!" );
        
        modal.modal('show');
    }
    
    function updateSocialMedia(message) {
        if (show_social) {
            modal.find("#share").show(); // show the social buttons....
        }
        // if we are on mobile...
        if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
            modal.find("#share .fa-whatsapp").parent().attr("href", "https://api.whatsapp.com/send?text=" + message); // use the whatsapp mobile link...
        } else {
            modal.find("#share .fa-whatsapp").parent().attr("href", "https://web.whatsapp.com/send?text=" + message); // use the whatsapp browser link...
        }
        modal.find("#share .fa-envelope").parent().attr("href", "mailto:?subject=Tzivos Hashem Nachas!&body=" + message);
        modal.find("#share .fa-twitter").parent().attr("href", "https://twitter.com/intent/tweet?text=" + message);
    }
    
    // hide the medal details...
    function hideMedal() {
        modal.find(".rbn").css({"display": "none"}); // show the medals...
    }
    
    // hide the rank details..
    function hideRank() {
        modal.find(".rank").css({"display": "none"}); // show the medals...
    }
    
    return {
        update:         update,
        showMedalModal: showMedalModal,
        showRank:       showRank,
        setCallback:    function(callback){
            onCloseCallback = callback;
        }
    };
}();