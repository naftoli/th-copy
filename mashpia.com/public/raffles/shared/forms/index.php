<?php
// enable debuging
if ( isset( $_GET['debug'] ) ) {
    //error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once($_SERVER["DOCUMENT_ROOT"].'/header.php');
// only superusers can use this page. Non superusers get redirected to the page that they can use
//if ($admin_user['auth'] != 'super') {
//    header("Location: /raffles/shared/forms/eligible_form.php");
//}
$links = [
    269	=> 'https://docs.google.com/spreadsheets/d/1jwoKxYDCe3IyxbVOfXz68AJytifmWaq51se2p4n10lY/edit#gid=0',
    621	=> 'https://docs.google.com/spreadsheets/d/1EmtGrkSR5RT7MbilT23TNd-tyCi_4OyUtM8EtYxJp64/edit#gid=0',
    176	=> 'https://docs.google.com/spreadsheets/d/1sblS-aeIp20cpTsh-Dgx28VUGZ4MjKTCA_ABOhNhxmg/edit#gid=0',
    162	=> 'https://docs.google.com/spreadsheets/d/1RoYLiphVBiXvGNqoPt27u46biJ08iqIEmAENXVkb5EE/edit#gid=0',
    45	=> 'https://docs.google.com/spreadsheets/d/1t8XReOfgBhN-mVUZ01u_z0jaxlF-rN0X957HKa0YwwA/edit#gid=0',
    54	=> 'https://docs.google.com/spreadsheets/d/1pjxD22W2KfKDkFw_f8UJU-pgCOMBdOHAvSF1SWaeqok/edit#gid=0',
    30	=> 'https://docs.google.com/spreadsheets/d/1mB2fEHX-8GIU73Ec33JGshqRDdhGdylXrd5RGFIWBZ8/edit#gid=0',
    2	  => 'https://docs.google.com/spreadsheets/d/12g-o-uaaJT_yRKAkbmDw0aZN98wilWrKfM9pyehVawM/edit#gid=0',
    690	=> 'https://docs.google.com/spreadsheets/d/1GDhq6rSLLbqMAn92ZI2VShLlBaFjIt8GczAfQQaKYeI/edit#gid=0',
    7	  => 'https://docs.google.com/spreadsheets/d/1H03K-Q51aoiIWSSW4MskzIDB2-mfKBPqJ0THkfgroQc/edit#gid=0',
    112	=> 'https://docs.google.com/spreadsheets/d/1gLWU7PbbaWjtuFx7FlCDrcWIPJXe9_6W4J8WWYvi_Eg/edit#gid=0',
    66	=> 'https://docs.google.com/spreadsheets/d/13QK485lOf8IQSoYnJu4FbO6LLWEteV3OQzpeW2Kztx8/edit#gid=0',
    63	=> 'https://docs.google.com/spreadsheets/d/1y8EApzmE3iqOC-mPb2-N0KYLxbqTkgEZGqALi1Jh6C8/edit#gid=0',
    693	=> 'https://docs.google.com/spreadsheets/d/1ovM-0hZD4SGX88l_aIYvnFbKRf6JhtRUPXb_7P0lcdM/edit#gid=0',
    81	=> 'https://docs.google.com/spreadsheets/d/1AnAKjGOm3U2aMnPOIw01hxcZCQzVPi1XUSon8fmqwCU/edit#gid=0',
    615	=> 'https://docs.google.com/spreadsheets/d/14xsfwKqI4eh6n4OtoPuey6pO8alFmkR-Rx4u4NXDsLw/edit#gid=0',
    613	=> 'https://docs.google.com/spreadsheets/d/1VtoCVq5GrsBSjeIE-kwbZ1m0PMCa4s6p-XoBwS96xmA/edit#gid=0',
    49	=> 'https://docs.google.com/spreadsheets/d/1NTsxmbyS9bjJu9akQzWP_pmRcrIz-9IZBZtFj4xDLK4/edit#gid=0',
    105	=> 'https://docs.google.com/spreadsheets/d/1CU6j9PF9C3aiuNhevPSBJiJOI_LtxciR_CNCT1uVHrY/edit#gid=0',
    192	=> 'https://docs.google.com/spreadsheets/d/1EhYxDjmyWL_u9PykQ5n1w7Xx2XwfsBOEgnhTQ2puN-8/edit#gid=0',
    89	=> 'https://docs.google.com/spreadsheets/d/12er7e2ENV4iVESu3tLYMCc-8UlUguZW69VRiNau9yz8/edit#gid=0',
    55	=> 'https://docs.google.com/spreadsheets/d/13Z5daJUTgI_LeMn8PnSB33NF2sQtkLEfdhIArU8fDfU/edit#gid=0',
    106	=> 'https://docs.google.com/spreadsheets/d/1l-NZqkOKSPfhLfjUf5k9a2SZgcx71Bgd69hmqhYweMo/edit#gid=0',
    470	=> 'https://docs.google.com/spreadsheets/d/13FdEWFEdlP9qY4_3kDCQgDUbMRl_tP1Ij__WKgbc7i0/edit#gid=0',
    5	  => 'https://docs.google.com/spreadsheets/d/1JIjhLdEXw1Exv-xQts_qWzHUxeGZopGXy8auZtUjxZE/edit#gid=0',
    50	=> 'https://docs.google.com/spreadsheets/d/17OeZPJvP1M7HMuX4t09bZoDLeCxrxCETiLRtsOxC3Sg/edit#gid=0',
    692	=> 'https://docs.google.com/spreadsheets/d/1ggcWqPdk8RVtfdR5S1IrYWKZAmlGud2kBCZVMhhQjY4/edit#gid=0',
    21	=> 'https://docs.google.com/spreadsheets/d/1frGBts9DIo5YLW_1YLAPem5FmV-n4m121fQa0mNEV_U/edit#gid=0',
    37	=> 'https://docs.google.com/spreadsheets/d/1GTvwVtPiYz55vq6n9rOiRqneTNxC579tqeMa1Nnntpk/edit#gid=0',
    86	=> 'https://docs.google.com/spreadsheets/d/1051asZCof0w6kyQr2hGz0acM9J6rNkcrFNX27bk6aq8/edit#gid=0',
    4	  => 'https://docs.google.com/spreadsheets/d/1E96Y0BMn_gu5cQUZCqLPlr3c00uqIARLdfjAw6dEY1Q/edit#gid=0',
    60	=> 'https://docs.google.com/spreadsheets/d/1JRKAlWJZCk8OW0mG6FLdgaxnbTMvr0x_4od_3jf3Qyc/edit#gid=0',
    430	=> 'https://docs.google.com/spreadsheets/d/1Y86pMSQuF2zkjtSppdvw9LHVtUghfdkZnFVMu5kKhgc/edit#gid=0',
    33	=> 'https://docs.google.com/spreadsheets/d/1zYyqqIakcYTZ7Xlgpl8uyws1xh94eQEVvechhlFE1L4/edit#gid=0',
    694	=> 'https://docs.google.com/spreadsheets/d/1wx8nVv0t-Kgy0-nnA_4BWnAklXQuemd9YFllMvMpnVo/edit#gid=0',
    739	=> 'https://docs.google.com/spreadsheets/d/1L6OJUe4LtRAxYyZH6QNg8edlqA805bi9cMWti15q6Wc/edit#gid=0',
    780	=> 'https://docs.google.com/spreadsheets/d/1aNFQY31mOMDJylzSBnZjEjUqLUFypQ5qrOp_Bv_Dcag/edit#gid=0',
    80	=> 'https://docs.google.com/spreadsheets/d/10DnIKML2Lrhei-se1GWcE2sHVS1h5bwswa4dqIbmifQ/edit#gid=0',
    110	=> 'https://docs.google.com/spreadsheets/d/1fFdWZUTip5-74QGQoUt-MAS6tqiOdp8X_27wOvk0o5M/edit#gid=0',
    472	=> 'https://docs.google.com/spreadsheets/d/18CrC-wFK6br5FWhTnlHEebrxYEe0AwAcrVK2aSLwxks/edit#gid=0',
    659	=> 'https://docs.google.com/spreadsheets/d/1czJVry4LNcUcJKtbedw1QR53AlF4GW4LRkO3BvYGxLk/edit#gid=0',
    434	=> 'https://docs.google.com/spreadsheets/d/1VRQ012ZJN3ZbdbwAhDjIlKT9ODdcFwk0FWS8A_E6GoA/edit#gid=0',
    480	=> 'https://docs.google.com/spreadsheets/d/1uQltGeK0mJ5Y0PYe7vUil7U0enzfbVH0RujbFn-cTI8/edit#gid=0',
    517	=> 'https://docs.google.com/spreadsheets/d/1pAYx3NkOY1-_xVP2SGf433jtTueM1abmqcYTREPhrn0/edit#gid=0',
    3	  => 'https://docs.google.com/spreadsheets/d/1_AKVddV4cty0HTlN68tOhTJj2GgBrO7eEHZ9zJt03fU/edit#gid=0',
    39	=> 'https://docs.google.com/spreadsheets/d/1Mis8hgeO-pc4IIi5NoN6ZCtOb1dhjolAHONBJeG1whU/edit#gid=0',
    19	=> 'https://docs.google.com/spreadsheets/d/1WLnRfjYlSQEhRcqSWWDC159EyCAvUpCx2PdQzuXvHh4/edit#gid=0',
    42	=> 'https://docs.google.com/spreadsheets/d/1OXWBMrpfuLXKZL0zhZeDMq8pS69qRRTHSRClHr0q9qM/edit#gid=0',
    265	=> 'https://docs.google.com/spreadsheets/d/1-WBlML7nhzYyaSnzu3jsWbYMBWO5Udd8T1uHWKjdIFA/edit#gid=0',
    726	=> 'https://docs.google.com/spreadsheets/d/1z3rK4CS0_H9r68eEy6adkNyoF2qlypZgKto_F2k3eI8/edit#gid=0',
    185	=> 'https://docs.google.com/spreadsheets/d/159V727ZU4CksMM6fTnwdysq0a1ir8s4-Y9_TBwH2FEQ/edit',
    614	=> 'https://docs.google.com/spreadsheets/d/1aEr-ZA8yazJ8qJwYAU1_0mqLzfk9YvsfJPFujKKqsUI/edit#gid=0',
    263	=> 'https://docs.google.com/spreadsheets/d/1d1MYhY-W3c8DG6qUceXZzTa1aJCjOO2NsQDA8F_81jc/edit#gid=0',
    61	=> 'https://docs.google.com/spreadsheets/d/12AEeUMKmo6K0ft-yM6lXbRt9Z9pNIiDm0jCVWBYwotY/edit#gid=0',
    255	=> 'https://docs.google.com/spreadsheets/d/11_1FDpMkr_AeJbiPHHBxAJ-C2UGmwFCiqaF8UrkaGaI/edit#gid=0',
    542	=> 'https://docs.google.com/spreadsheets/d/1fI2nx549yr6rseouPn8urwn8LX7amMgEbNrz0LwRA2E/edit#gid=0',
    48	=> 'https://docs.google.com/spreadsheets/d/1Xy7kYZh9DQ4bG3ueVDuiN6aN9giFhZGgXh_L8wZszKc/edit#gid=0',
    577	=> 'https://docs.google.com/spreadsheets/d/1Bq9Pf5CKMyVzs_sDJqrS8nHFGwxwb1EJvlFSAL0OL_Y/edit#gid=0',
    727	=> 'https://docs.google.com/spreadsheets/d/1sux20xJ6caGO1vN3VbqT0PYOTvgY4usolO-wAF2dyv0/edit#gid=0',
    471	=> 'https://docs.google.com/spreadsheets/d/1Vyfr6lFytT8wUJAKKgDTmd7KpE4H2yc2qsVC4coemAA/edit#gid=0',
    84	=> 'https://docs.google.com/spreadsheets/d/1QSKwYC80bDOPsZuxbctkcJ1C81xGY4Q0POGOQlkVJyo/edit#gid=0',
    663	=> 'https://docs.google.com/spreadsheets/d/1qrklAmHJH9E-oGKFwKKHwG4AUgcUHLICE3gcLRxorLg/edit#gid=0',
    427	=> 'https://docs.google.com/spreadsheets/d/1CdmZ5H26XAMdGyZcbP4j2NvD74hGQQzc8IY6Th8Pejw/edit#gid=0',
    9	  => 'https://docs.google.com/spreadsheets/d/1a2ayKsNkqQo3gcsKE3vimcHrfqgIXiaJntPKSWotJAs/edit#gid=0',
    11	=> 'https://docs.google.com/spreadsheets/d/1JBoIDq1cdX_j2xyp3OzTrRus1VOpzxoT1u8xMRLEMys/edit#gid=0',
    40	=> 'https://docs.google.com/spreadsheets/d/1q9RaRwzcFDOgR8j8iz5sX9hEX8PSr9o88mcVgfLar58/edit#gid=0',
    58	=> 'https://docs.google.com/spreadsheets/d/1FlWELsUVbv1AFhmkYq-XiKN0eFrExmJNYlhW7YNWJ9c/edit#gid=0'
];

$school_ids = $admin_user['auths']['school'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Raffles Home Page</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/raffles/shared/styles/action-links.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <? // load the admin UI and JQuery 1.4
            include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php');
        ?>
        <h1>Tzivos Hashem Raffle System</h1>
        
        <h2>Documents</h2>
        <p style="text-align: center">
            Please click <a href="https://docs.google.com/document/d/1FKrfoxTUTORwW9lO3Kx0mUY9wVeCBFCn0noQzVoj4q8/edit?usp=sharing">here</a> for the Rewards Manual
        </p>

        <h2>All Raffles</h2>
        <div id="action-links">
            <a href="eligible_form.php">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="tickets"/>
                    <span class="link-text">Eligible Students</span>
                </div>
            </a>
            <a href="winners_form.php">
                <div class="button">
                    <img src="/images/icon_admin_medal.png" height="32" alt="medal"/>
                    <span class="link-text">Raffle Winners</span>
                </div>
            </a>
            <a href="/auction/winners">
              <div class="button">
                <img src="/images/icon_admin_medal.png" height="32" alt="tickets"/>
                <span class="link-text">Auction Winners</span>
              </div>
            </a>
        </div>
        
        <h2>Weekly Raffles</h2>
        <div id="action-links">
            <!-- <a href="eligible_form.php">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="tickets"/>
                    <span class="link-text">Eligible Students</span>
                </div>
            </a>
            <a href="winners_form.php">
                <div class="button">
                    <img src="/images/icon_admin_medal.png" height="32" alt="medal"/>
                    <span class="link-text">Winners</span>
                </div>
            </a> -->
            <a href="/raffles/posters/weekly.php">
                <div class="button">
                    <img src="/images/icon_admin_medal.png" height="32" alt="tickets"/>
                    <span class="link-text">Weekly Raffle Winners Posters</span>
                </div>
            </a>
        </div>
        <!-- <h2>Grand Raffles</h2>
        <div id="action-links">
            <a href="eligible_form.php">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="tickets"/>
                    <span class="link-text">Eligible Students</span>
                </div>
            </a>
            <a href="winners_form.php">
                <div class="button">
                    <img src="/images/icon_admin_medal.png" height="32" alt="medal"/>
                    <span class="link-text">Winners</span>
                </div>
            </a>
        </div>
        <h2>Raffle 180</h2>
        <div id="action-links">            
            <?//if ($admin_user['auth'] == 'super') {?>
                <!-- <a href="../../yearly/eligibility_report_hq.php">
                    <div class="button">
                        <img src="/images/icon_auction.png" height="32" alt="tickets"/>
                        <span class="link-text">Eligible Students</span>
                    </div>
                </a> -->
                <!--
            <?// } else { ?>
                <a href="eligible_form.php">
                <div class="button">
                    <img src="/images/icon_profile.png" height="32" alt="tickets"/>
                    <span class="link-text">Eligible Students</span>
                </div>
            </a>
            <?// } ?>
            <a href="winners_form.php">
                <div class="button">
                    <img src="/images/icon_admin_medal.png" height="32" alt="medal"/>
                    <span class="link-text">Winners</span>
                </div>
            </a>
        </div> -->

            <!-- <a href="../../yearly/printout/">
                <div class="button">
                    <img src="/images/icon_auction.png" height="32" alt="tickets"/>
                    <span class="link-text">Personalized Raffle Posters w/ Eligible List</span>
                </div>
            </a> -->
            <!-- <a href="../../yearly/printout/">
                <div class="button">
                    <img src="/images/parentIcons/Printer.gif" height="32" alt="tickets"/>
                    <span class="link-text">Print Posters</span>
                </div>
            </a> -->

        <?if ($admin_user['auth'] == 'super') {?>
            <h2>Administration Forms</h2>
            <div id="action-links">
                <a href="../../weekly/forms/prize_form.php">
                    <div class="button">
                        <img src="/images/icon_admin_medal.png" height="32" alt="medal"/>
                        <span class="link-text">Weekly Prizes</span>
                    </div>
                </a>
                <a href="/admin_prize_auction.php">
                    <div class="button">
                        <img src="/images/icon_admin_medal.png" height="32" alt="medal"/>
                        <span class="link-text">Grand Prizes</span>
                    </div>
                </a>
                <br/>
                <a href="raffle_form.php">
                    <div class="button">
                        <img src="/images/icon_auction.png" height="32" alt="tickets"/>
                        <span class="link-text">Raffles</span>
                    </div>
                </a>
            </div>
            <h2>Administration Reports</h2>
            <div id="action-links">
                <a href="winners_hachayol_form.php">
                    <div class="button">
                        <img src="/images/icon_admin_medal.png" height="32" alt="medal"/>
                        <span class="link-text">Hachayol Winners</span>
                    </div>
                </a>
                <a href="winners_shamai_form.php">
                    <div class="button">
                        <img src="/images/icon_admin_medal.png" height="32" alt="medal"/>
                        <span class="link-text">Video Winners</span>
                    </div>
                </a>
            </div>
        <?} // end admin only links ?>
        </div>

        <h2>Prizes</h2>
        <?php foreach ($school_ids as $id) : ?>
        <div id="action-links">
          <a href="<?= $links[$id] ?>">
            <div class="button">
              <img src="/images/icon_admin_medal.png" height="32" alt="tickets"/>
              <span class="link-text">Prizes Fulfillment Report</span>
            </div>
          </a>
        </div>
        <?php endforeach; ?>

        <div style="margin-bottom: 30px;"> </div>
    </body>
</html>