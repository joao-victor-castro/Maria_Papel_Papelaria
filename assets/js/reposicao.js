// Variável que vai guardar todos os itens da tabela
let dados = {};
// Flag de qual tabela esta ativa
let tabelaAtiva = "pedidos";
// Carrega a tabela assim que a pagina abre
carregartabela();

// Modal de adicionar pedidos
const btnAddPedido = document.getElementById('btn-add-pedido');
const modalReposicao = document.getElementById('modal-reposicao');
const closeModal = document.getElementById('close-modal');

// Modal de Informações
const modalInfo = document.getElementById('modal-info');
const closeModalInfo = document.getElementById('close-modal-info');

// Modal de Editar
const modalEditar = document.getElementById('modal-editar');
const closeModalEditar = document.getElementById('close-modal-editar');

// Modal de cancelar pedido
const modalCancelar = document.getElementById('modal-cancelar');
const closeModalCancelar = document.getElementById('close-modal-cancelar');


// Abre o modal ao clicar no botão
if (btnAddPedido) {
    btnAddPedido.addEventListener('click', function (e) {
        e.preventDefault();

        // Limpa todos os inputs
        const inputs = document.querySelectorAll('input');
        const select_urgencia = document.getElementById('urgencia');
        const select_tipo = document.getElementById('tipo');
        inputs.forEach(element => {
            
            if (element.id == "quantidade"){
                element.value = 1;
                return;
            }
            element.value = "";
        });

        select_urgencia.value = "muito urgente";
        select_tipo.value = "papelaria";

        modalReposicao.style.display = 'flex';
    });
}

// Fecha o modal ao clicar no X
if (closeModal) {
    closeModal.addEventListener('click', function () {
        modalReposicao.style.display = 'none';
    });
}

// Fecha o modal Info ao clicar no X
if (closeModalInfo) {
    closeModalInfo.addEventListener('click', function () {
        modalInfo.style.display = 'none';
    });
}

// Fecha o modal Editar ao clicar no X
if (closeModalEditar) {
    closeModalEditar.addEventListener('click', function () {
        modalEditar.style.display = 'none';
    });
}

// Fecha o modal cancelar ao clicar no X
closeModalCancelar.addEventListener('click', ()=>{
    modalCancelar.style.display = 'none';
})

// Fecha o modal ao clicar fora da caixa do modal
window.addEventListener('click', function (e) {
    if (e.target === modalReposicao) {
        modalReposicao.style.display = 'none';
    }
    if (e.target === modalInfo) {
        modalInfo.style.display = 'none';
    }
    if (e.target === modalEditar) {
        modalEditar.style.display = 'none';
    }
    if(e.target == modalCancelar){
        modalCancelar.style.display = 'none';
    }
});


// Ouve os botões de seleção do tipo de tabela
const botoes_tab = document.querySelectorAll('.btnTab');
botoes_tab.forEach(botao => {
    botao.addEventListener('click', function () {
        const datas = document.querySelectorAll('.filtro_data');
        const estado = document.querySelector('.filtro_estado');

        if (botao.id == "tab_ativo") {
            // Esconde datas e mostra estado
            datas.forEach(filtro => {
                filtro.style.display = "none";
            })

            estado.style.display = "flex";

            // Altera a informação de qual tabela está sendo mostrada
            tabelaAtiva = "pedidos";
        }
        else if(botao.id == "tab_historico"){
            // Mostra os filtros de data e esconde o filtro de estado
            datas.forEach(filtro => {
                filtro.style.display = "flex";
            })

            estado.style.display = "none";

            // Altera a informação de qual tabela está sendo mostrada
            tabelaAtiva = "historico";
        }
        else if(botao.id == "tab_cancelado"){
            datas.forEach(filtro => {
                filtro.style.display = "flex";
            })

            estado.style.display = "none";
            tabelaAtiva = "cancelado";
        }

        renderTabela(dados);
    });
});


// Filtragem de dados
const btnFiltrar = document.getElementById('btnFiltrar');
btnFiltrar.addEventListener('click', function () {
    // Pega os valores de cada filtro
    const urgencia = document.getElementById('filtroUrgencia').value;
    const tipo = document.getElementById('filtroTipo').value;
    const estado = document.getElementById('filtroEstado').value;
    const data_inicio = document.getElementById('filtro_data_inicio').value;
    const data_fim = document.getElementById('filtro_data_fim').value;

    // Filtra os dados
    dadosFiltrados = dados.filter(item => {
        if (urgencia != "" && item.urgencia != urgencia)
            return false;
        if (tipo != "" && item.tipo != tipo)
            return false;

        if (estado != "") {
            if (estado == "por_pedir" && item.pedido == 1)
                return false;
            else if (estado == "pedido" && item.pedido == 0)
                return false;
        }

        if (data_inicio != "" && data_fim != "") {
            if (item.data_criacao < data_inicio || item.data_criacao > data_fim)
                return false
        }
        else if (data_inicio != "") {
            if (item.data_criacao < data_inicio)
                return false
        }
        else if (data_fim != "") {
            if (item.data_criacao > data_fim)
                return false
        }


        return true
    });

    renderTabela(dadosFiltrados);

});


// Adicionar pedido
function addPedido() {
    // Pega os dados do formulário
    const artigo = document.getElementById('artigo').value;
    const referencia = document.getElementById('referencia').value;
    const tipo = document.getElementById('tipo').value;
    const cliente = document.getElementById('cliente').value;
    const telemovel = document.getElementById('telemovel').value;
    const urgencia = document.getElementById('urgencia').value;
    const quantidade = document.getElementById('quantidade').value;
    const observacoes = document.getElementById('obs').value;

    // Valida o artigo
    if (artigo === '') {
        modalReposicao.style.display = "none";
        mostrarMsg("red", "Introduza um artigo");
        return;
    }

    // Constrói a variável dos dados
    let dados = {
        "acao": "adicionar",
        "artigo": artigo,
        "referencia": referencia,
        "tipo": tipo,
        "cliente": cliente,
        "telemovel": telemovel,
        "urgencia": urgencia,
        "quantidade": quantidade,
        "observacoes": observacoes
    }

    // Envia os dados pro servidor
    fetch('reposicao.php', {
        method: "post",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados)
    }).then(response => response.json())
        .then(data => {

            if (data['resultado'] == "sucesso") {
                //tratar sucesso -> Recarregar tabela
                carregartabela();
                modalReposicao.style.display = "none";
                mostrarMsg("green", data['msg']);
            }
            else {
                //Mensagem de erro vinda do servidor
                modalReposicao.style.display = "none";
                mostrarMsg("red", data['msg'])
            }
        })


    // Limpa o modal ao concluir
    artigo.value = "";
    referencia.value = "";
    cliente.value = "";
    telemovel.value = "";
}


function editarPedido(){
    const item_id = document.getElementById('edit_id').value;
    const artigo = document.getElementById('edit-artigo').value;
    const referencia = document.getElementById('edit-referencia').value;
    const tipo = document.getElementById('edit-tipo').value;
    const quantidade = document.getElementById('edit-quantidade').value;
    const cliente = document.getElementById('edit-cliente').value;
    const telemovel = document.getElementById('edit-telemovel').value;
    const urgencia = document.getElementById('edit-urgencia').value;
    const obs = document.getElementById('edit-obs').value;

    // Constrói a variável dos dados
    let dados = {
        "acao": "editar",
        "item_id" : item_id,
        "artigo": artigo,
        "referencia": referencia,
        "tipo": tipo,
        "cliente": cliente,
        "telemovel": telemovel,
        "urgencia": urgencia,
        "quantidade": quantidade,
        "observacoes": obs
    }

        // Envia os dados pro servidor
    fetch('reposicao.php', {
        method: "post",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(dados)
    }).then(response => response.json())
        .then(data => {

            if (data['resultado'] == "sucesso") {
                //tratar sucesso -> Recarregar tabela
                carregartabela();
                modalEditar.style.display = "none";
                mostrarMsg("green", data['msg']);
            }
            else {
                //Mensagem de erro vinda do servidor
                modalEditar.style.display = "none";
                mostrarMsg("red", data['msg'])
            }
        })
}


function carregartabela() {
    // Busca os dados no servidor
    fetch('reposicao.php', {
        method: "post",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            "acao": "listar"
        })
    }).then(response => response.json())
        .then(data => {
            // Salva os dados recebidos do servidor numa variável global
            dados = data;

            renderTabela(dados);

        })
}


function renderTabela(dados) {
    // Gera cada linha da tabela dinamicamente
    let tbody = document.querySelector('tbody');

    // Limpa o tbody antes de reescrever
    tbody.innerHTML = '';

    // Seleciona os dados com base no tipo de tabela
    if (tabelaAtiva == "historico") {
        dados = dados.filter(element => {
            if (element.concluido == 0) {
                return false;
            }
            return true;
        })
    }
    else if (tabelaAtiva == "pedidos") {
        dados = dados.filter(element => {
            if (element.concluido == 1) {
                return false;
            }
            if(element.cancelado == 1){
                return false;
            }

            return true;
        })
    }
    else if (tabelaAtiva == "cancelado"){
        dados = dados.filter(element=>{
            if(element.cancelado != 0){
                return true;
            }
            else{
                return false;
            }
        })
    }

    dados.forEach(element => {
        let linha = document.createElement('tr');

        // Cria cada célula da linha
        let cores = {
            Registado : "red",
            Pedido : "goldenrod",
            Concluído : "green",
            Cancelado : "darkred" 
        }
        let estado;
        if(element.pedido == 0 && element.cancelado == 0){
            estado = "Registado";
        }
        else if(element.concluido == 1){
            estado = "Concluído";
        }
        else if(element.cancelado == 1){            
            estado = "Cancelado"
        }
        else if(element.pedido == 1){
            estado = "Pedido";
        }

        let estado_artigo = document.createElement('td');
        estado_artigo.innerText = estado;
        estado_artigo.style.color = cores[estado];
        estado_artigo.style.fontWeight = "bold";
        linha.appendChild(estado_artigo);



        let artigo = document.createElement('td');
        artigo.innerText = element.artigo;
        linha.appendChild(artigo);

        let referencia = document.createElement('td');
        referencia.innerText = element.referencia;
        linha.appendChild(referencia);

        let tipo = document.createElement('td');
        tipo.innerText = element.tipo;
        linha.appendChild(tipo);

        let urgencia = document.createElement('td');
        // Dicionario para fazer correspondencia do tipo de urgência com o texto a ser apresentado (somente estética)
        urgencias = {
            "nao urgente": "Não Urgente",
            "urgente": "Urgente",
            "muito urgente": "Muito Urgente"
        }
        urgencia.innerText = urgencias[element.urgencia];

        // Adiciona estilo com base no tipo de urgencia
        switch (element.urgencia) {
            case "muito urgente":
                urgencia.classList.add('muito-urgente');
                break;

            case "urgente":
                urgencia.classList.add('urgente');
                break;

            case "nao urgente":
                urgencia.classList.add('nao-urgente');
                break;
        }
        linha.appendChild(urgencia);

        let obs = document.createElement('td');
        obs.innerText = element.observacoes;
        linha.appendChild(obs);

        let qntd = document.createElement('td');
        qntd.innerText = element.quantidade;
        linha.appendChild(qntd);


        // Botões de ação: Mostra o botão correspondente ao estado atual do pedido
        // Se o item já tiver estado de concluído, mostra somente um texto
        let acoes = document.createElement('td');
        acoes.classList.add('td-acoes');

        if (element.pedido == 1 && element.concluido == 0 && element.cancelado == 0) {
            // Botão "Marcar como concluído"
            const botao = document.createElement('button');
            botao.innerText = "Marcar como Concluído";
            botao.classList.add('button', 'small', 'btn-concluido');
            botao.addEventListener('click', () => attEstadoConcluido(element.item_id));
            acoes.appendChild(botao);
        }
        else if (element.pedido == 0 && element.cancelado == 0) {
            // Botão "Marcar como pedido"
            const botao = document.createElement('button');
            botao.innerText = "Marcar como pedido";
            botao.classList.add('button', 'small', 'btn-pedido');
            botao.addEventListener('click', () => attEstadoPedido(element.item_id));
            acoes.appendChild(botao);
        }

        // Botão de editar e cancelar
        if (element.concluido == 0 && element.cancelado == 0) {
            const btnEditar = document.createElement('button');
            btnEditar.innerText = "Editar"
            btnEditar.classList.add('button', 'secondary', 'small');
            
            // Preenche o modal de editar com os dados
            btnEditar.addEventListener('click', () => {
                document.getElementById('edit_id').value = element.item_id;
                document.getElementById('edit-artigo').value = element.artigo || "";
                document.getElementById('edit-referencia').value = element.referencia || "";
                document.getElementById('edit-tipo').value = element.tipo || "papelaria";
                document.getElementById('edit-quantidade').value = element.quantidade || 1;
                document.getElementById('edit-cliente').value = element.nome_cliente || "";
                document.getElementById('edit-telemovel').value =element.telefone_cliente || "";
                document.getElementById('edit-urgencia').value= element.urgencia ||"muito urgente";
                document.getElementById('edit-obs').value = element.observacoes || "";

                modalEditar.style.display = "flex";

            });
            acoes.appendChild(btnEditar);
    
            // Botão de cancelar
            const btnCancelar = document.createElement('button');
            btnCancelar.innerText = "Cancelar"
            btnCancelar.classList.add('button', 'btn-cancelar', 'small');
            // Abre o modal de cancelamento
            btnCancelar.addEventListener('click', ()=>{
                $('#cancelar-obs').val(element.observacoes);
                $('#cancelar-id').val(element.item_id);

                modalCancelar.style.display = "flex";
            })

            acoes.appendChild(btnCancelar);
        }
        
        // Botão de mais informações
        const btnInfo = document.createElement('a');
        btnInfo.href = "#";
        btnInfo.innerText = "Mais info.";

        btnInfo.addEventListener('click', function (e) {
            document.getElementById('info-content').innerHTML = `  
                <span><strong>CLiente: </strong> ${element.nome_cliente || '-'}</span>
                <p><strong>Nº Telemóvel: </strong> ${element.telefone_cliente || '-'}</p>
                <span><strong>Data da criação do artigo: </strong> ${element.data_criacao}</span>
                <p><strong>Artigo criado por: </strong> ${element.criado_por}</p>
                <span><strong>Data marcado como "Pedido": </strong> ${element.data_pedido || '-'}</span>
                <p><strong>Marcado como "Pedido" por: </strong> ${element.pedido_por || '-'}</p>
                <span><strong>Data marcado como "Concluído": </strong> ${element.data_conclusao || '-'}</span>
                <p><strong>Marcado como "Concluído" por: </strong> ${element.concluido_por || '-'}</p>
            `;

            document.getElementById('modal-info').style.display = 'flex';
        });
        acoes.appendChild(btnInfo);


        linha.appendChild(acoes);

        tbody.appendChild(linha);
    });
}


function attEstadoPedido(id) {
    fetch('reposicao.php', {
        method: "post",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            "acao": "atualizar",
            "estado": "pedido",
            "id": id,
            "observacoes":""
        })
    }).then(response => response.json())
        .then(data => {
            if (data['resultado'] == "sucesso") {
                //tratar sucesso -> Recarregar tabela
                carregartabela();
                mostrarMsg("green", data['msg']);
            }
            else {
                //Mensagem de erro vinda do servidor
                mostrarMsg("red", data['msg']);
            }
        })
}


function attEstadoConcluido(id) {
    fetch('reposicao.php', {
        method: "post",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            "acao": "atualizar",
            "estado": "concluido",
            "id": id,
            "observacoes":""
        })
    }).then(response => response.json())
        .then(data => {
            if (data['resultado'] == "sucesso") {
                //tratar sucesso -> Recarregar tabela
                carregartabela();
                mostrarMsg("green", data['msg']);
            }
            else {
                //Mensagem de erro vinda do servidor
                mostrarMsg("red", data['msg']);
            }
        });
}


function attEstadoCancelado(){
    const item_id = document.getElementById('cancelar-id').value;
    const observacoes = document.getElementById('cancelar-obs').value;

    if(!observacoes){
        modalCancelar.style.display = "none";
        mostrarMsg("red", "Deve colocar algo nas observações ao cancelar um pedido");
        return;
    }

    const dados = {
        "acao":"atualizar",
        "estado":"cancelado",
        "id":item_id,
        "observacoes":observacoes
    }


    fetch('reposicao.php',{
        method:"post",
        headers:{ "Content-Type": "application/json" },
        body:JSON.stringify(dados)
    }).then(response => response.json())
    .then(data =>{
        if (data['resultado'] == "sucesso") {
            //tratar sucesso -> Recarregar tabela
            modalCancelar.style.display = "none";
            mostrarMsg("green", data['msg']);
            carregartabela();
        }
        else {
            //Mensagem de erro vinda do servidor
            mostrarMsg("red", data['msg']);
        }
    })
}


function mostrarMsg(cor, conteudo) {
    msg.style.color = cor;
    msg.innerText = conteudo;

    setTimeout(() => {
        msg.innerText = "";
    }, 2000)
}