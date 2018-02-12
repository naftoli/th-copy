/* updateMedalRanks object.
    
    Handles updating the medal and rank status of a user and showing the correct modals....

*/
var debug;
var updateMedalRanks = function(){
    // define some internal variables we may need
    var modal = $("#rankMedalModal");
    callback_run = false;
    var onCloseCallback; // create a blank callback for the function
    var rank_names = ["",   "Private",    "Sergeant", "Sergeant Major",   "Second Lieutenant",
        "First Lieutenant", "Captain",  "Major",    "Colonel",  "General",
        "1* General",   "2* General", "3* General",   "4* General",   "5* General"
    ];
    
    modal.on('hidden.bs.modal', function(event) {
        if(onCloseCallback) { // make sure that onCloseCallBack is showing...
            onCloseCallback = onCloseCallback(event);
        }
    });
    
    // exposed function update....
    function update(user_id) {
        $.post('../ajax/updateMedalsRanks.php', { user : user_id }, function(data){
            data = JSON.parse(data);
            if (data.medal) {
                showMedalModal(data.medal, user_id);
            }
            if (data.medal && data.rank) { // if there is a medal and a rank...
                callback_run = false;
                onCloseCallback = function(){
                    callback_run = true;
                    showRank(data.rank);
                };
                var rank = data.rank;
                setTimeout(function(){
                    if(!callback_run){
                        callback_run = true;
                        onCloseCallback = false; // clear the callback so it will not run
                        $(".rank-medal-modal .cong-box .rbn img").addClass("rbn-close");
                        setTimeout(function(){
                            showRank(rank);
                        }, 1500);
                    }
                }, 7700);
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
        modal.modal('show');
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