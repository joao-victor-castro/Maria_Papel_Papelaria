const CORES_ESTADOS = {
    'registada': 'darkred',
    'pedida':    'orange',
    'parcialmente_separada':'navy',
    'separada':  'purple',
    'concluida': 'goldenrod',
    'entregue':  'green',
    'cancelada': 'red'
};

async function get_encomendas_ano() {
    const response = await fetch('index.php', {
        method: "post",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ "acao": "get_encomendas_ano" })
    });
    return await response.json();
}

async function get_encomendas_agrupamento() {
    const response = await fetch('index.php', {
        method: "post",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ "acao": "get_encomendas_agrupamento" })
    });
    return await response.json();
}

async function get_encomendas_estado() {
    const response = await fetch('index.php', {
        method: "post",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ "acao": "get_encomendas_estado" })
    });
    return await response.json();
}

async function get_manuais_tipo() {
    const response = await fetch('index.php', {
        method: "post",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ "acao": "get_manuais_tipo" })
    });
    return await response.json();
}

async function get_reposicao_estado() {
    const response = await fetch('index.php', {
        method: "post",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ "acao": "get_reposicao_estado" })
    });
    return await response.json();
}

async function renderGrafico() {
    const dados = await get_encomendas_ano();

    const labels = dados.map(d => d.nome_ano_escolar);
    const quantidades = dados.map(d => parseInt(d.quantidade));

    const ctx = document.getElementById('grafico');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Nº de Encomendas',
                data: quantidades,
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

async function renderCardsEstado() {
    const dados = await get_encomendas_estado();
    const container = document.getElementById('estado-cards');

    const quantidades = {};
    dados.forEach(d => {
        quantidades[d.estado_encomenda] = parseInt(d.quantidade);
    });

    const estados = Object.keys(CORES_ESTADOS);
    const metade = Math.ceil(estados.length / 2);
    const col1 = estados.slice(0, metade);
    const col2 = estados.slice(metade);

    const renderCol = (lista) => lista.map(estado => {
        const cor = CORES_ESTADOS[estado];
        const quantidade = quantidades[estado] ?? 0;
        return `
            <div class="estado-card" style="border-color: ${cor}; color: ${cor};">
                <span class="estado-nome">${estado}</span>
                <span class="estado-count">${quantidade}</span>
            </div>`;
    }).join('');

    container.innerHTML = `
        <div class="estado-cards-col">${renderCol(col1)}</div>
        <div class="estado-cards-col">${renderCol(col2)}</div>`;
}

async function renderCardsManuaisTipo() {
    const dados = await get_manuais_tipo();
    const container = document.getElementById('manuais-tipo-cards');
    if (!container) return;

    const mapeamento = [
        { label: 'Manuais',          chave: 'manuais',       cor: '#2980b9' },
        { label: 'Livros de Fichas', chave: 'livros_fichas', cor: '#8e44ad' }
    ];

    container.innerHTML = mapeamento.map(({ label, chave, cor }) => {
        const quantidade = parseInt(dados[chave]) || 0;
        return `
            <div class="estado-card" style="border-color: ${cor}; color: ${cor};">
                <span class="estado-nome">${label}</span>
                <span class="estado-count">${quantidade}</span>
            </div>`;
    }).join('');
}


async function renderGraficoAgrupamento() {
    const dados = await get_encomendas_agrupamento();

    const labels = dados.map(d => d.nome_agrupamento);
    const quantidades = dados.map(d => parseInt(d.quantidade));

    const ctx = document.getElementById('grafico-agrupamento');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Nº de Encomendas',
                data: quantidades,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                x: { beginAtZero: true },
                y: { beginAtZero: true }
            }
        }
    });
}

renderGrafico();
renderGraficoAgrupamento();
renderCardsEstado();
renderCardsManuaisTipo();