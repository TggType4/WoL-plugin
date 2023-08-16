<body>
    <p hidden id="userid"><?php echo wp_get_current_user()->ID ?></p>
    <p hidden id="dirs"><?php get_dirs() ?></p>
    <p hidden id="nonce"><?php echo wp_create_nonce("wol_nonce") ?></p>
    <div id="statuses">
        <button id="refresh_statuses" onclick="refresh()">Refresh</button>
    </div>
</body>
