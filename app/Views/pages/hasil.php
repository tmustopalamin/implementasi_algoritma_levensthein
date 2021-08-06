<div class="container">

<div class="row">
    <div class="col-md-12 py-3">
        <form action="<?php echo base_url(); ?>/upload/hasilkan_<?php echo $download_type; ?>" method="post">
            <input type="hidden" name="before" value="<?php echo $inputan; ?>">
            <input type="hidden" name="after" value="<?php echo $koreksi; ?>">
            <input type="submit" value="Download <?php echo $download_type; ?>">
        </form>        
    </div>
    <div class="col-md-12 py-3">
        <h3>Before</h3> 
        <?php echo $inputan; ?>
    </div>
    <div class="col-md-12 py-3">
        <h3>After</h3> 
        <?php echo $koreksi; ?>
    </div>
</div>

</div>