<?php
require("./functions/config.php");
require("./templates/header.php");

?>
<div id="home-page-container">
    <img id="home-page-chanuka-logo" src="./images/Asset 1.png" alt="logo"><br><br>

    <h2 class="welcome text-yellow">Welcome <?php if (isset($_SESSION['user_name'])) { echo ucwords($_SESSION['user_name']); } else {echo " to the Chanukah Challenge";} ?></h2><br><br>
    
    <p class="mb-4 home-page-p">Win a prize just for entering! <br><br>

    Have you completed your Chanukah missions? <br><br>

    Fill out any of the Chanukah tasks that you have done this year and be entered into a special raffle! <br><br>

    Each mission can be completed once throughout Chanukah. <br><br>
    </p>

    <hr class="w-50" style="border: 1px solid #f47a20"><br>

    <div class="text-yellow">Celebrating 40 years of Tzivos Hashem - World's largest Jewish children's organization.</div><br><br>


    <a class="" href="https://www.tzivoshashem.org"><img id="home-page-logo" src="https://www.tzivoshashem.org/wp-content/uploads/2017/02/Main-Logo.png" alt="logo image"></a>

</div><br><br><br>

<h3 class="missions-header text-orange">Chanukah Missions</h3><br><br>
    <div class="row">
        <?php
        foreach ($missions_arr as $key => $value) :
        ?>

        <div class="col-lg-4 p-3 mission-box-container">
            <div data-mission-num="<?= $key+1; ?>" class="mission-box border p-2 text-center">
                <h2 class="mission-title"><?= $value['name']; ?></h2>
                <div class="mission-imgs-con d-flex justify-content-between">
                    <img class="mission-img" src="./images/<?= $value['mission-img']?>" alt="mission image">
                    <div>Win a <?= $value['prize']; ?>!</div>
                    <img class="mission-img " src="./images/<?= $value['prize-img']?>" alt="prize image">
                </div>
                
                <div><b>(<?= $value['amount']; ?> raffles)</b></div>

                <?php if (isset($value['notes'])) : ?>
                    <div style="display: none" class="notes-div"><b>Note: </b><?= $value['notes']; ?></div>
                <?php endif; ?>
                <br><br>
                <br>
            </div>
        </div>

        <?php endforeach; ?>
    </div>
        <button id="mission-submit-btn-first" data-toggle="modal" disabled data-target="#exampleModalCenter" class="submit-btn btn w-25 mt-4">Submit!</button>



<?php
require("./templates/footer.php");

?>