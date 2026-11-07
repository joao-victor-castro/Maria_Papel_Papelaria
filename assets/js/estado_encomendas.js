let encomendas = [];

const cores_estados = {
    'registada': 'darkred',
    'pedida': 'orange',
    'separada': 'teal',
    'concluida': 'goldenrod',
    'entregue': 'green',
    'cancelada': 'red',
    'parcialmente_separada':'navy'
};

(async ()=>{
    encomendas = await getEncomendas();
    filtrar_encomendas();
})();

const btnsFiltroAno = document.querySelectorAll('.filtroAno');
btnsFiltroAno.forEach(btn=>{
    btn.addEventListener('change', filtrar_encomendas);
});

const btnsFiltroEstado = document.querySelectorAll('.filtroEstado');
btnsFiltroEstado.forEach(btn=>{
    btn.addEventListener('change', filtrar_encomendas);
});

async function getEncomendas(){
    const response = await fetch('estado_encomendas.php', {
        method:"post",
        headers: { 'Content-Type': 'application/json' },
        body:JSON.stringify({
            acao : "get_encomendas"
        })
    });

    const data = await response.json();
    return data.encomendas;
}

function renderTabela(encomendas){
    const tbody = document.querySelector('tbody');
    tbody.innerHTML = '';

    document.getElementById('num_encomendas').innerText = encomendas.length;

    encomendas.forEach(encomenda => {
        const linha = document.createElement('tr');

        const num_encomenda = document.createElement('td');
        num_encomenda.innerText = encomenda.num_encomenda;
        linha.appendChild(num_encomenda);

        const data_encomenda = document.createElement('td');
        data_encomenda.innerText = encomenda.data_encomenda;
        linha.appendChild(data_encomenda);

        const ano_escolar = document.createElement('td');
        ano_escolar.innerText = encomenda.nome_ano_escolar;
        linha.appendChild(ano_escolar);

        const estado = document.createElement('td');
        const span_estado = document.createElement('span');
        span_estado.innerText = encomenda.estado_encomenda;
        span_estado.style.color = cores_estados[encomenda.estado_encomenda];
        span_estado.style.fontWeight = 'bold';
        estado.appendChild(span_estado);
        linha.appendChild(estado);

        const acoes = document.createElement('td');
        const link = document.createElement('a');
        link.href = `detalhe_encomenda.php?id=${encomenda.id_encomenda}`;
        link.innerText = 'Ver encomenda';
        acoes.appendChild(link);
        linha.appendChild(acoes);

        tbody.appendChild(linha);
    });
}

function filtrar_encomendas(){
    const ids_anos = [...document.querySelectorAll('.filtroAno:checked')].map(c => Number(c.value));
    const estados = [...document.querySelectorAll('.filtroEstado:checked')].map(c => c.value);

    let selecionadas = encomendas;

    if(ids_anos.length > 0){
        selecionadas = selecionadas.filter(e => ids_anos.includes(Number(e.id_ano_encomenda)));
    }

    if(estados.length > 0){
        selecionadas = selecionadas.filter(e => estados.includes(e.estado_encomenda));
    }

    renderTabela(selecionadas);
}
