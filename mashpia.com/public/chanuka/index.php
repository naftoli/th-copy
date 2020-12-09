<?php
require("./functions/config.php");
require("./templates/header.php");

?>
<div id="home-page-container">
    <img id="home-page-chanuka-logo" src="./images/Asset 1.png" alt="logo"><br><br>

    <h3 id="first-header" class="text-yellow w-100">Have you completed your Chanukah missions?</h3>

    <p class="mb-4 home-page-p">

    Fill out any of the Chanukah tasks that you have done this year and be entered into a special raffle! <br>

    Each mission can be completed once throughout Chanukah. <br>
    </p>

    <h4 class="text-orange">Win a prize just for entering!</h4>

    <!-- <hr class="w-50" style="border: 1px solid #f47a20"><br> -->

    
</div><br>

<!-- <h3 class="missions-header text-orange">Chanukah Missions</h3><br><br> -->
    <div class="row px-5">
        <?php
        foreach ($missions_arr as $key => $value) :
        ?>

        <div class="col-lg-6 p-4 mission-box-container">
            <div data-mission-num="<?= $key+1; ?>" class="mission-box border text-center d-flex justify-content-between">
                
                <div class="mission-imgs-con d-flex p-3">
                    <img class="mission-img" src="./images/<?= $value['mission-img']?>" alt="mission image">
                    <h3 class="mission-title"><?= $value['name']; ?></h3>
                </div>
                <div class="prize-img-container"><img class="prize-img " src="./images/<?= $value['prize-img']?>" alt="prize image"></div>

                
                
            </div><?php if (isset($value['notes'])) : ?>
                    <div style="display: none" class="notes-div"><b>Note: </b><?= $value['notes']; ?></div>
                <?php endif; ?>
        </div>

        <?php endforeach; ?>
    </div><br><br>
        <button id="mission-submit-btn-first" data-toggle="modal" disabled data-target="#exampleModalCenter" class="submit-btn btn w-25 mt-4">Submit!</button>


<?php
require("./templates/footer.php");

?>