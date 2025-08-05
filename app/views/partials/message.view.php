
<div id="message-content">
    <div class="background"></div>
    <div class="container message-container">
        <p class="message"><?php echo $message?></p>
        <button class="button" onclick="dismissMessage()">Back</button>
    </div>
</div>


<script>
    function dismissMessage() {
        const box = document.getElementById('message-content');
        if (box) {
            box.remove(); // Removes the element from DOM
        }
    }
</script>