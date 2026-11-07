heartbeat();

// Verificação de logout por tempo parado
let tempo_limite = 30 * 60 * 1000 // Minutos para milissegundos
let timer;
iniciarContagem();

function iniciarContagem(){
    clearTimeout(timer);
    timer = setTimeout(()=>{
        alert("Sessão expirada por inatividade.")
        window.location.href = "logout.php";
    }, tempo_limite);   
}

document.addEventListener("mousemove", iniciarContagem);
document.addEventListener("keypress", iniciarContagem);
document.addEventListener("click", iniciarContagem);


// Verificação de logout por página fechada
function heartbeat(){
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