<!--        <div class="footer">
            <nav class="navbar navbar-default navbar-fixed-bottom" role="navigation">
                <div class="btn-group btn-group-justified">
                    <a href="index.php" class="btn btn-profile"><i class="icon"></i>Home</a>
                    <a href="missions.php" class="btn btn-missions"><i class="icon"></i>Missions</a>
                    <a href="goals.php" class="btn btn-goals"><i class="icon"></i>Goals</a>
                </div>
            </nav>
        </div>-->
    <div style="position: fixed;width: 100%;bottom:0px; ">
        <div class="span12 footer">
        		<div class="span3">
			<? 
			if (isset($_GET['app'])) echo "<a href='/reg/parent_detail.html'>"; 
			else echo "<a href='reg/parent_detail.html'>";
			?>
                <div class="menu-item">
                    <div class="span12">
                        <img src="/mobile/img_new/boy-color-white.svg">
                    </div>
                    <div class="span12">
                        <span data-key="Accounts" class="i18n">Accounts</span>
                    </div>
                </div>
			</a>
        </div>
        <div class="span3 active">
			<a href="#" id="missionsLink">
			<div class="menu-item">
				<div class="span12">
					<img src="/mobile/img_new/square-check-color-white.svg">
				</div>
				<div class="span12">
					<span data-key="Missions" class="i18n">Missions</span>
				</div>
			</div>
			</a>
		</div>
		<div class="span3">
			<a href="#" id="rankLink">
			<div class="menu-item">
				<div class="span12">
					<img src="/mobile/img_new/achievements-color-white.svg">
				</div>
				<div class="span12">
					<span data-key="Achievements" class="i18n">Achievements</span>
				</div>
			</div>
			</a>
		</div>
		<div class="span3">
			<a href="#" id="storeLink">
			<div class="menu-item">
				<div class="span12">
					<img src="/mobile/img_new/cart-color-white.svg">
				</div>
				<div class="span12">
					<span data-key="Rewards" class="i18n">Rewards</span>
				</div>
			</div>
			</a>
		</div>
    </div>
</div>