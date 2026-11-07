const btnFiltrar = document.getElementById('btnFiltro');
btnFiltrar.addEventListener('click', async function (){
    const tipo_filtro = document.querySelector('input[name="filtro"]:checked');
    const filtro = document.getElementById('filtro_input');
    if(!tipo_filtro){
        mostrarMsg("red", "Selecione uma opção de filtro");
        return;
    }

    if(!filtro.value){
        mostrarMsg("red", "Introduza um valor a filtrar");
        return;
    }

    const response = await fetch('pesquisar_encomendas.php',{
        method:"post",
        headers: { 'Content-Type': 'application/json' },
        body:JSON.stringify({
            tipo_filtro:tipo_filtro.value,
            filtro: filtro.value
        })
    });

    const data = await response.json();
    
    renderTabela(data["resultado"]);
});



function renderTabela(encomendas){
    const tbody = document.querySelector('tbody');
    tbody.innerHTML = '';

    encomendas.forEach(encomenda => {
        
        const linha = document.createElement('tr');

        const id_encomenda = document.createElement('td');
        id_encomenda.innerText = encomenda.id_encomenda;
        linha.appendChild(id_encomenda);

        const num_encomenda = document.createElement('td');
        num_encomenda.innerText = encomenda.num_encomenda;
        linha.appendChild(num_encomenda);
        
        const nome_ano_escolar = document.createElement('td');
        nome_ano_escolar.innerText = encomenda.nome_ano_escolar;
        linha.appendChild(nome_ano_escolar);

        const nome_aluno_encomenda = document.createElement('td');
        nome_aluno_encomenda.innerText = encomenda.nome_aluno_encomenda;
        linha.appendChild(nome_aluno_encomenda);

        const estado_encomenda = document.createElement('td');
        estado_encomenda.innerText = encomenda.estado_encomenda;
        const cores_estados = {
                'registada':'darkred',
                'pedida':'orange',
                'separada':'teal',
                'concluida':'goldenrod',
                'entregue':'green',
                'cancelada':'red',
                'parcialmente_separada':'navy'
        };
        estado_encomenda.style.color = cores_estados[encomenda.estado_encomenda];
        linha.appendChild(estado_encomenda);


        const detalhes_encomenda = document.createElement('td');
        const link_encomenda = document.createElement('a');

        link_encomenda.href = `detalhe_encomenda.php?id=${encomenda.id_encomenda}`;
        link_encomenda.innerText = 'Ver detalhes da encomenda';

        detalhes_encomenda.appendChild(link_encomenda);
        linha.appendChild(detalhes_encomenda);

        tbody.appendChild(linha);
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