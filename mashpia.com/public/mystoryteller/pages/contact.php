<? include 'inc/head.php'; ?>

<section id="tz-main"><!--start tz-main-->

    <section class="tz-main-body">

        <div class="container-fluid"><!--start container-fluid-->

            <div class="tz-inner"><!--start tz-inner-->

                <section class="tz-content-wrap row-fluid">

                    <section id="tz-content" class="span7">

                        <section id="tz-component">

                            <div class="contact">

                                <div class="page-header">
                                    <h2>
                                        <span class="contact-name"></span>
                                    </h2>
                                </div>

                                <div class="contact-top">
                                  <div class="contact-miscinfo">

                                      <span class="jicons-none">

                                      </span>

                                      <span class="contact-misc">

                                          <p>
</p>                    </span>
                                    </div>

                                </div>
                                <div class="contact-content">

                                    <div class="contact-left pull-left">
                                        <h2>Contact Form</h2>

                                        <div class="contact-form">

                                            <form id="contact-form" method="post" class="form-validate form-horizontal">
                                                <fieldset>

                                                    <div class="control-group-name">

                                                        <input type="text" size="30" class="required invalid" value="Name (required)" id="jform_contact_name" name="jform[contact_name]"
                                                               aria-required="true" required="required" aria-invalid="true"
                                                               onblur="if (this.value=='') this.value='Name (required)';" onfocus="if (this.value=='Name (required)') this.value='';" />
                                                    </div>

                                                    <div class="control-group-email">

                                                        <input type="email" size="30" value="Email (required)" id="jform_contact_email" class="validate-email required invalid"
                                                               name="jform[contact_email]" aria-required="true" required="required" aria-invalid="true"
                                                               onblur="if (this.value=='') this.value='Email (required)';" onfocus="if (this.value=='Email (required)') this.value='';" />
                                                    </div>

                                                    <div class="control-group-subject">

                                                        <input type="hidden" size="60" class="required invalid" value="Subject (required)" id="jform_contact_emailmsg" name="jform[contact_subject]"
                                                               aria-required="true" required="required" aria-invalid="true"
                                                               onblur="if (this.value=='') this.value='Subject (required)';" onfocus="if (this.value=='Subject (required)') this.value='';" />
                                                    </div>

                                                    <div class="control-group-message">

                                                        <textarea name="jform[contact_message]" id="jform_contact_message" cols="50" rows="10" class="required"></textarea>
                                                    </div>

                                                    <div class="control-group">
                                                        <div class="controls">
                                                        </div>
                                                    </div>

                                                    <div class="form-actions">

                                                        <span class="tz-form-note">Your email address will never be shared.</span>
                                                        <button class="btn-base validate" type="submit">Send Email</button>
                                                        <input type="hidden" name="option" value="com_contact" />
                                                        <input type="hidden" name="task" value="contact.submit" />
                                                        <input type="hidden" name="return" value="" />
                                                        <input type="hidden" name="id" value="1:arrivederci" />
                                                        <input type="hidden" name="1d800c523fd89a059e5e8e15e756bf65" value="1" />

                                                    </div>

                                                </fieldset>

                                            </form>

                                        </div>

                                    </div>

                                    <div class="contact-right pull-right">

                                        <div class="address-inner">
                                            <h2>Contact Info</h2>
                                            <dl class="contact-address dl-horizontal">
                                                <dt>
                                                    <span class="jicons-none" >
                                                    </span>
                                                </dt>
                                                <dd>

                                                    <span class="contact-street">

                                                      
Rabbi Sholem Perl
              
<br/>
                                                    </span>
                                                </dd>

                                                <dt>

                                                    <span class="jicons-none" >

                                                    </span>
                                                </dt>

                                                <dd>

                                                    <span class="contact-telephone">

                                                       323 816 0304
                                                  </span>

                                                </dd>
                                                <dt>

                                                    <span class="jicons-none" >

                                                    </span>

                                                </dt>
                                                <dd>&nbsp;</dd>

                                                <dt>

                                                    <span class="jicons-none" >

                                                    </span>
                                                </dt>

                                                <dd>

                                                    <span class="contact-emailto">

                                                <a href="MAILTO:mystoryteller@sbcglobal.net">mystoryteller@sbcglobal.net</a> </dd>

                                            </dl>

                                        </div>

                                    </div>

                                    <div class="clr"></div>

                                </div>

                            </div><!--end contact-->

                        </section><!--end component-->

                    </section><!--end tz-content-->

                    <aside id="right-sidebar" class="span4 right-sidebar">

                        <div class="sidebar-nav">

                            <div class="box "><!--start box-->

                                <div>

                                   

                                  <div class="content"><!--start content-->

                                        <div class="custom"  >

                                           <img src="../images/thlogo.png" alt=""/></div>

                                    </div>

                                </div>

                            </div><!--end box-->

                          <!--end box--><!--end sidebar_new--><!--end sidebar_new-->

                        </div><!--end sidebar-nav-->

                    </aside><!--end right-sidebar-->

                    <div class="clr"></div>
                </section><!--end tz-content-wrap-->

            </div><!--end tz-inner-->

        </div><!--end container-fluid-->

    </section><!--end tz-main-body-->

</section><!--end tz-main-->

<? include 'inc/footer.html'; ?>

<script src="../js/jquery.min.js"></script>
<script>
	$( function() {
		$("#contact-form").submit( function() {
			var name = $("#jform_contact_name").val().trim();
			var subject = $("#jform_contact_emailmsg").val().trim();
			var email = $("#jform_contact_email").val().trim();
			var message = $("#jform_contact_message").val().trim();
			
			if (name == '' || subject == '' || email == '' || message == '') {
				alert("All fields are mandatory!");
				return false;
			}
				
			$.post('contactScript.php', {
				name : name, 
				subject : subject, 
				email : email, 
				message : message
			}, function( data ) {
				if (data == 1) {
					alert("Your email has been sent. Thank you.");
					window.location.href = "index.php";
				} else if (data = 0) {
					alert("There was a problem sending your email. Please try again.");
					return false;
				} else {
					alert( data );
					return false;
				}
			});
		});
	});
</script>
