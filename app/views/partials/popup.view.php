<div class="popup <?=$classname?>" style='display:<?=$display?>'>
    <div class="content">
        <div class="container">
        <?php echo $content?>
        </div>
    </div>
    <div class="background"></div>
</div>

<script>
    function dismissMessage() {
        const box = document.querySelector('.<?=$classname?>');
        if (box) {
            box.style.display = 'none';
        }
    }
</script>