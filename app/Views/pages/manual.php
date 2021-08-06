<div class="container">
  <div class="row ">
    <div class="col-md-6 g-5">    
      <div class="">        
            <p>Input Text:</p>
            <textarea rows="10" cols="50" name="textInput" id="textInput"></textarea>
            <div class="">
              <input type="submit" name="submit" value="Submit" id="submitText" onclick="proses()"/>
              <span id="info"></span>
              <p></p>
              <div id="download_doc">
              <form action="/upload/hasilkan_pdf" method="post">
                <input type="hidden" name="before" id="before" value="">
                <input type="hidden" name="after" id="after" value="">
                <input type="submit" value="Download pdf" id="kirimText" disabled="disabled">
              </form>  
              </div>
            </div>           
        
      </div>
    </div>
    <div class="col-md-6 g-5">    
      <div class="">        
            <p>Output Text:</p>
            <textarea rows="10" name="textOutput" cols="50" id="textOutput"></textarea>        
      </div>
    </div>
  </div>
</div>

<script src="<?php echo base_url(); ?>/assets/js/jquery.min.js"></script>

<script>
    function proses() {
      
    var textInput = document.getElementById("textInput").value;
      if(textInput != ""){
        $.ajax({
            type : "POST",  //type of method
            url  : "<?php base_url(); ?>/do_manual",  //your page
            data : { textInput : textInput },// passing the values
            beforeSend : function(){
              document.getElementById("info").innerHTML = 'Sedang di Proses.';
              document.getElementById("submitText").disabled="disabled";  
            },
            complete : function(){
              document.getElementById("submitText").disabled="";  
              document.getElementById("info").innerHTML = 'Selesai.';
              document.getElementById("before").value = document.getElementById("textInput").value;
              document.getElementById("after").value = document.getElementById("textOutput").value;
              document.getElementById("kirimText").disabled="";  
            },
            success: function(res){  
              var myObj = JSON.parse(res);
              document.getElementById("textOutput").innerHTML = myObj['koreksi'];
            }
        });
      }
    }
</script>
