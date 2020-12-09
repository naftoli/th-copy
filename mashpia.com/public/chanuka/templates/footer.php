     </div>

    </div> 
        <footer>
            <div class="container text-white text-center p-3 mt-4">
                &copy; Developed by Urisaul <?= date('Y');?>
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
                 <div class="modal-body text-center">
                   <button id="has-account-btn" class="submit-btn btn w-100" >I already have an account</button>
                   <form id="mission-form-modal" action="" method="post" class="mt-4">
                       <div id="stage-1">
                           <div class="form-group d-flex">
                               <label for="exampleInputEmail1">First Name:</label> &nbsp; &nbsp;
                               <input type="text" class="form-control mission-form-inp w-75" name="first-name" id="first-name-inp" aria-describedby="emailHelp" placeholder="">
                           </div>
                           <div class="form-group">
                               <label for="exampleInputEmail1">Last Name:</label>
                               <input type="text" class="form-control mission-form-inp" name="last-name" id="last-name-inp" aria-describedby="emailHelp" placeholder="">
                           </div>
                           <div class="form-group">
                               <label for="exampleInputEmail1">Birthday:</label>
                               <input type="date" class="form-control mission-form-inp" name="birthday" id="birthday-inp" aria-describedby="emailHelp" placeholder="">
                           </div>
                           <div class="form-group">
                               <label for="exampleInputEmail1">School:</label>
                               <input type="text" class="form-control mission-form-inp" name="school" id="school-inp" aria-describedby="emailHelp" placeholder="">
                           </div>
                           <button type="button" id="mission-form-submit-modal" disabled class="btn btn-primary">Submit</button>
                       </div>
                       <div id="stage-2" style="display:none">
                           <div class="form-group">
                               <label for="exampleInputEmail1">Email:</label>
                               <input type="text" class="form-control mission-form-inp" name="email" id="email-inp" aria-describedby="emailHelp" placeholder="">
                           </div>
                           <div class="form-group">
                               <label for="exampleInputEmail1">Password:</label>
                               <input type="password" class="form-control mission-form-inp" name="password" id="password-inp" aria-describedby="emailHelp" placeholder="">
                           </div>
                           <small>This will be used to inform you if you win and of any future mission campaigns you may be interested in.</small><br>
                           <input id="mission-num-inp" type="hidden" name="mission-num">
                           <button type="submit" name="submit" id="mission-form-submit-modal-final" disabled class="btn btn-primary">Join and submit your entry!</button>
                       </div>
                   </form>
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
           </body>
           </html>