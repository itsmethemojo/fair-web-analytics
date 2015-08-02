<?php
header("Cache-Control: max-age=900");
if(isset($this->par["jsonData"])){
?>
    
<div class="navigation" id="navigation" ></div>
<div class="container" id="container" ></div>

<script>

    var bla = new ClickStatistic(
        {
            <?php if(isset($this->par['year'])){ echo "year: \"".$this->par['year']."\",\n"; }?>
            <?php if(isset($this->par['month'])){ echo "month: \"".$this->par['month']."\",\n"; }?>
            <?php if(isset($this->par['day'])){ echo "day: \"".$this->par['day']."\",\n"; }?>
            containerDivId : "container",
            navigationDivId : "navigation",
            domain : "<?php echo $this->par['domain'];?>"
        }
    );

</script>

<?php
    
}


