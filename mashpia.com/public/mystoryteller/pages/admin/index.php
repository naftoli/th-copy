<?
session_start();
if (isset($_SESSION['login']) && $_SESSION['login'] == 2) {
	header("Location: reports.php");
	exit;
}
?>

<? include 'header.php'; ?>

<section id="tz-main"><!-- tz-main-->

    <section class="tz-main-body">

        <div class="container-fluid"><!--start container-fluid-->

            <div class="tz-inner"><!--start tz-inner-->

              <section class="tz-content-wrap row-fluid">

                    <section id="tz-content" class="span8">

                        <section id="tz-component">

                            <div class="TzBlog blog">

                                <div class="TzBlogInner">
                                	
	                                <form action="login.php" method="post">
										<table>
											<tr>
												<td>Username:</td>
												<td><input type="text" name="username" /></td>
											</tr>
											<tr>
												<td>Password:</td>
												<td><input type="text" name="password" /></td>
											</tr>
											<tr>
												<td></td>
												<td><input type="submit" name="submit" value="login" /></td>
											</tr>
										</table>			
									</form>
	                                    
	                                <div class="clearfix"></div>

                                </div>

                            </div><!--end TzBlog-->
                            
                    <div class="clr"></div>

                </section><!--end tz-content-wrap-->

            </div><!--end tz-inner-->

      </div><!--end container-fluid-->

    </section><!--end tz-main-body-->

</section><!--end tz-main-->

<? include 'footer.php'; ?>