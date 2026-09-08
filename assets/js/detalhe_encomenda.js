// Adicionar observação
const btnObs = document.getElementById('btnObs');
if(btnObs){
    btnObs.addEventListener('click', async function () {
        const obs = document.getElementById('obs');
        const id_encomenda = obs.dataset.id_encomenda;
    
        if(!obs.value){
            mostrarMsg("red", "Introduza uma observação");
            return;
        }
    
        const response = await fetch('detalhe_encomenda.php', {
            method:"post",
            headers:{ 'Content-Type': 'application/json' },
            body:JSON.stringify({
                acao:"add_obs",
                id_encomenda:id_encomenda,
                obs:obs.value
            })
        });
    
        const data = await response.json();
    
        alert(data["resultado"]);
        location.reload();
    });
}


// Botao de concluir
const btnConcluir = document.getElementById('btnConcluir');
if(btnConcluir){
    btnConcluir.addEventListener('click', ()=>{
        document.getElementById('modalConcluir').style.display = "flex";
    });
}

// Fechar modal de concluir
const btnFecharConcluir = document.getElementById('fecharConcluir');
if(btnFecharConcluir){
    btnFecharConcluir.addEventListener('click', ()=>{
        document.getElementById('modalConcluir').style.display = "none";
    });
}

// Confirmar concluir
const btnConfirmarConcluir = document.getElementById('confirmarConcluir');
if(btnConfirmarConcluir){
    btnConfirmarConcluir.addEventListener('click', async function (){
        const response = await fetch('detalhe_encomenda.php', {
            method:"post",
            headers:{ 'Content-Type': 'application/json' },
            body:JSON.stringify({
                "acao":"concluir_encomenda",
                "id_encomenda":btnConfirmarConcluir.dataset.id_encomenda
            })
        })

        const data = await response.json();

        if(data["resultado"] == "sucesso"){
            alert(data["msg"]);
            location.reload();
        }
        else{
            alert(data["msg"]);
            location.reload();
        }
    });
}

// Botao de entregar
const btnEntregar = document.getElementById('btnEntregar');
if(btnEntregar){
    btnEntregar.addEventListener('click', ()=>{
        document.getElementById('modalEntregar').style.display = "flex";
    });
}

// Fechar modal de entregar
const btnFecharConfirmar = document.getElementById('fecharConfirmar');
if(btnFecharConfirmar){
    btnFecharConfirmar.addEventListener('click', ()=>{
        document.getElementById('modalEntregar').style.display = "none";
    });
}

// Confirmar entrega
const btnConfirmarEntregar = document.getElementById('confirmarEntregar');
if(btnConfirmarEntregar){
    btnConfirmarEntregar.addEventListener('click', async function (){
        const caucaoLevantamento = document.getElementById('caucaoLevantamento').value ?? 0;

        const response = await fetch('detalhe_encomenda.php', {
            method:"post",
            headers:{ 'Content-Type': 'application/json' },
            body:JSON.stringify({
                "acao":"entregar_encomenda",
                "caucaoLevantamento":caucaoLevantamento,
                "id_encomenda":btnConfirmarEntregar.dataset.id_encomenda
            })
        })

        const data = await response.json();

        if(data["resultado"] == "sucesso"){
            alert(data["msg"]);
            window.location.href = "index.php";
        }
        else{
            alert(data["msg"]);
            location.reload();
        }
    });
}


// Botao de entregar
const btnCancelar = document.getElementById('btnCancelar');
if(btnCancelar){
    btnCancelar.addEventListener('click', ()=>{
        document.getElementById('modalCancelar').style.display = "flex";
    });
}

// Fechar modal de entregar
const btnFecharCancelar = document.getElementById('fecharCancelar');
if(btnFecharCancelar){
    btnFecharCancelar.addEventListener('click', ()=>{
        document.getElementById('modalCancelar').style.display = "none";
    });
}

// Confirmar entrega
const btnConfirmarCancelar = document.getElementById('confirmarCancelar');
if(btnConfirmarCancelar){
    btnConfirmarCancelar.addEventListener('click', async function (){
        const response = await fetch('detalhe_encomenda.php', {
            method:"post",
            headers:{ 'Content-Type': 'application/json' },
            body:JSON.stringify({
                "acao":"cancelar_encomenda",
                "id_encomenda":btnConfirmarEntregar.dataset.id_encomenda
            })
        })

        const data = await response.json();

        if(data["resultado"] == "sucesso"){
            alert(data["msg"]);
            window.location.href = "index.php";
        }
        else{
            alert(data["msg"]);
            location.reload();
        }
    });
}
 
// Event listener novo aviso
const btnNovoAviso = document.getElementById('btnNovoAviso');
if(btnNovoAviso){
    btnNovoAviso.addEventListener('click', async ()=>{
        const response = await fetch('detalhe_encomenda.php', {
            method:"post",
            headers:{'Content-type':'application/json'},
            body:JSON.stringify({
                acao:"add_obs",
                id_encomenda:btnNovoAviso.dataset.id_encomenda,
                obs: "MPP3: O cliente foi avisado novamente para vir levantar a encomenda."
            })
        });

        if(response.ok){
            alert("Novo aviso enviado com sucesso!");
        }

        fetch('enviar_email.php', {
            method:"post",
            headers:{'Content-type':'application/json'},
            body:JSON.stringify({
                "tipo_email":"novo_aviso",
                "id_encomenda":btnNovoAviso.dataset.id_encomenda
            })
        });

        location.reload();
    });
}


 const msg = document.getElementById('msgErro');
 function mostrarMsg(cor, conteudo) {
    msg.style.color = cor;
    msg.innerText = conteudo;

    setTimeout(() => {
        msg.innerText = "";
    }, 2000)
}

// Botão de editar email
const btnEditarEmail = document.getElementById('btnEditarEmail');
if(btnEditarEmail){
    btnEditarEmail.addEventListener('click', ()=>{
        const modal = document.getElementById('modalEditarEmail');
        const input = document.getElementById('inputNovoEmail');
        input.value = btnEditarEmail.dataset.email;
        modal.style.display = "flex";
    });
}

// Fechar modal editar email (botão Cancelar)
const btnFecharEditarEmail = document.getElementById('fecharEditarEmail');
if(btnFecharEditarEmail){
    btnFecharEditarEmail.addEventListener('click', ()=>{
        document.getElementById('modalEditarEmail').style.display = "none";
    });
}

// Fechar modal editar email (X)
const closeModalEditarEmail = document.getElementById('closeModalEditarEmail');
if(closeModalEditarEmail){
    closeModalEditarEmail.addEventListener('click', ()=>{
        document.getElementById('modalEditarEmail').style.display = "none";
    });
}

// Confirmar editar email
const formEditarEmail = document.getElementById('formEditarEmail');
if(formEditarEmail){
    formEditarEmail.addEventListener('submit', async function(e){
        e.preventDefault();

        const novoEmail = document.getElementById('inputNovoEmail').value.trim();
        const btnConfirmarEditarEmail = document.getElementById('confirmarEditarEmail');

        const response = await fetch('detalhe_encomenda.php', {
            method:"post",
            headers:{ 'Content-Type': 'application/json' },
            body:JSON.stringify({
                acao:"editar_email",
                id_encomenda: btnConfirmarEditarEmail.dataset.id_encomenda,
                email: novoEmail
            })
        });

        const data = await response.json();

        if(data["resultado"] == "sucesso"){
            alert(data["msg"]);
            location.reload();
        } else {
            alert(data["msg"]);
        }
    });
}

// Reenviar email de confirmacao
const btnReenviarEmail = document.getElementById("btnReenviarEmail");
btnReenviarEmail.addEventListener('click', async function () {
    id_encomenda = btnReenviarEmail.dataset.id_encomenda;
    
    const response = await fetch('detalhe_encomenda.php', {
        method:"post",
        headers: {"Content-Type":"application/son"},
        body:JSON.stringify({
            acao:"reenviar_email",
            id_encomenda : id_encomenda
        })
    });
    
    const data = await response.json();
    
    if(data["resultado"] == "sucesso"){
        alert(data["msg"]);
        location.reload();
    } else {
        alert(data["msg"]);
    }
})