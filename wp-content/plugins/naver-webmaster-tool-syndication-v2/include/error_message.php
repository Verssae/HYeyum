<?php if ($this->errorMessage): ?>
    <div class='error'>
        <p><strong class="error-message"><?php echo $this->errorMessage; ?></strong></p>
        <?php if ($this->errorNaverMessage): ?>
            <p>응답 메세지: <?php echo $this->errorNaverMessage ?></p>
        <?php endif ?>
        <?php if ($this->errorNaverCode): ?>
            <p>응답 코드: <?php echo $this->errorNaverCode ?></p>
        <?php endif ?>
    </div>
<?php endif ?>