<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div id="statuses">
        <button id="refresh_statuses" onclick="refresh()">Refresh</button>
    </div>
</body>
<script>

let wol_success_msg = "Wake-on-LAN request sent successfully"
let wol_error_msg = "Error sending Wake-on-LAN request"
let wol_nonce_error_msg = "Nonce verification failed, Wake-on-LAN request not sent"
let loading_msg = "Checking status..."
let desktops
let nonce
let logged_in

function handleWolResponse(response_text, on_button){
    desktop = on_button.alt
    desktop_div = document.getElementById(desktop)
    response_element = document.querySelector(`#${desktop}>.response_element`)
    response_element.className = "response_element"
    if (response_text == "sent"){
        response_text = wol_success_msg
        on_button.classList.add("inactive")

    }
    else if (response_text == "nonce_error"){
        response_text = wol_nonce_error_msg
    }
    else {
        response_text = wol_error_msg
    }
    response_element.innerHTML = response_text
}


function desktops_generate(){
    let statuses = document.getElementById("statuses")
    for (let desktop in desktops){
        let desktop_div = document.createElement("div") 
        desktop_div.id = desktop
        let name = document.createElement("p")
        let name_str = document.createTextNode(desktop)
        name.appendChild(name_str)
        let ip = document.createElement("p")
        let ip_str = document.createTextNode(desktops[desktop]["ip"])
        ip.appendChild(ip_str)
        let mac = document.createElement("p")
        let mac_str = document.createTextNode(desktops[desktop]["mac"])
        mac.appendChild(mac_str)
        let status = document.createElement("p")
        status.classList.add("dot_red") 
        let status_dot = document.createTextNode("⬤")
        status.appendChild(status_dot)
        let on_button = document.createElement("img")
        on_button.src = "<?php echo (plugin_dir_url(__FILE__) . "/on_button.png")?>"
        on_button.alt = desktop
        on_button.classList.add("inactive")
        on_button.addEventListener("click", function (){
            if (!Array.from(on_button.classList).includes("inactive") && logged_in){
                fetch("<?php echo get_rest_url(null, "v1/wol/sendwol") ?>", {
                    headers: {
                        "Content-Type": "application/json"
                    },
                    method: "POST",
                    body: JSON.stringify({"name": desktop, "nonce": nonce})
                })
                .then(response => response.text()) 
                .then(data => {
                    handleWolResponse(data, on_button)
                })          
            }
        })
        let status_check_indicator = document.createElement("p")
        status_check_indicator.classList.add("status_check_indicator")
        status_check_indicator.innerHTML = loading_msg
        let response_element = document.createElement("p")
        response_element.classList.add("response_element")
        desktop_div.append(name, ip, mac, status, on_button, status_check_indicator, response_element)
        statuses.appendChild(desktop_div)
    }
    getStatus()
}


function getLoginStatus(){
    fetch("<?php echo get_rest_url(null, "v1/wol/getloginstatus") ?>", {
        method: "GET"
    })
    .then(response => response.text())
    .then(data => {
        console.log(data)
    })
}


function getNonce(){
    fetch("<?php echo get_rest_url(null, "v1/wol/getnonce") ?>", {
        method: "GET"
    })
    .then(response => response.text())
    .then(data => {
        nonce = data
    })
}


function getDesktops(){
    fetch("<?php echo get_rest_url(null, "v1/wol/getdesktops") ?>", {
        method: "GET"
    })
    .then(response => response.text())
    .then(data => {
        desktops = JSON.parse(data)
        desktops_generate()
    })

}


function getStatus(){
    for (let desktop in desktops){
        fetch("<?php echo get_rest_url(null, "v1/wol/getstatus") ?>", {
            headers: {
                "Content-Type": "application/json" 
            },
            method: "POST",
            body: JSON.stringify({"name": desktop})

        })
        .then(response => response.text()) 
        .then(data => {
            status_check_indicator = document.querySelector(`#${desktop}>.status_check_indicator`)
            status_check_indicator.remove()
            if (data == "online") {
                status_dot = document.querySelector(`#${desktop}>.dot_red`)
                status_dot.classList.replace("dot_red", "dot_green") 
            }
            else {
                if (logged_in){
                    on_button = document.querySelector(`#${desktop}>img`)
                    on_button.classList.remove("inactive")
                }
            }
        })
    }
}

function refresh(){
    was_online = document.querySelectorAll(".dot_green")
    was_online.forEach(element => {
        element.classList.replace("dot_green", "dot_red")
    });
    hidden = document.querySelectorAll("#statuses>div>img")
    hidden.forEach(element => {
        element.classList.add("inactive")
    });
    no_status_check = document.querySelectorAll("#statuses>div:not(div>.status_check_indicator)")
    no_status_check.forEach(element => {
        status_check_indicator = document.createElement("p")
        status_check_indicator.classList.add("status_check_indicator")
        status_check_indicator.innerHTML = loading_msg
        element.appendChild(status_check_indicator)
    });
    responses = document.querySelectorAll(".response_element")
    responses.forEach(element => {
        element.innerHTML = ""
    });
    getStatus()
}

getLoginStatus()
getNonce()
getDesktops()

</script>
</html>