<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
</head>
<body>
    <p hidden ><?php echo wp_get_current_user()->ID?></p>
    <p hidden id="dirs"><?php get_dirs() ?></p>
    <p hidden id="nonce"><?php echo wp_create_nonce("admin_nonce") ?></p>
    <div>
        <p id="add_response"></p>
        <h2>Add/update desktop</h2>
        <input type="text" placeholder="Name" class="desktops_add">
        <input type="text" placeholder="Ip" class="desktops_add">
        <input type="text" placeholder="Mac" class="desktops_add">
    </div>
    <div>
        <input type="button" value="Add" onclick="desktops_add('add')">
        <input type="button" value="Update" onclick="desktops_add('update')">
    </div>
    <div>
        <p id="del_response"></p>
        <h2>Delete desktop</h2>
        <input type="text" placeholder="Name" class="desktops_delete">
    </div>
    <div>
        <input type="button" value="Delete" onclick="desktops_delete()">
    </div>
</body>
<script>
let nonce = document.querySelector("#nonce").innerHTML
let dirs

function getDirs(){
    dirsstr = document.querySelector("#dirs").innerHTML
    dirlist = dirsstr.split("|")
    dirs = {
        "staticdir": dirlist[0],
        "endpointdir": dirlist[1]
    }   
}


function desktops_add(action){

    to_be_added = document.querySelectorAll(".desktops_add")
    add_response = document.querySelector("#add_response")
    fetch(`${dirs["endpointdir"]}v1/wol/adddesktop`, {
        headers: {
            "Content-Type": "application/json"
        },
        method: "POST",
        body: JSON.stringify({
            "name": to_be_added[0].value,
            "ip": to_be_added[1].value,
            "mac": to_be_added[2].value,
            "admin_nonce": nonce,
            "action": action
        })
    })
    .then(response => response.text())
    .then(data => {
        if (data == "nonce_error"){
            add_response.innerHTML = "Nonce verification failed"
        }
        else if (data == "error_already_exists"){
            add_response.innerHTML = "A desktop with the same name already exists"
        }
        else if (data == "error_non_existent"){
            add_response.innerHTML = "This desktop doesnt exist"
        }
        else if (data == "success_add"){
            add_response.innerHTML = "Desktop added successfully"
        }
        else if (data == "success_update"){
            add_response.innerHTML = "Desktop updated successfully"
        }
        else {
            add_response.innerHTML = data
        }
    })
}

function desktops_delete(){

    to_be_deleted = document.querySelector(".desktops_delete")
    del_response = document.querySelector("#del_response")
    fetch(`${dirs["endpointdir"]}v1/wol/deldesktop`, {
        headers: {
            "Content-Type": "application/json"
        },
        method: "POST",
        body: JSON.stringify({"name": to_be_deleted.value, "admin_nonce": nonce})
    })
    .then(response => response.text())
    .then(data => {
        if (data == "nonce_error"){
            del_response.innerHTML = "Nonce verification failed"
        }
        else if (data == "error_non_existent"){
            add_response.innerHTML = "This desktop doesnt exist"
        }
        else if (data == "success_del"){
            del_response.innerHTML = "Desktop deleted succesfully"
        }
    })
}


getDirs()

</script>
</html>