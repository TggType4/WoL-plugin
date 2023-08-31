<?php
namespace WolPlugin;
$user_id = wp_get_current_user()->ID;
$dirs = get_dirs();
$nonce = wp_create_nonce("wol_nonce");
?>

<body>
    <p hidden id="userid"><?php echo $user_id ?></p>
    <p hidden id="dirs"><?php echo $dirs ?></p>
    <p hidden id="nonce"><?php echo $nonce ?></p>
    <div id="statuses">
        <button id="refresh_statuses" onclick="refresh()">Refresh</button>
    </div>
</body>
