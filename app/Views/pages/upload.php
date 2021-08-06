<div class="container-fluid">
  <div class="row">
    <div class="col-md-12">
    
      <div class="position-absolute top-50 start-50 translate-middle">
        <form action="<?php echo base_url(); ?>/upload/do_upload" method="post" enctype="multipart/form-data">
            <h4>Upload a File</h4>
            <p>Select file to upload:</p>
            <input type="file" name="uploadedFile" id="fileToUpload">
            <input type="submit" name="submit" value="Start Upload">
        </form>
      </div>
    </div>
  </div>
</div>