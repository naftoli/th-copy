     </div>

    </div> 
        <footer>
       
            <div class="container text-white text-center p-3 mt-4">
                <div class="mt-5">
                    <a class="" href="https://www.tzivoshashem.org"><img id="home-page-logo" src="https://www.tzivoshashem.org/wp-content/uploads/2017/02/Main-Logo.png" alt="logo image"></a><br><br>
                    <div class="text-yellow d-inline">Celebrating 40 years of Tzivos Hashem - World's largest Jewish children's organization.</div><br><br>
                </div>
                    <!-- &copy; Developed by UriSaul <?= date('Y'); ?> -->
            </div>
        </footer>
           
           </div>
             
           
           
           
              
               
           
           
           
           
           <!-- Modal -->
           <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
             <div class="modal-dialog modal-dialog-centered" role="document">
               <div class="modal-content">
                 <div class="modal-header">
                   <h5 class="modal-title" id="exampleModalLongTitle">Submit a mission</h5>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                   </button>
                 </div>
                 <div id="modal-body" class="modal-body text-center">
                   
                  
                   

                    <div id="stage-1">
                    <button id="has-account-btn" class="account-btn btn w-100" >I already have an account</button><br><br>
                        <div>
                            <h4>Please fill out your details:</h4>
                        </div>
                        <form id="mission-form-modal" action="" method="post" class="mt-4">
                           <div class="form-group row">
                               <label for="exampleInputEmail1" class="col-lg-4 col-form-label text-left">Your First Name:</label> &nbsp; &nbsp;
                               <input type="text" class="form-control mission-form-inp col-lg-7" name="first_name" id="first-name-inp" placeholder="">
                               <div id="first-name-msg" class="text-danger d-none offset-lg-4 col-lg-7">First name not valid</div>
                           </div>
                           <div class="form-group row">
                               <label for="exampleInputEmail1" class="col-lg-4 col-form-label text-left">Your Last Name:</label> &nbsp; &nbsp;
                               <input type="text" class="form-control mission-form-inp col-lg-7" name="last_name" id="last-name-inp" placeholder="">
                               <div id="last-name-msg" class="text-danger d-none offset-lg-4 col-lg-7">Last name not valid</div>
                           </div>
                            <div class="form-group row">
                                <label for="dob" class="col-lg-4 col-form-label text-left">Your DOB:</label> &nbsp; &nbsp;
                                <input type="date" class="form-control mission-form-inp col-lg-7" name="dob" id="dob" placeholder="" required>
                            </div>
                           <div class="form-group row">
                               <label for="exampleInputEmail1" class="col-lg-4 col-form-label px-0 text-left">&nbsp;&nbsp;Parent email address:</label> &nbsp; &nbsp;
                               <input type="text" class="form-control mission-form-inp col-lg-7" name="email_address" id="email-inp" aria-describedby="emailHelp" placeholder="">
                               <div id="email-msg" class="text-danger d-none offset-lg-4 col-lg-7">Email not valid</div>
                           </div>
                           <div class="px-5">
                                <small>This will be used to inform you if you win and of any future mission campaigns you may be interested in.</small>
                           </div>
                           <input id="mission-num-inp" type="hidden" name="task_checked_off">
                           <button type="submit" name="submit" id="mission-form-submit-modal" class="btn form-submit-btn">Join and submit your entry!</button>
                        </form>
                    </div>

                    <div id="stage-2" style="display:none">
                    <button id="no-account-btn" class="account-btn btn w-100" >I don't have a Tzivos Hashem account yet</button><br><br>
                        <div>
                            <h4>Please fill out your details:</h4>
                        </div>
                       <form id="mission-form-modal-member" action="" method="post" class="mt-4">
                            <div class="form-group row">
                               <label for="exampleInputEmail1" class="col-lg-6 col-form-label text-left">Tzivos Hashem Serial Number:</label> &nbsp; &nbsp;
                               <input type="text" class="form-control mission-form-inp col-lg-5" name="serial_number" id="serial-number-inp" aria-describedby="emailHelp" placeholder="">
                               <div id="serial-number-msg" class="text-danger d-none offset-lg-4 col-lg-7">Serial number not valid</div>
                           </div>
                           <input id="mission-num-inp-member" type="hidden" name="task_checked_off">
                           <button type="submit" name="submit" id="mission-form-submit-modal-member" class="btn form-submit-btn">Submit your mission!</button>
                        </form>
                    </div>

                       
                      
                 </div>
                 <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                 </div>
               </div>
             </div>
           </div>
           
           
           
           <script src="./js/jquery/jquery-3.5.1.min.js"></script> 
           <script src="./js/bootstrap/bootstrap.min.js"></script>  
           <script src="./js/main.js"></script>
           <script src="./js/js.cookie.js"></script>
           </body>
           </html>