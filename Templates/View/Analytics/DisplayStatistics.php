<?php
header("Cache-Control: max-age=900");
if(isset($this->par["jsonData"])){
?>
    
<div class="navigation" id="navigation" ></div>
<div class="container" id="container" ></div>

<script>

    var bla = new Xclass(
        {
            year : "<?php echo $_GET['year'];?>",
            <?php if(isset($_GET['month'])){ echo "month: \"".$_GET['month']."\",\n"; }?>
            <?php if(isset($_GET['day'])){ echo "day: \"".$_GET['day']."\",\n"; }?>
            containerDivId : "container",
            navigationDivId : "navigation",
            domain : "<?php echo $_GET['domain'];?>"
        }
    );

</script>

<?php
    
}


