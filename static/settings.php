<?php 
namespace WolPlugin;
$user_id = wp_get_current_user()->ID;
$dirs = get_dirs();
$nonce = wp_create_nonce("admin_nonce");
$cURL_fallback = get_option("wol_cURL_fallback");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
</head>
<body>
    <p hidden ><?php echo $user_id ?></p>
    <p hidden id="dirs"><?php echo $dirs ?></p>
    <p hidden id="nonce"><?php echo $nonce?></p>
    <div>
        <p id="add_response"></p>
        <h2>Add/update desktop</h2>
        <input type="text" placeholder="Name" class="desktops_add" id="desktops_add_name">
        <input type="text" placeholder="Ip" class="desktops_add" id="desktops_add_ip">
        <input type="text" placeholder="Mac" class="desktops_add" id="desktops_add_mac">
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
    <hr>
</body>
<script>
let nonce = document.querySelector("#nonce").innerHTML
let dirs
/*let fallback_option = document.querySelector("#cURL_fallback")
let fallback_option_response = document.querySelector("#fallback_response")
fallback_option.checked;
fallback_option.addEventListener("change", () => {
    fetch(`${dirs["endpointdir"]}v1/wol/cURLfallbacksetting`, {
        headers: {
            "Content-Type": "application/json"
        },
        method: "POST",
        body: JSON.stringify({
            "admin_nonce": nonce,
            "option": fallback_option.checked*1
        })
    })
    .then(response => response.text())
    .then(data => {
        switch (data){
            case "success":
                fallback_option_response.innerHTML = "Fallback setting successfully updated"
                break
            case "fail_update":
                fallback_option_response.innerHTML = "Couldn't update fallback setting"
                break
            case "fail_add":
                fallback_option_response.innerHTML = "Couldn't create setting for cURL fallback"
                break
            default:
                fallback_option_response.innerHTML = data
        }   
    })
})*/



function getDirs(){
    dirsstr = document.querySelector("#dirs").innerHTML
    dirlist = dirsstr.split("|")
    dirs = {
        "staticdir": dirlist[0],
        "endpointdir": dirlist[1]
    }   
}


function desktops_add(action){
    [to_be_added_name, to_be_added_ip, to_be_added_mac] = 
    [document.querySelector("#desktops_add_name"), document.querySelector("#desktops_add_ip"), document.querySelector("#desktops_add_mac")];
    add_response = document.querySelector("#add_response")
    fetch(`${dirs["endpointdir"]}v1/wol/adddesktop`, {
        headers: {
            "Content-Type": "application/json"
        },
        method: "POST",
        body: JSON.stringify({
            "name": to_be_added_name.value,
            "ip": to_be_added_ip.value,
            "mac": to_be_added_mac.value,
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
            add_response.innerHTML = "Desktop added successfully";
            [to_be_added_name.value, to_be_added_ip.value, to_be_added_mac.value] = ["", "", ""]
        }
        else if (data == "success_update"){
            add_response.innerHTML = "Desktop updated successfully";
            [to_be_added_name.value, to_be_added_ip.value, to_be_added_mac.value] = ["", "", ""]
        }
        else if (data == "error_add"){
            add_response.innerHTML = "Failed adding desktop"
        }
        else if (data == "error_update"){
            add_response.innerHTML = "Failed updating desktop"
        }
        else {
            add_response.innerHTML = data
        }
    })
}

function desktops_delete(){

    to_be_deleted = document.querySelector(".desktops_delete")
    to_be_deleted_name = to_be_deleted.value
    del_response = document.querySelector("#del_response")
    fetch(`${dirs["endpointdir"]}v1/wol/deldesktop`, {
        headers: {
            "Content-Type": "application/json"
        },
        method: "POST",
        body: JSON.stringify({"name": to_be_deleted_name, "admin_nonce": nonce})
    })
    .then(response => response.text())
    .then(data => {
        if (data == "nonce_error"){
            del_response.innerHTML = "Nonce verification failed"
        }
        else if (data == "error_non_existent"){
            del_response.innerHTML = "This desktop doesnt exist"
        }
        else if (data == "success_del"){
            del_response.innerHTML = "Desktop deleted succesfully"
            to_be_deleted.value = ""
        }
        else if (data == "error_del"){
            add_response.innerHTML = "Failed deleting desktop"
        }
        else {
            del_response.innerHTML = data
        }
    })
}


getDirs()

</script>
</html>
