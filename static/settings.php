<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div>
        <p></p>
        <h2>Add desktop</h2>
        <input type="text" placeholder="Name" class="desktops_add">
        <input type="text" placeholder="Ip" class="desktops_add">
        <input type="text" placeholder="Mac" class="desktops_add">
        <input type="button" value="Add" onclick="desktops_add()">
    </div>
    <div>
        <p></p>
        <h2>Delete desktop</h2>
        <input type="text" placeholder="Name" class="desktops_delete">
        <input type="button" value="Delete" onclick="desktops_delete()">
    </div>
</body>
<script>

function desktops_add(){
    to_be_added = document.querySelectorAll(".desktops_add")
    fetch("<?php echo get_rest_url(null, "v1/wol/adddesktop") ?>", {
        headers: {
            "Content-Type": "application/json"
        },
        method: "POST",
        body: JSON.stringify({"name": to_be_added[0].value, "ip": to_be_added[1].value, "mac": to_be_added[2].value})
    })
    .then(response => response.text())
    .then(data => {
        console.log(data)
    })
}

function desktops_delete(){
    to_be_deleted = document.querySelector(".desktops_delete")
    fetch("<?php echo get_rest_url(null, "v1/wol/deldesktop") ?>", {
        headers: {
            "Content-Type": "application/json"
        },
        method: "POST",
        body: JSON.stringify({"name": to_be_deleted.value})
    })
    .then(response => response.text())
    .then(data => {
        console.log(data)
    })
}


</script>
</html>