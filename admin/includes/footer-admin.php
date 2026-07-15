</div><!-- /.main -->

<?php if (!empty($scripts_extra_admin)): foreach ($scripts_extra_admin as $src): ?>
        <script src="<?= htmlspecialchars($src) ?>"></script>
<?php endforeach;
endif; ?>
</body>

</html>