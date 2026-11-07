heartbeat();

function heartbeat(){
    console.log("Mandando beat");

    fetch('timeout.php', {
        method: "post",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            "sinal" : "sinal"
        })
    }).then(response=>response.json())
    .then(data=>{
        if(data["expirou"] ==  true){
            window.location.href = "logout.php";
        }
    })
}

setInterval(heartbeat, 30000);